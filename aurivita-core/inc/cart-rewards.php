<?php
defined('ABSPATH') || exit;

use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;
use Automattic\WooCommerce\StoreApi\Formatters\MoneyFormatter;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;

function av_register_cart_rewards(): void {
    AV_Cart_Rewards::register();
}

final class AV_Cart_Rewards {
    private static bool $registered = false;
    private static bool $reconciling = false;
    private static bool $replacing = false;
    private static ?array $adding = null;
    private static string $notice = '';

    public static function register(): void {
        if (self::$registered) return;
        self::$registered = true;
        add_action('woocommerce_blocks_loaded', [self::class, 'register_api']);
        add_action('woocommerce_before_calculate_totals', [self::class, 'prepare'], 1);
        add_action('woocommerce_after_calculate_totals', [self::class, 'reconcile'], 1);
        add_action('woocommerce_cart_loaded_from_session', [self::class, 'reload'], 20);
        add_filter('woocommerce_add_cart_item_data', [self::class, 'protect_marker'], PHP_INT_MAX, 4);
        add_filter('woocommerce_get_item_data', [self::class, 'item_label'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [self::class, 'order_label'], 10, 4);
        foreach (['minimum', 'maximum', 'multiple_of'] as $limit) {
            add_filter('woocommerce_store_api_product_quantity_' . $limit, [self::class, 'quantity_limit'], 10, 3);
        }
        add_filter('woocommerce_store_api_product_quantity_editable', [self::class, 'quantity_editable'], 10, 3);
    }

    private static function settings(): array {
        $defaults = ['enabled' => false, 'threshold' => '', 'gift_ids' => [], 'eligible_product_ids' => [],
            'eligible_category_ids' => [], 'coupon_basis' => 'after', 'tax_basis' => 'display',
            'starts' => '', 'ends' => '', 'recommendation_ids' => [], 'shipping_method_ids' => []];
        $settings = function_exists('av_cart_reward_settings') ? av_cart_reward_settings() : [];
        return array_replace($defaults, is_array($settings) ? $settings : []);
    }

    private static function money($amount): string {
        return (new MoneyFormatter())->format($amount);
    }

    private static function ids($ids): array {
        return is_array($ids) ? array_values(array_unique(array_filter(array_map('absint', $ids)))) : [];
    }

    private static function signature(int $product_id, int $variation_id): string {
        return wp_hash('aurivita:reward-gift:' . $product_id . ':' . $variation_id, 'auth');
    }

    public static function is_gift(array $item): bool {
        return is_string($item['av_reward_gift'] ?? null) && hash_equals(
            self::signature((int) ($item['product_id'] ?? 0), (int) ($item['variation_id'] ?? 0)),
            $item['av_reward_gift']
        );
    }

    public static function protect_marker($item, $product_id, $variation_id, $quantity): array {
        $item = (array) $item;
        unset($item['av_reward_gift']);
        if (self::$adding === [(int) $product_id, (int) $variation_id]) {
            $item['av_reward_gift'] = self::signature((int) $product_id, (int) $variation_id);
        }
        return $item;
    }

    public static function prepare($cart): void {
        foreach ($cart->get_cart() as $key => $item) {
            if (!self::is_gift($item) || !($item['data'] ?? null) instanceof WC_Product) continue;
            // Product instance caching can share an object with a paid cart line.
            $product = clone $item['data'];
            $product->set_price(0);
            $cart->cart_contents[$key]['data'] = $product;
            $cart->cart_contents[$key]['quantity'] = 1;
        }
    }

    public static function reload($cart): void {
        $cart->calculate_totals();
    }

    private static function image(WC_Product $product): string {
        $id = (int) $product->get_image_id();
        if (!$id && $product->is_type('variation')) {
            $parent = wc_get_product($product->get_parent_id());
            $id = $parent ? (int) $parent->get_image_id() : 0;
        }
        return $id && wp_attachment_is_image($id) ? (string) (wp_get_attachment_image_url($id, 'woocommerce_thumbnail') ?: '') : '';
    }

    private static function published(WC_Product $product): bool {
        if ($product->get_status() !== 'publish') return false;
        if ($product->is_type('variation')) {
            $parent = wc_get_product($product->get_parent_id());
            if (!$parent || $parent->get_status() !== 'publish' || !$product->variation_is_visible()) return false;
        }
        return true;
    }

    private static function gift_product(int $id) {
        $product = wc_get_product($id);
        if (!$product || !$product->is_type(['simple', 'variation']) || !self::published($product) || !self::image($product)) return false;
        if ($product->is_type('variation')) {
            foreach ($product->get_variation_attributes() as $attribute) {
                if ($attribute === '') return false;
            }
        }
        return $product;
    }

    private static function stock_available(WC_Product $product, $cart, array $ignored_keys = []): bool {
        if (!$product->is_purchasable() || !$product->is_in_stock() || !$product->has_enough_stock(1)) return false;
        if ($product->is_type('variation') && !$product->variation_is_active()) return false;
        $quantity = 1;
        foreach ($cart->get_cart() as $key => $item) {
            if (in_array($key, $ignored_keys, true) || !($item['data'] ?? null) instanceof WC_Product) continue;
            if ($item['data']->get_stock_managed_by_id() === $product->get_stock_managed_by_id()) {
                $quantity += (float) $item['quantity'];
            }
        }
        if (!$product->has_enough_stock($quantity)) return false;
        if ($product->managing_stock() && !$product->backorders_allowed()) {
            $reserved = wc_get_held_stock_quantity($product, (int) (WC()->session ? WC()->session->get('store_api_draft_order', 0) : 0));
            if ((float) $product->get_stock_quantity() - $reserved < $quantity) return false;
        }
        return true;
    }

    private static function campaign_open(array $settings): bool {
        if (empty($settings['enabled']) || !is_scalar($settings['threshold'])
            || !is_numeric($settings['threshold']) || (float) $settings['threshold'] <= 0) return false;
        $now = current_datetime();
        $dates = [];
        foreach (['starts', 'ends'] as $key) {
            $value = $settings[$key];
            if ($value === '') continue;
            if (!is_string($value)) return false;
            $date = false;
            foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s'] as $format) {
                $parsed = DateTimeImmutable::createFromFormat('!' . $format, $value, wp_timezone());
                if ($parsed && $parsed->format($format) === $value) { $date = $parsed; break; }
            }
            if (!$date) return false;
            $dates[$key] = $date;
        }
        if (isset($dates['starts'], $dates['ends']) && $dates['ends'] <= $dates['starts']) return false;
        return (!isset($dates['starts']) || $now >= $dates['starts']) && (!isset($dates['ends']) || $now < $dates['ends']);
    }

