<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\InventoryCredential;
use App\Models\Product;
use App\Services\Inventory\InventoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = InventoryCredential::with(['product', 'order'])->latest();

        if ($status && in_array($status, ['available', 'reserved', 'sold', 'disabled'])) {
            $query->where('status', $status);
        }

        $credentials = $query->paginate(25);
        $products = Product::where('active', true)->get();

        $stats = [
            'total' => InventoryCredential::count(),
            'available' => InventoryCredential::where('status', 'available')->count(),
            'sold' => InventoryCredential::where('status', 'sold')->count(),
            'disabled' => InventoryCredential::where('status', 'disabled')->count(),
        ];

        return view('admin.inventory.index', compact('credentials', 'products', 'stats', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'email' => 'required|email|max:150',
            'password' => 'required|string|min:4|max:200',
            'notes' => 'nullable|string|max:500',
        ]);

        $credential = new InventoryCredential();
        $credential->product_id = $validated['product_id'];
        $credential->email = $validated['email'];
        $credential->password = $validated['password'];
        $credential->notes = $validated['notes'] ?? null;
        $credential->status = 'available';
        $credential->save();

        AuditLog::logAction('inventory_credential_created', 'InventoryCredential', (string) $credential->id, [
            'masked_email' => $credential->masked_email,
            'product_id' => $validated['product_id'],
        ]);

        return back()->with('success', 'Credential added successfully into encrypted inventory.');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $product = Product::findOrFail($request->product_id);
        $file = $request->file('csv_file');

        try {
            $result = $this->inventoryService->importFromCsv($product, $file->getRealPath());

            AuditLog::logAction('inventory_csv_imported', 'Product', (string) $product->id, [
                'added' => $result['added'],
                'duplicates' => $result['duplicates'],
                'invalid' => $result['invalid'],
            ]);

            $message = "Import complete! Added: {$result['added']}, Duplicates: {$result['duplicates']}, Invalid: {$result['invalid']}.";
            return back()->with('success', $message);
        } catch (Exception $e) {
            return back()->with('error', 'CSV Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Re-authenticated credential reveal for admin.
     */
    public function reveal(Request $request, InventoryCredential $credential): JsonResponse
    {
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            AuditLog::logAction('admin_credential_reveal_unauthorized', 'InventoryCredential', (string) $credential->id);
            return response()->json(['success' => false, 'message' => 'Invalid administrator password.'], 403);
        }

        AuditLog::logAction('admin_credential_revealed', 'InventoryCredential', (string) $credential->id, [
            'masked_email' => $credential->masked_email,
            'order_id' => $credential->order_id,
        ]);

        return response()->json([
            'success' => true,
            'email' => $credential->email,
            'password' => $credential->password,
            'notes' => $credential->notes,
        ]);
    }

    public function toggleStatus(InventoryCredential $credential)
    {
        if ($credential->status === 'sold') {
            return back()->with('error', 'Cannot toggle status of an already sold credential.');
        }

        $newStatus = $credential->status === 'available' ? 'disabled' : 'available';
        $credential->update(['status' => $newStatus]);

        AuditLog::logAction('inventory_status_changed', 'InventoryCredential', (string) $credential->id, [
            'new_status' => $newStatus,
        ]);

        return back()->with('success', "Credential marked as {$newStatus}.");
    }

    public function destroy(InventoryCredential $credential)
    {
        if ($credential->status === 'sold') {
            return back()->with('error', 'Cannot delete a credential that has already been delivered to a customer.');
        }

        AuditLog::logAction('inventory_credential_deleted', 'InventoryCredential', (string) $credential->id, [
            'masked_email' => $credential->masked_email,
        ]);

        $credential->delete();

        return back()->with('success', 'Credential removed from inventory.');
    }
}
