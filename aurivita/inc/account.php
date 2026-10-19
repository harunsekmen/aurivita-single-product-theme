<?php
defined('ABSPATH') || exit;

function av_account_icon(string $name): string {
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'orders' => '<path d="m12 3 9 5-9 5-9-5 9-5Zm-9 5v10l9 5 9-5V8M12 13v10M7.5 5.5l9 5"/>',
        'download' => '<path d="M12 3v12m-5-5 5 5 5-5M4 16v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4"/>',
        'address' => '<path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'user' => '<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'logout' => '<path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4m7-13 5 5-5 5M8 12h13"/>',
        'arrow' => '<path d="m14 5-7 7 7 7M7 12h14"/>',
        'chevron' => '<path d="m9 5 7 7-7 7"/>',
    ];
    return '<svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? $paths['user']) . '</svg>';
}

function av_account_intro(): array {
    if (!is_user_logged_in() && !is_wc_endpoint_url('lost-password')) return ['title' => 'My account', 'description' => 'Sign in to track your orders and manage your details.'];
    $views = [
        'orders' => ['My orders', 'Track your orders and their current status here.'],
        'view-order' => ['Order details', 'Review your order items, total and status.'],
        'downloads' => ['My downloads', 'Find downloadable files for your orders here.'],
        'edit-address' => ['Adreslerim', 'Manage your billing and shipping addresses in one place.'],
        'edit-account' => ['Hesap bilgileri', 'Edit your personal details and account preferences.'],
        'payment-methods' => ['My payment methods', 'Manage the payment methods saved to your account.'],
        'add-payment-method' => ['Add payment method', 'Add a new payment method to your account.'],
        'lost-password' => ['Recover access to your account', 'Use your account email address to reset your password.'],
    ];
    foreach ($views as $endpoint => $copy) {
        if (is_wc_endpoint_url($endpoint)) return ['title' => $copy[0], 'description' => $copy[1]];
    }
    return ['title' => 'My account', 'description' => is_user_logged_in() ? 'Your orders, addresses and account details in one place.' : 'Sign in to track your orders and manage your details.'];
}

function av_account_kicker(): string { return av_brand_name() . ' · HESABINIZ'; }

function av_account_customer_name(WP_User $customer): string {
    $name = trim((string) ($customer->first_name ?: $customer->display_name));
    return $name !== '' ? $name : __('My account', 'aurivita');
}