    private static function amount($cart, array $settings): string {
        $product_ids = self::ids($settings['eligible_product_ids']);
        $category_ids = self::ids($settings['eligible_category_ids']);
        $with_tax = $settings['tax_basis'] === 'include'
            || ($settings['tax_basis'] === 'display' && $cart->display_prices_including_tax());
        $before = $settings['coupon_basis'] === 'before';
        $amount = 0.0;
        foreach ($cart->get_cart() as $item) {
            if (self::is_gift($item) || !($item['data'] ?? null) instanceof WC_Product) continue;
            $product = $item['data'];
            $id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();
            if ($product_ids || $category_ids) {
                $matches_product = in_array($product->get_id(), $product_ids, true) || in_array($id, $product_ids, true);
                $matches_category = $category_ids && has_term($category_ids, 'product_cat', $id);
                if (!$matches_product && !$matches_category) continue;
            }
            $amount += (float) ($item[$before ? 'line_subtotal' : 'line_total'] ?? 0);
            if ($with_tax) $amount += (float) ($item[$before ? 'line_subtotal_tax' : 'line_tax'] ?? 0);
        }
        return self::money(max(0, $amount));
    }

    private static function gift_keys($cart): array {
        return array_keys(array_filter($cart->get_cart(), [self::class, 'is_gift']));
    }

    private static function gift_state($cart, array $settings): array {
        $keys = self::gift_keys($cart);
        $selected_key = $keys[0] ?? '';
        $selected_item = $selected_key ? $cart->get_cart_item($selected_key) : [];
        $selected_id = (int) (($selected_item['variation_id'] ?? 0) ?: ($selected_item['product_id'] ?? 0));
        $options = [];
        foreach (self::ids($settings['gift_ids']) as $id) {
            $product = self::gift_product($id);
            if (!$product) continue;
            $options[] = ['id' => $id, 'name' => $product->get_name(), 'image' => self::image($product),
                'permalink' => $product->get_permalink(), 'reference_price' => self::money(wc_get_price_to_display($product, ['display_context' => 'cart'])),
                'available' => self::stock_available($product, $cart, $keys), 'selected' => $id === $selected_id];
        }
        $threshold = is_scalar($settings['threshold']) && is_numeric($settings['threshold']) ? self::money(max(0, (float) $settings['threshold'])) : '0';
        $amount = self::amount($cart, $settings);
        $active = self::campaign_open($settings) && (int) $threshold > 0 && count($options) > 0;
        return ['active' => $active, 'eligible' => $active && (int) $amount >= (int) $threshold,
            'threshold' => $threshold, 'amount' => $amount, 'remaining' => (string) max(0, (int) $threshold - (int) $amount),
            'selected_key' => $selected_key, 'options' => $options, 'notice' => self::$notice];
    }

