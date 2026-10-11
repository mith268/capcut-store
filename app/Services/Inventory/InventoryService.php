<?php

namespace App\Services\Inventory;

use App\Models\InventoryCredential;
use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Atomically allocate an available credential to the paid order using row-level locking.
     */
    public function allocateCredential(Order $order): ?InventoryCredential
    {
        return DB::transaction(function () use ($order) {
            // Check if already has an assigned credential
            $existing = InventoryCredential::where('order_id', $order->id)->first();
            if ($existing) {
                return $existing;
            }

            // Lock the first available credential row to prevent race conditions
            $credential = InventoryCredential::where('product_id', $order->product_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();

            if (!$credential) {
                Log::warning("OUT OF STOCK ALERT: Order #{$order->order_number} paid but no credential available for product ID: {$order->product_id}");
                $order->update([
                    'delivery_status' => 'pending',
                ]);
                return null;
            }

            $credential->update([
                'status' => 'sold',
                'order_id' => $order->id,
                'sold_at' => now(),
            ]);

            $order->update([
                'delivery_status' => 'ready',
                'delivered_at' => now(),
            ]);

            Log::info("Credential allocated successfully to Order #{$order->order_number} (Credential ID: {$credential->id})");

            return $credential;
        });
    }

    /**
     * Parse and import credentials from CSV.
     * Format: email,password[,notes]
     */
    public function importFromCsv(Product $product, string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("Unable to read CSV file.");
        }

        $file = fopen($filePath, 'r');
        $added = 0;
        $duplicates = 0;
        $invalid = 0;
        $errors = [];
        $lineNumber = 0;

        while (($row = fgetcsv($file, 1000, ',')) !== false) {
            $lineNumber++;

            // Skip header if detected
            if ($lineNumber === 1 && (strtolower(trim($row[0])) === 'email' || strtolower(trim($row[0])) === 'username')) {
                continue;
            }

            $email = isset($row[0]) ? trim($row[0]) : '';
            $password = isset($row[1]) ? trim($row[1]) : '';
            $notes = isset($row[2]) ? trim($row[2]) : null;

            if (empty($email) || empty($password)) {
                $invalid++;
                $errors[] = "Row {$lineNumber}: Missing email or password.";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid++;
                $errors[] = "Row {$lineNumber}: Invalid email format ({$email}).";
                continue;
            }

            // Duplicate check across available credentials for this product
            $allProductCreds = InventoryCredential::where('product_id', $product->id)->get();
            $isDuplicate = false;
            foreach ($allProductCreds as $existingCred) {
                if (strtolower($existingCred->email) === strtolower($email)) {
                    $isDuplicate = true;
                    break;
                }
            }

            if ($isDuplicate) {
                $duplicates++;
                $errors[] = "Row {$lineNumber}: Duplicate email already exists ({$email}).";
                continue;
            }

            try {
                $credential = new InventoryCredential();
                $credential->product_id = $product->id;
                $credential->email = $email;
                $credential->password = $password;
                $credential->notes = $notes;
                $credential->status = 'available';
                $credential->save();
                $added++;
            } catch (Exception $e) {
                $invalid++;
                $errors[] = "Row {$lineNumber}: Storage error.";
            }
        }

        fclose($file);

        return [
            'added' => $added,
            'duplicates' => $duplicates,
            'invalid' => $invalid,
            'errors' => array_slice($errors, 0, 10), // Limit error list
        ];
    }

    /**
     * Release credentials that were reserved but order never completed within window.
     */
    public function releaseExpiredReservations(int $minutes = 15): int
    {
        $expiredTime = now()->subMinutes($minutes);

        return InventoryCredential::where('status', 'reserved')
            ->where('reserved_at', '<', $expiredTime)
            ->update([
                'status' => 'available',
                'order_id' => null,
                'reserved_at' => null,
            ]);
    }
}
