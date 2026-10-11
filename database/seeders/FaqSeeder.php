<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'What exactly do I receive?',
                'answer' => 'You receive CapCut Pro access for 1 month with 1200 AI Credits. A Canva Pro bonus with 1-year validity is also included subject to the stated bonus claim process.',
                'display_order' => 1,
            ],
            [
                'question' => 'Is Canva Pro the main product?',
                'answer' => 'No. CapCut Pro is the main product. Canva Pro is a complimentary bonus.',
                'display_order' => 2,
            ],
            [
                'question' => 'How long is Canva Pro valid?',
                'answer' => 'The Canva Pro bonus has 1-year validity.',
                'display_order' => 3,
            ],
            [
                'question' => 'How many devices are supported?',
                'answer' => 'The offer supports up to 2 devices, subject to the applicable account/product conditions.',
                'display_order' => 4,
            ],
            [
                'question' => 'Can I use it on mobile?',
                'answer' => 'Yes, mobile access is supported where the relevant CapCut version/service is available.',
                'display_order' => 5,
            ],
            [
                'question' => 'Can I use it on Windows?',
                'answer' => 'Yes, supported Windows desktop devices can be used.',
                'display_order' => 6,
            ],
            [
                'question' => 'Can I use it on Mac?',
                'answer' => 'Yes, supported Mac devices can be used.',
                'display_order' => 7,
            ],
            [
                'question' => 'When do I provide my Canva email?',
                'answer' => 'After successful payment. The Canva email is optional and is requested only if you want to claim the Canva bonus.',
                'display_order' => 8,
            ],
            [
                'question' => 'Do I need to provide my WhatsApp number?',
                'answer' => 'Yes. Your WhatsApp number is collected during checkout for order communication and support.',
                'display_order' => 9,
            ],
            [
                'question' => 'Is there a warranty?',
                'answer' => 'Yes. The offer includes a 30-day warranty subject to the stated terms.',
                'display_order' => 10,
            ],
            [
                'question' => 'How do I access CapCut if it isn\'t available in my region?',
                'answer' => 'Availability can vary by region and device. Follow the access instructions supplied with your order and use the service in accordance with CapCut\'s applicable terms and local laws.',
                'display_order' => 11,
            ],
        ];

        Faq::truncate();

        foreach ($faqs as $faq) {
            Faq::create(array_merge($faq, ['category' => 'general', 'is_active' => true]));
        }
    }
}