    public static function reconcile($cart): void {
        if (self::$reconciling || self::$replacing) return;
        self::$reconciling = true;
        try {
            $settings = self::settings();
            $state = self::gift_state($cart, $settings);
            $kept = false;
            $changed = false;
            foreach (self::gift_keys($cart) as $key) {
                $item = $cart->get_cart_item($key);
                $id = (int) ($item['variation_id'] ?: $item['product_id']);
                $product = in_array($id, self::ids($settings['gift_ids']), true) ? self::gift_product($id) : false;
                if (!$kept && $state['eligible'] && $product && self::stock_available($product, $cart, [$key])) {
                    $kept = true;
                    continue;
                }
                $cart->remove_cart_item($key);
                $changed = true;
                self::$notice = !$state['active'] ? __('The gift offer is no longer active; the gift was removed.', 'aurivita')
                    : (!$state['eligible'] ? __('Your cart is below the gift threshold; the gift was removed.', 'aurivita')
                    : __('The gift is no longer available and has been removed.', 'aurivita'));
            }
            // Native removal may itself calculate totals; complete the final snapshot before returning.
            if ($changed) $cart->calculate_totals();
        } finally {
            self::$reconciling = false;
        }
    }

    public static function select_gift($data): void {
        if (!is_array($data) || ($data['action'] ?? '') !== 'select_gift' || !is_scalar($data['id'] ?? null)
            || !ctype_digit((string) $data['id']) || (int) $data['id'] < 1) {
            throw new RouteException('aurivita_invalid_gift_action', __('Please choose a valid gift.', 'aurivita'), 400);
        }
        $cart = WC()->cart;
        if (!$cart) throw new RouteException('aurivita_cart_unavailable', __('Your cart could not be loaded.', 'aurivita'), 409);
        $cart->calculate_totals();
        $settings = self::settings();
        $state = self::gift_state($cart, $settings);
        $id = (int) $data['id'];
        $product = in_array($id, self::ids($settings['gift_ids']), true) ? self::gift_product($id) : false;
        $keys = self::gift_keys($cart);
        if (!$state['eligible'] || !$product || !self::stock_available($product, $cart, $keys)) {
            throw new RouteException('aurivita_gift_unavailable', __('This gift is currently unavailable.', 'aurivita'), 409);
        }
        foreach ($keys as $key) {
            $item = $cart->get_cart_item($key);
            if ((int) ($item['variation_id'] ?: $item['product_id']) === $id) return;
        }
        $previous = $cart->get_cart();
        $removed = $cart->get_removed_cart_contents();
        $product_id = $product->is_type('variation') ? $product->get_parent_id() : $id;
        $variation_id = $product->is_type('variation') ? $id : 0;
        $variation = $variation_id ? $product->get_variation_attributes() : [];
        self::$replacing = true;
        try {
            foreach ($keys as $key) $cart->remove_cart_item($key);
            if (!apply_filters('woocommerce_add_to_cart_validation', true, $product_id, 1, $variation_id, $variation, [])) {
                throw new RouteException('aurivita_gift_add_failed', __('The gift could not be added. Your previous selection was kept.', 'aurivita'), 409);
            }
            self::$adding = [$product_id, $variation_id];
            try {
                $key = $cart->add_to_cart($product_id, 1, $variation_id, $variation, []);
            } finally {
                self::$adding = null;
            }
            if (!$key || !self::is_gift($cart->get_cart_item($key))) {
                throw new RouteException('aurivita_gift_add_failed', __('The gift could not be added. Your previous selection was kept.', 'aurivita'), 409);
            }
            self::$notice = __('Your gift was added.', 'aurivita');
        } catch (Throwable $error) {
            $cart->set_cart_contents($previous);
            $cart->set_removed_cart_contents($removed);
            if ($error instanceof RouteException) throw $error;
            throw new RouteException('aurivita_gift_add_failed', __('The gift could not be added. Your previous selection was kept.', 'aurivita'), 409);
        } finally {
            self::$adding = null;
            self::$replacing = false;
            $cart->calculate_totals();
        }
    }

