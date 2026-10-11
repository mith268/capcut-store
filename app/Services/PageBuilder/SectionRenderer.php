<?php

namespace App\Services\PageBuilder;

use App\Models\Product;
use App\Models\Setting;

class SectionRenderer
{
    /**
     * Supported section definitions and default data structures.
     */
    public static function getAvailableSectionTypes(): array
    {
        return [
            'announcement' => [
                'name' => 'Announcement Bar',
                'category' => 'Navigation',
                'icon' => 'bell',
                'default_content' => [
                    'text' => '⚡ LIMITED-TIME OFFER • Instant Digital Delivery • 30-Day Warranty Included',
                    'link_url' => '',
                    'link_label' => '',
                ],
                'default_design' => [
                    'bg_color' => '#0F172A',
                    'text_color' => '#FFFFFF',
                ],
            ],
            'header' => [
                'name' => 'Header & Navigation',
                'category' => 'Navigation',
                'icon' => 'menu',
                'default_content' => [
                    'store_name' => 'CONVORA',
                    'store_badge' => 'STORE',
                    'offer_summary' => '1 Month • 1200 AI Credits',
                    'show_store_link' => true,
                    'cta_label' => 'GET ACCESS',
                    'cta_target' => '#pricing',
                    'links' => [
                        ['label' => 'Benefits', 'url' => '#benefits'],
                        ['label' => 'Bonus', 'url' => '#bonus'],
                        ['label' => 'How It Works', 'url' => '#how-it-works'],
                        ['label' => 'Guidelines', 'url' => '#guidelines'],
                        ['label' => 'Warranty', 'url' => '#warranty'],
                        ['label' => 'FAQ', 'url' => '#faq'],
                    ],
                ],
                'default_design' => [
                    'sticky' => true,
                    'style' => 'dark_glass',
                ],
            ],
            'hero' => [
                'name' => 'Conversion Hero (2-Column)',
                'category' => 'Core',
                'icon' => 'sparkles',
                'default_content' => [
                    'kicker' => '🔥 LIMITED-TIME DIGITAL OFFER',
                    'title' => 'CapCut Pro',
                    'highlight_title' => '1 Month',
                    'subtitle' => 'Premium video editing access for supported mobile, Windows and Mac devices. Instant digital delivery with 30-day warranty.',
                    'chip_credits' => '1200 AI Credits Included',
                    'chip_bonus' => 'Includes Canva Pro 1 Year as a bonus',
                    'video_id' => 'GeXVI3p-BqE',
                    'video_poster' => 'assets/images/capcut-pro-sticker.png',
                    'cta_primary_label' => 'GET CAPCUT PRO',
                    'cta_whatsapp_label' => 'WHATSAPP',
                    'show_price' => true,
                ],
                'default_design' => [
                    'layout' => 'two_column',
                    'align' => 'left',
                ],
            ],
            'trust_badges' => [
                'name' => 'Trust Guarantees Strip',
                'category' => 'Trust',
                'icon' => 'shield-check',
                'default_content' => [
                    'items' => [
                        ['icon' => 'bolt', 'title' => 'Instant Digital Delivery', 'subtitle' => 'Access details dispatched after payment'],
                        ['icon' => 'shield', 'title' => '30-Day Warranty', 'subtitle' => 'Complete coverage & replacement guarantee'],
                        ['icon' => 'device', 'title' => 'Multi-Device Support', 'subtitle' => 'Mobile, Windows PC & Mac access'],
                        ['icon' => 'lock', 'title' => 'Secure Checkout', 'subtitle' => '256-bit SSL encrypted payment gateway'],
                    ],
                ],
                'default_design' => [
                    'columns' => 4,
                ],
            ],
            'feature_cards' => [
                'name' => 'Feature Benefits Grid',
                'category' => 'Content',
                'icon' => 'grid',
                'default_content' => [
                    'kicker' => 'PRO CAPABILITIES',
                    'heading' => 'Everything You Need to Edit Like a Pro',
                    'subtitle' => 'Unlock all pro features, effects, transitions, AI credits, and multi-device capabilities.',
                    'cards' => [
                        ['title' => 'Full Pro Feature Library', 'desc' => 'Access all Pro transitions, video effects, smart filters, and velocity curve templates.'],
                        ['title' => '1200 AI Creative Credits', 'desc' => 'Generate automatic captions, auto-cut subtitles, AI speech, and script avatars.'],
                        ['title' => '4K 60FPS Video Export', 'desc' => 'Export watermark-free videos in pristine 4K resolution up to 60 FPS for social platforms.'],
                        ['title' => 'Cross-Platform License', 'desc' => 'Authorized access across Windows PC, Mac, Android, and iOS.'],
                    ],
                ],
                'default_design' => [
                    'columns' => 4,
                ],
            ],
            'bonus_offer' => [
                'name' => 'Bonus Value Box',
                'category' => 'Offer',
                'icon' => 'gift',
                'default_content' => [
                    'badge' => 'SPECIAL BONUS',
                    'title' => '🎁 BONUS: CANVA PRO 1 YEAR FREE',
                    'subtitle' => 'Complete your CapCut Pro purchase today and claim Canva Pro 1-Year access complimentary.',
                    'explanation' => 'Canva Pro activation is handled conveniently on your email after confirmed order payment.',
                    'image' => 'assets/images/canva-pro-sticker.png',
                ],
                'default_design' => [
                    'accent' => 'purple',
                ],
            ],
            'pricing_selector' => [
                'name' => 'Pricing & Plan Selector',
                'category' => 'Commerce',
                'icon' => 'currency-rupee',
                'default_content' => [
                    'kicker' => 'SELECT YOUR PLAN',
                    'heading' => 'Straightforward Pricing. Zero Auto-Renewal.',
                    'subtitle' => 'One-time secure payment. No hidden recurring subscriptions.',
                    'cta_label' => 'GET STARTED NOW',
                ],
                'default_design' => [
                    'style' => 'cards',
                ],
            ],
            'how_it_works' => [
                'name' => 'Step-by-Step Tutorial Steps',
                'category' => 'Guidance',
                'icon' => 'list-ordered',
                'default_content' => [
                    'kicker' => 'LOGIN TUTORIAL',
                    'heading' => 'Tutorial Steps to Login to CapCut',
                    'subtitle' => 'Follow these simple steps to connect, sign in, verify your access, and start editing.',
                    'steps' => [
                        ['step' => '01', 'title' => 'Download & Connect VPN', 'desc' => 'Ensure a stable connection using a recommended VPN for regional accessibility.'],
                        ['step' => '02', 'title' => 'Download CapCut', 'desc' => 'Install official CapCut app from the verified source on mobile, PC or Mac.'],
                        ['step' => '03', 'title' => 'Sign In With Provided Details', 'desc' => 'Open the application, click Sign In and enter your assigned Pro credentials.'],
                        ['step' => '04', 'title' => 'Verify Pro & Start Editing', 'desc' => 'Confirm the Pro badge active in the top bar. You are ready to edit!'],
                    ],
                ],
                'default_design' => [
                    'columns' => 4,
                ],
            ],
            'warranty_summary' => [
                'name' => 'Warranty & Guarantee Card',
                'category' => 'Trust',
                'icon' => 'badge-check',
                'default_content' => [
                    'days' => 30,
                    'title' => '30-Day Replacement Guarantee',
                    'description' => 'We provide 30 days of warranty coverage. If you encounter any access interruptions, our dedicated WhatsApp team will resolve or replace promptly.',
                ],
                'default_design' => [
                    'card_style' => 'glass_border',
                ],
            ],
            'faq' => [
                'name' => 'FAQ Accordion',
                'category' => 'Content',
                'icon' => 'help-circle',
                'default_content' => [
                    'kicker' => 'HAVE QUESTIONS?',
                    'heading' => 'Frequently Asked Questions',
                    'subtitle' => 'Everything you need to know about access, delivery, devices, and warranty.',
                    'items' => [
                        ['q' => 'How quickly will I receive my CapCut Pro access?', 'a' => 'Instantly after payment confirmation. Your order portal will show your details, and an email is dispatched immediately.'],
                        ['q' => 'Can I use this on both PC and Mobile?', 'a' => 'Yes, our plan supports up to 2 active devices simultaneously across PC (Windows/Mac) and Mobile.'],
                        ['q' => 'What if I face any login issues?', 'a' => 'Our dedicated WhatsApp support team is available from 9 AM to 11 PM IST with under 2-hour response time to assist you.'],
                        ['q' => 'Is there any automatic recurring charge?', 'a' => 'No. All purchases on our store are strictly one-time payments with zero automatic renewals.'],
                    ],
                ],
                'default_design' => [
                    'style' => 'accordion',
                ],
            ],
            'cta_banner' => [
                'name' => 'Bottom CTA Banner',
                'category' => 'Core',
                'icon' => 'zap',
                'default_content' => [
                    'heading' => 'Ready to Create Stunning Videos in CapCut Pro?',
                    'subtitle' => 'Join thousands of creators editing with 1200 AI credits, pro filters, and Canva Pro 1 Year free.',
                    'cta_label' => 'GET CAPCUT PRO NOW',
                    'whatsapp_label' => 'WHATSAPP',
                ],
                'default_design' => [
                    'bg_gradient' => 'cyan_blue',
                ],
            ],
            'footer' => [
                'name' => 'Landing Page Footer',
                'category' => 'Navigation',
                'icon' => 'layout-bottom',
                'default_content' => [
                    'disclaimer' => 'Independent digital service provider. CapCut and ByteDance are registered trademarks of their respective owners.',
                    'show_legal_links' => true,
                ],
                'default_design' => [
                    'minimal' => true,
                ],
            ],
            'sticky_bar' => [
                'name' => 'Sticky Bottom Purchase Bar',
                'category' => 'Core',
                'icon' => 'dock-bottom',
                'default_content' => [
                    'title' => 'CapCut Pro • 1 Month • 1200 AI Credits',
                    'bonus_text' => '• Canva Pro 1 Year FREE',
                    'cta_label' => 'GET CAPCUT PRO',
                    'whatsapp_label' => 'WHATSAPP',
                ],
                'default_design' => [
                    'mobile_only' => false,
                ],
            ],
            'spacer' => [
                'name' => 'Spacer / Divider',
                'category' => 'Layout',
                'icon' => 'minus',
                'default_content' => [
                    'height' => 32,
                    'show_divider' => false,
                ],
                'default_design' => [],
            ],
        ];
    }

    /**
     * Build the default Dark CapCut Pro 13-section composition.
     */
    public static function buildDefaultCapcutComposition(Product $product): array
    {
        $types = self::getAvailableSectionTypes();
        $sections = [];

        $order = [
            'announcement',
            'header',
            'hero',
            'trust_badges',
            'feature_cards',
            'bonus_offer',
            'how_it_works',
            'pricing_selector',
            'warranty_summary',
            'faq',
            'cta_banner',
            'footer',
            'sticky_bar',
        ];

        foreach ($order as $idx => $type) {
            $def = $types[$type];
            $content = $def['default_content'];

            // Bind real product attributes
            if ($type === 'hero') {
                $content['title'] = $product->name;
                $content['subtitle'] = $product->short_description ?: $product->description;
                $content['video_id'] = $product->video_id ?: 'GeXVI3p-BqE';
            } elseif ($type === 'pricing_selector') {
                $content['heading'] = 'Get CapCut Pro Access';
            }

            $sections[] = [
                'id' => 'sec_' . substr(md5($type . $idx), 0, 8),
                'type' => $type,
                'enabled' => true,
                'content' => $content,
                'design' => $def['default_design'],
            ];
        }

        return $sections;
    }
}
