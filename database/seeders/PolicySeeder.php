<?php

namespace Database\Seeders;

use App\Models\RefundPolicy;
use App\Models\WarrantyPolicy;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        WarrantyPolicy::updateOrCreate(
            ['id' => 1],
            [
                'title' => '30-Day Digital Warranty',
                'warranty_days' => 30,
                'policy_content' => 'Your purchase includes a 30-day warranty subject to the product\'s usage guidelines and stated device/login limitations.',
                'eligible_issues' => "• Account credentials become invalid during legitimate authorized use.\n• 1200 credit allotment or Team features deactivate unexpectedly before warranty expiry.\n• 1-to-1 replacement account provided promptly upon support verification.",
                'excluded_issues' => "Warranty does not cover issues caused by:\n• Exceeding device limits (more than 1 mobile + 1 laptop/desktop)\n• Repeated unauthorized login attempts\n• Account misuse\n• Sharing credentials beyond permitted use\n• Violating the stated usage instructions\n• Requests filed after the 30-day warranty window has elapsed.",
                'is_active' => true,
            ]
        );

        RefundPolicy::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Digital Products Refund & Replacement Policy',
                'refund_conditions' => 'Because digital credentials are intangible and immediately accessible upon purchase, full monetary refunds are issued strictly when credentials cannot be provided or cannot be activated due to seller technical faults within 24 hours of purchase.',
                'replacement_conditions' => 'If an account experiences technical failure within the 30-day warranty period and adheres to device limits, a 1-to-1 replacement account is provided within 12 hours of reporting to support.',
                'excluded_conditions' => "• Change of mind after credentials have been revealed or delivered.\n• Exceeding device limits or violating login guidelines.\n• Account misuse or unauthorized credential sharing.",
                'is_active' => true,
            ]
        );
    }
}
