<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Subscriptions',
                'slug' => 'subscriptions',
                'icon' => 'sparkles',
                'description' => 'Premium memberships, active streaming, AI tools, and professional access.',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Design Tools',
                'slug' => 'design-tools',
                'icon' => 'palette',
                'description' => 'Creative suites, video editing, graphics design, and AI media generators.',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Software Licenses',
                'slug' => 'software-licenses',
                'icon' => 'key',
                'description' => 'Genuine retail operating system product keys, utilities, and office suites.',
                'is_active' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
