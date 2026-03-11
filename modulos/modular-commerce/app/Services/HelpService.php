<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class HelpService
{
    private const CACHE_KEY_SECTIONS = 'help_sections';

    private const CACHE_KEY_FAQS = 'help_faqs';

    private const CACHE_KEY_CONTACT = 'help_contact';

    private const CACHE_TTL = 3600; // 1 hour

    public function getHelpContent($user = null): array
    {
        return [
            'sections' => $this->getHelpSections($user),
            'faqs' => $this->getFAQs(),
            'contactInfo' => $this->getContactInfo(),
        ];
    }

    public function getHelpSections($user = null): array
    {
        return Cache::remember(self::CACHE_KEY_SECTIONS, self::CACHE_TTL, function () use ($user) {
            $sections = [
                [
                    'title' => 'Getting Started',
                    'icon' => 'rocket-launch',
                    'color' => 'blue',
                    'articles' => [
                        ['title' => 'Creating Your First Order', 'slug' => 'first-order'],
                        ['title' => 'Managing Your Profile', 'slug' => 'manage-profile'],
                        ['title' => 'Understanding the Dashboard', 'slug' => 'dashboard-guide'],
                    ],
                ],
                [
                    'title' => 'Orders & Billing',
                    'icon' => 'credit-card',
                    'color' => 'green',
                    'articles' => [
                        ['title' => 'Order Status Guide', 'slug' => 'order-status'],
                        ['title' => 'Payment Methods', 'slug' => 'payment-methods'],
                        ['title' => 'Invoice Management', 'slug' => 'invoices'],
                    ],
                ],
                [
                    'title' => 'Account Settings',
                    'icon' => 'cog-6-tooth',
                    'color' => 'purple',
                    'articles' => [
                        ['title' => 'Security Settings', 'slug' => 'security'],
                        ['title' => 'Notification Preferences', 'slug' => 'notifications'],
                        ['title' => 'Data & Privacy', 'slug' => 'privacy'],
                    ],
                ],
            ];

            // Add admin-specific sections if user is admin
            if ($user && $this->isAdmin($user)) {
                $sections[] = [
                    'title' => 'Admin Guide',
                    'icon' => 'shield-check',
                    'color' => 'red',
                    'articles' => [
                        ['title' => 'User Management', 'slug' => 'admin-users'],
                        ['title' => 'System Configuration', 'slug' => 'admin-config'],
                        ['title' => 'Reports & Analytics', 'slug' => 'admin-reports'],
                    ],
                ];
            }

            return $sections;
        });
    }

    public function getFAQs(): array
    {
        return Cache::remember(self::CACHE_KEY_FAQS, self::CACHE_TTL, function () {
            return [
                [
                    'question' => 'How do I create a new order?',
                    'answer' => 'Navigate to the Orders page and click the "New Order" button. Fill in the required information and submit.',
                    'category' => 'Orders',
                ],
                [
                    'question' => 'Can I change my order after submission?',
                    'answer' => 'Orders can be modified only if they haven\'t been processed yet. Contact support immediately for changes.',
                    'category' => 'Orders',
                ],
                [
                    'question' => 'How do I reset my password?',
                    'answer' => 'Click on "Forgot Password" on the login page and follow the instructions sent to your email.',
                    'category' => 'Account',
                ],
                [
                    'question' => 'What payment methods are accepted?',
                    'answer' => 'We accept credit cards, debit cards, and PayPal. All payments are processed securely.',
                    'category' => 'Billing',
                ],
                [
                    'question' => 'How can I contact customer support?',
                    'answer' => 'You can reach our support team via email at support@modular-commerce.com or use the live chat feature.',
                    'category' => 'Support',
                ],
            ];
        });
    }

    public function getContactInfo(): array
    {
        return Cache::remember(self::CACHE_KEY_CONTACT, self::CACHE_TTL, function () {
            return [
                'email' => config('help.contact.email', 'support@modular-commerce.com'),
                'phone' => config('help.contact.phone', '+1 (555) 123-4567'),
                'hours' => config('help.contact.hours', 'Monday - Friday, 9:00 AM - 6:00 PM EST'),
                'liveChat' => config('help.contact.live_chat', true),
                'responseTime' => config('help.contact.response_time', 'Usually within 2 hours during business hours'),
            ];
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_SECTIONS);
        Cache::forget(self::CACHE_KEY_FAQS);
        Cache::forget(self::CACHE_KEY_CONTACT);
    }

    private function isAdmin($user): bool
    {
        return $user->email === 'admin@modular-commerce.com';
    }
}