    private static function shipping_method_selected($method, array $selected): bool {
        return $method->id === 'free_shipping' && $method->enabled === 'yes' && $method->requires === 'min_amount'
            && is_numeric($method->min_amount) && (float) $method->min_amount > 0
            && (in_array((string) $method->get_instance_id(), $selected, true)
                || in_array('free_shipping:' . $method->get_instance_id(), $selected, true));
    }

    private static function shipping_progress($method, $cart): array {
        $amount = $cart->get_displayed_subtotal();
        if ($method->ignore_discounts === 'no') {
            $amount -= $cart->get_discount_total();
            if ($cart->display_prices_including_tax()) $amount -= $cart->get_discount_tax();
        }
        $amount = self::money(max(0, $amount));
        $threshold = self::money(max(0, (float) $method->min_amount));
        return ['threshold' => $threshold, 'amount' => $amount,
            'remaining' => (string) max(0, (int) $threshold - (int) $amount)];
    }

    private static function shipping($cart, array $settings): array {
        $customer = WC()->customer;
        $known = $customer && $cart->has_calculated_shipping() && $customer->get_shipping_country() !== ''
            && ($customer->get_shipping_address_1() !== '' || $customer->get_shipping_city() !== '' || $customer->get_shipping_postcode() !== '');
        $empty = ['active' => false, 'threshold' => '0', 'amount' => '0', 'remaining' => '0',
            'eligible' => false, 'address_known' => (bool) $known, 'available' => false];
        $selected = is_array($settings['shipping_method_ids']) ? array_map('strval', $settings['shipping_method_ids']) : [];
        if (!$selected) return $empty;
        $progress = [];
        $available = (bool) $known;
        if ($known) {
            $packages = $cart->get_shipping_packages();
            if (!$packages) return $empty;
            foreach ($packages as $package) {
                $zone = WC_Shipping_Zones::get_zone_matching_package($package);
                $choices = [];
                $package_available = false;
                foreach ($zone->get_shipping_methods(true) as $method) {
                    if (!self::shipping_method_selected($method, $selected)) continue;
                    $choices[] = self::shipping_progress($method, $cart);
                    $package_available = $method->is_available($package) || $package_available;
                }
                if (!$choices) return $empty;
                usort($choices, static fn($a, $b) => (int) $a['remaining'] <=> (int) $b['remaining']);
                $progress[] = $choices[0];
                $available = $available && $package_available;
            }
            usort($progress, static fn($a, $b) => (int) $b['remaining'] <=> (int) $a['remaining']);
        } else {
            $zones = array_merge([['zone_id' => 0]], array_values(WC_Shipping_Zones::get_zones()));
            foreach ($zones as $zone_data) {
                $zone = new WC_Shipping_Zone((int) $zone_data['zone_id']);
                foreach ($zone->get_shipping_methods(true) as $method) {
                    if (self::shipping_method_selected($method, $selected)) $progress[] = self::shipping_progress($method, $cart);
                }
            }
            usort($progress, static fn($a, $b) => (int) $a['threshold'] <=> (int) $b['threshold']);
        }
        if (!$progress) return $empty;
        return array_replace($empty, $progress[0], ['active' => true,
            'eligible' => $progress[0]['remaining'] === '0', 'available' => $available]);
    }

    private static function recommendations($cart, array $settings): array {
        $recommendations = [];
        $in_cart = [];
        foreach ($cart->get_cart() as $item) {
            $in_cart[] = (int) ($item['product_id'] ?? 0);
            $in_cart[] = (int) ($item['variation_id'] ?? 0);
        }
        foreach (self::ids($settings['recommendation_ids']) as $id) {
            if (in_array($id, $in_cart, true)) continue;
            $product = wc_get_product($id);
            if (!$product || !self::published($product) || !$product->is_visible() || !$product->is_purchasable()
                || !$product->is_in_stock() || !self::image($product)
                || ($product->is_type('variation') && in_array($product->get_parent_id(), $in_cart, true))) continue;
            $recommendations[] = ['id' => $id, 'name' => $product->get_name(), 'image' => self::image($product),
                'price' => self::money(wc_get_price_to_display($product, ['display_context' => 'cart'])),
                'permalink' => $product->get_permalink(), 'type' => $product->get_type()];
            if (count($recommendations) === 3) break;
        }
        return $recommendations;
    }

