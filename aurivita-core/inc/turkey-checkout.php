<?php
defined('ABSPATH') || exit;

function av_turkey_only_checkout(): bool {
    if (!function_exists('WC') || !WC()->countries) return false;
    return array_keys(WC()->countries->get_allowed_countries()) === ['TR']
        && array_keys(WC()->countries->get_shipping_countries()) === ['TR'];
}

function av_turkey_block_checkout(): bool {
    return av_turkey_only_checkout() && function_exists('is_checkout') && is_checkout()
        && !is_order_received_page() && has_block('woocommerce/checkout', get_post());
}

function av_turkey_districts(): array {
    return class_exists('TRCF\\Plugin') && get_option('trcf_enable_district', 'yes') === 'yes'
        ? \TRCF\Plugin::instance()->get_districts() : [];
}

add_filter('woocommerce_get_country_locale', static function(array $locale): array {
    if (!av_turkey_only_checkout()) return $locale;
    $locale['TR']['state'] = array_merge($locale['TR']['state'] ?? [], ['label' => __('City', 'aurivita'), 'priority' => 80]);
    $locale['TR']['city'] = array_merge($locale['TR']['city'] ?? [], ['label' => __('District', 'aurivita'), 'priority' => 85]);
    return $locale;
}, 30);

// Vendso remains untouched; its classic address editor is retained outside Block checkout.
add_filter('trcf_load_classic_assets', static fn($load) => av_turkey_block_checkout() ? false : $load);
add_filter('trcf_is_block_checkout', static fn($load) => av_turkey_block_checkout() && av_turkey_districts() ? false : $load);
add_action('wp_enqueue_scripts', static function(): void {
    if (!av_turkey_block_checkout()) return;
    $districts = av_turkey_districts();
    wp_enqueue_script('aurivita-turkey-checkout', plugins_url('../assets/turkey-checkout.js', __FILE__), ['wp-data', 'wc-blocks-data-store'], (string) filemtime(__DIR__.'/../assets/turkey-checkout.js'), true);
    wp_localize_script('aurivita-turkey-checkout', 'AurivitaTurkey', [
        'districts' => $districts,
        'districtLabel' => __('District', 'aurivita'),
        'provincePlaceholder' => __('Choose a city', 'aurivita'),
        'chooseDistrict' => __('Select district', 'aurivita'),
        'chooseProvince' => __('Choose a city first', 'aurivita'),
        'invalidDistrict' => __('Please choose a district within your selected city.', 'aurivita'),
    ]);
}, 30);

function av_turkey_validate_district(string $country, string $state, string $city): bool {
    $districts = av_turkey_districts();
    return $country !== 'TR' || !$districts || in_array(trim($city), $districts[$state] ?? [], true);
}

add_action('woocommerce_after_checkout_validation', static function(array $data, WP_Error $errors): void {
    if (!av_turkey_only_checkout()) return;
    foreach (['billing', 'shipping'] as $group) {
        if ($group === 'shipping' && empty($data['ship_to_different_address'])) continue;
        if (!av_turkey_validate_district($data[$group.'_country'] ?? '', $data[$group.'_state'] ?? '', $data[$group.'_city'] ?? '')) {
            $errors->add('av_'.$group.'_district', __('Please choose a district within your selected city.', 'aurivita'), ['id' => $group.'_city']);
        }
    }
}, 20, 2);

add_action('woocommerce_store_api_checkout_update_order_from_request', static function(WC_Order $order): void {
    if (!av_turkey_only_checkout()) return;
    foreach (['billing', 'shipping'] as $group) {
        if ($group === 'shipping' && !$order->needs_shipping_address()) continue;
        if (!av_turkey_validate_district($order->{'get_'.$group.'_country'}(), $order->{'get_'.$group.'_state'}(), $order->{'get_'.$group.'_city'}())) {
            throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException('av_'.$group.'_district', __('Please choose a district within your selected city.', 'aurivita'), 400);
        }
    }
}, 20);