    public static function cart_data(): array {
        $cart = WC()->cart;
        $settings = self::settings();
        $gift = self::gift_state($cart, $settings);
        if (!$gift['notice'] && $gift['active'] && $gift['eligible']) {
            $gift['notice'] = $gift['selected_key'] ? __('Your gift was added.', 'aurivita')
                : (array_filter($gift['options'], static fn($option) => $option['available'])
                    ? __('Choose your gift.', 'aurivita') : __('The gift options are currently out of stock.', 'aurivita'));
        }
        return ['gift' => $gift, 'shipping' => self::shipping($cart, $settings), 'recommendations' => self::recommendations($cart, $settings)];
    }

    public static function item_data($item): array {
        $gift = self::is_gift($item);
        $id = (int) (($item['variation_id'] ?? 0) ?: ($item['product_id'] ?? 0));
        $product = $gift ? wc_get_product($id) : ($item['data'] ?? null);
        return ['gift' => $gift, 'reference_price' => $product instanceof WC_Product
            ? self::money(wc_get_price_to_display($product, ['display_context' => 'cart'])) : '0'];
    }

    public static function quantity_limit($value, $product, $item) {
        return is_array($item) && self::is_gift($item) ? 1 : $value;
    }

    public static function quantity_editable($value, $product, $item) {
        return is_array($item) && self::is_gift($item) ? false : $value;
    }

    public static function item_label($labels, $item): array {
        if (self::is_gift($item)) $labels[] = ['key' => __('Gift', 'aurivita'), 'value' => __('Gift', 'aurivita'), 'raw_key' => 'av_reward_gift'];
        return $labels;
    }

    public static function order_label($item, $key, $values, $order): void {
        if (!self::is_gift($values)) return;
        $item->add_meta_data('_av_reward_gift', true, true);
        $item->add_meta_data(__('Gift', 'aurivita'), __('Gift', 'aurivita'), true);
    }

    private static function fields(array $types): array {
        $fields = [];
        foreach ($types as $name => $type) {
            $fields[$name] = ['type' => $type, 'readonly' => true, 'context' => ['view', 'edit']];
            if ($type === 'string' && in_array($name, ['threshold', 'amount', 'remaining', 'price', 'reference_price'], true)) $fields[$name]['pattern'] = '^[0-9]+$';
        }
        return $fields;
    }

    public static function cart_schema(): array {
        $option = self::fields(['id' => 'integer', 'name' => 'string', 'image' => 'string', 'permalink' => 'string',
            'reference_price' => 'string', 'available' => 'boolean', 'selected' => 'boolean']);
        $gift = self::fields(['active' => 'boolean', 'eligible' => 'boolean', 'threshold' => 'string', 'amount' => 'string',
            'remaining' => 'string', 'selected_key' => 'string', 'notice' => 'string']);
        $gift['options'] = ['type' => 'array', 'readonly' => true, 'items' => ['type' => 'object', 'properties' => $option]];
        $shipping = self::fields(['active' => 'boolean', 'threshold' => 'string', 'amount' => 'string', 'remaining' => 'string',
            'eligible' => 'boolean', 'address_known' => 'boolean', 'available' => 'boolean']);
        return ['gift' => ['type' => 'object', 'readonly' => true, 'properties' => $gift],
            'shipping' => ['type' => 'object', 'readonly' => true, 'properties' => $shipping],
            'recommendations' => ['type' => 'array', 'readonly' => true, 'items' => ['type' => 'object',
                'properties' => self::fields(['id' => 'integer', 'name' => 'string', 'image' => 'string', 'price' => 'string', 'permalink' => 'string', 'type' => 'string'])]]];
    }

    public static function register_api(): void {
        if (!function_exists('woocommerce_store_api_register_endpoint_data') || !function_exists('woocommerce_store_api_register_update_callback')) return;
        woocommerce_store_api_register_endpoint_data(['endpoint' => CartSchema::IDENTIFIER, 'namespace' => 'aurivita',
            'data_callback' => [self::class, 'cart_data'], 'schema_callback' => [self::class, 'cart_schema'], 'schema_type' => ARRAY_A]);
        woocommerce_store_api_register_endpoint_data(['endpoint' => CartItemSchema::IDENTIFIER, 'namespace' => 'aurivita',
            'data_callback' => [self::class, 'item_data'],
            'schema_callback' => static fn() => self::fields(['gift' => 'boolean', 'reference_price' => 'string']), 'schema_type' => ARRAY_A]);
        woocommerce_store_api_register_update_callback(['namespace' => 'aurivita', 'callback' => [self::class, 'select_gift']]);
    }
}
