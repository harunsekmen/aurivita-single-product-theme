<?php
defined('ABSPATH') || exit;

function av_register_cart_settings(): void {
    add_action('admin_menu', static function () {
        add_submenu_page('woocommerce', __('Sepet ve hediyeler', 'aurivita'), __('Sepet ve hediyeler', 'aurivita'), 'manage_woocommerce', 'aurivita-cart-settings', 'av_cart_settings_page');
    });
    add_filter('option_page_capability_aurivita_cart_rewards', static function () { return 'manage_woocommerce'; });
    add_action('admin_init', static function () {
        register_setting('aurivita_cart_rewards', 'aurivita_cart_rewards', [
            'type' => 'array', 'default' => av_cart_reward_defaults(), 'sanitize_callback' => 'av_sanitize_cart_reward_settings',
        ]);
        add_settings_section('av_cart_rewards', __('Kampanya ayarları', 'aurivita'), static function () {}, 'aurivita-cart-settings');
        foreach (av_cart_reward_fields() as $key => $label) {
            add_settings_field('av-cart-' . $key, $label, 'av_cart_settings_field', 'aurivita-cart-settings', 'av_cart_rewards', ['key' => $key, 'label_for' => 'av-cart-' . $key]);
        }
    });
}

function av_cart_reward_defaults(): array {
    return [
        'enabled' => false, 'threshold' => '', 'gift_ids' => [], 'eligible_product_ids' => [],
        'eligible_category_ids' => [], 'coupon_basis' => 'after', 'tax_basis' => 'display',
        'starts' => '', 'ends' => '', 'recommendation_ids' => [], 'shipping_method_ids' => [],
    ];
}

function av_cart_reward_stored_settings(): array {
    $saved = get_option('aurivita_cart_rewards', []);
    $defaults = av_cart_reward_defaults();
    return is_array($saved) ? array_replace($defaults, array_intersect_key($saved, $defaults)) : $defaults;
}

function av_cart_reward_id_list($input, bool $shipping = false): ?array {
    if (!is_array($input) || !array_is_list($input)) return null;
    $ids = [];
    foreach ($input as $index => $value) {
        // Native selects submit the first empty value to explicitly clear a selection.
        if ($index === 0 && $value === '') continue;
        if ($shipping) {
            if (!is_string($value) || !preg_match('/^free_shipping:[1-9][0-9]*$/D', $value)) return null;
            $ids[] = $value;
        } else {
            if ((!is_int($value) && !is_string($value)) || !preg_match('/^[1-9][0-9]*$/D', (string) $value)) return null;
            $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) return null;
            $ids[] = $id;
        }
    }
    return array_values(array_unique($ids));
}

function av_cart_reward_decimal($input): ?string {
    if ((!is_string($input) && !is_int($input) && !is_float($input)) || !function_exists('wc_format_decimal')) return null;
    $value = trim((string) $input);
    if ($value === '') return '';
    $separator = wc_get_price_decimal_separator();
    $pattern = '/^[0-9]+(?:[' . preg_quote('.' . $separator, '/') . '][0-9]+)?$/D';
    if (strlen($value) > 64 || !preg_match($pattern, $value)) return null;
    $decimal = wc_format_decimal($value, wc_get_price_decimals());
    return is_finite((float) $decimal) && (float) $decimal > 0 ? $decimal : null;
}

function av_cart_reward_date($input): ?DateTimeImmutable {
    if (!is_string($input) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}$/D', $input)) return null;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $input, wp_timezone());
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) || $date->format('Y-m-d\TH:i') !== $input) return null;
    return $date;
}

function av_cart_reward_product(int $id, bool $reward = false) {
    if (!function_exists('wc_get_product')) return false;
    $product = wc_get_product($id);
    if (!$product || $product->get_status() !== 'publish') return false;
    if ($product->is_type('variation')) {
        $parent = wc_get_product($product->get_parent_id());
        if (!$parent || $parent->get_status() !== 'publish' || !$parent->is_type('variable')) return false;
    }
    if (!$reward) return $product;
    if (!$product->is_type(['simple', 'variation']) || !$product->is_purchasable()) return false;
    $image_id = (int) $product->get_image_id();
    if (!$image_id && $product->is_type('variation')) $image_id = (int) $parent->get_image_id();
    if (!$image_id || get_post_type($image_id) !== 'attachment' || !wp_attachment_is_image($image_id) || !wp_get_attachment_image_url($image_id, 'thumbnail')) return false;
    if (str_contains(basename((string) get_attached_file($image_id)), 'woocommerce-placeholder')) return false;
    return $product;
}

function av_cart_reward_shipping_methods(): array {
    if (!class_exists('WC_Shipping_Zones') || !class_exists('WC_Shipping_Zone')) return [];
    $zones = WC_Shipping_Zones::get_zones();
    $rest = new WC_Shipping_Zone(0);
    $zones[0] = ['zone_id' => 0, 'zone_name' => $rest->get_zone_name(), 'shipping_methods' => $rest->get_shipping_methods()];
    $methods = [];
    foreach ($zones as $zone) {
        foreach ($zone['shipping_methods'] as $method) {
            if ($method->id !== 'free_shipping' || (int) $method->get_instance_id() < 1) continue;
            $requires = (string) $method->get_option('requires', '');
            $discounts = (string) $method->get_option('ignore_discounts', 'no');
            $minimum = av_cart_reward_decimal($method->get_option('min_amount', '0'));
            $id = 'free_shipping:' . (int) $method->get_instance_id();
            $methods[$id] = [
                'id' => $id, 'zone_id' => (int) $zone['zone_id'], 'zone' => (string) $zone['zone_name'],
                'title' => $method->get_title(), 'requires' => $requires,
                'min_amount' => $minimum ?? '', 'coupon_basis' => $discounts === 'yes' ? 'before' : 'after',
                'selectable' => $method->enabled === 'yes' && $requires === 'min_amount'
                    && $minimum !== null && $minimum !== '' && in_array($discounts, ['yes', 'no'], true),
            ];
        }
    }
    return $methods;
}

function av_cart_reward_shipping_matches(array $method, array $settings): bool {
    return $method['selectable'] && $settings['coupon_basis'] === $method['coupon_basis'];
}

function av_cart_reward_input_errors(array $settings): array {
    $errors = [];
    $threshold = av_cart_reward_decimal($settings['threshold']);
    if ($threshold === null || $threshold === '') $errors['threshold'] = __('Pozitif bir hediye eşiği girin.', 'aurivita');
    foreach (['gift_ids', 'eligible_product_ids', 'eligible_category_ids', 'recommendation_ids', 'shipping_method_ids'] as $key) {
        $ids = av_cart_reward_id_list($settings[$key], $key === 'shipping_method_ids');
        if ($ids === null) { $errors[$key] = __('Geçerli bir seçim listesi gönderin.', 'aurivita'); continue; }
        if ($key === 'gift_ids' && (count($ids) < 1 || count($ids) > 12)) $errors[$key] = __('1–12 hediye seçeneği belirleyin; müşteri bunlardan yalnızca birini, adet 1 seçer.', 'aurivita');
        if ($key === 'recommendation_ids' && count($ids) > 3) $errors[$key] = __('En fazla üç öneri seçin.', 'aurivita');
        foreach ($ids as $id) {
            if ($key === 'shipping_method_ids') continue;
            if ($key === 'eligible_category_ids') {
                $term = get_term($id, 'product_cat');
                $valid = $term && !is_wp_error($term) && $term->taxonomy === 'product_cat';
            } else {
                $valid = av_cart_reward_product($id, $key === 'gift_ids');
                if ($valid && $key === 'recommendation_ids') $valid = $valid->is_type(['simple', 'variable', 'variation']) && $valid->is_purchasable() && $valid->get_image_id();
            }
            if (!$valid) $errors[$key] = __('Seçim artık geçerli değil. Hediye ve öneriler, gerçek görseli olan yayınlanmış ve satın alınabilir basit ürün veya varyasyon olmalıdır.', 'aurivita');
        }
    }
    if (!in_array($settings['coupon_basis'], ['after', 'before'], true)) $errors['coupon_basis'] = __('Kupon hesaplama esasını seçin.', 'aurivita');
    if (!in_array($settings['tax_basis'], ['display', 'include', 'exclude'], true)) $errors['tax_basis'] = __('Vergi hesaplama esasını seçin.', 'aurivita');
    $dates = [];
    foreach (['starts', 'ends'] as $key) {
        if ($settings[$key] === '') continue;
        $dates[$key] = av_cart_reward_date($settings[$key]);
        if (!$dates[$key]) $errors[$key] = __('Site saat diliminde geçerli bir tarih ve saat girin.', 'aurivita');
    }
    if (!empty($dates['starts']) && !empty($dates['ends']) && $dates['ends'] <= $dates['starts']) $errors['ends'] = __('Bitiş, başlangıçtan sonra olmalıdır.', 'aurivita');
    $shipping = av_cart_reward_id_list($settings['shipping_method_ids'], true);
    if ($shipping) {
        $methods = av_cart_reward_shipping_methods();
        foreach ($shipping as $id) {
            if (!isset($methods[$id]) || !av_cart_reward_shipping_matches($methods[$id], $settings)) {
                $errors['shipping_method_ids'] = __('Etkin, pozitif minimum tutarlı native ücretsiz kargo seçin. Yalnızca minimum tutar koşulu desteklenir; kupon esası yöntemle eşleşmelidir. VE / VEYA kupon koşulları ek şart gerektirdiğinden desteklenmez.', 'aurivita');
            }
        }
    }
    return $errors;
}

/** Effective settings for any gift engine; consumers award one gift at quantity 1 and omit cart items from recommendations. */
function av_cart_reward_settings(): array {
    $settings = av_cart_reward_stored_settings();
    $valid = !array_diff_key(av_cart_reward_input_errors($settings), array_flip(['recommendation_ids', 'shipping_method_ids']));
    $settings['enabled'] = in_array($settings['enabled'], [true, 1, '1'], true) && $valid;
    foreach (['gift_ids', 'eligible_product_ids', 'eligible_category_ids', 'recommendation_ids', 'shipping_method_ids'] as $key) {
        $settings[$key] = av_cart_reward_id_list($settings[$key], $key === 'shipping_method_ids') ?? [];
    }
    $settings['threshold'] = av_cart_reward_decimal($settings['threshold']) ?? '';
    foreach (['coupon_basis' => ['after', 'before'], 'tax_basis' => ['display', 'include', 'exclude']] as $key => $values) {
        if (!in_array($settings[$key], $values, true)) $settings[$key] = av_cart_reward_defaults()[$key];
    }
    foreach (['starts', 'ends'] as $key) {
        if ($settings[$key] !== '' && !av_cart_reward_date($settings[$key])) $settings[$key] = '';
    }
    if ($settings['shipping_method_ids']) {
        $methods = av_cart_reward_shipping_methods();
        $settings['shipping_method_ids'] = array_values(array_filter($settings['shipping_method_ids'], static function ($id) use ($methods, $settings) {
            return isset($methods[$id]) && av_cart_reward_shipping_matches($methods[$id], $settings);
        }));
    }
    $now = current_datetime();
    $starts = av_cart_reward_date($settings['starts']);
    $ends = av_cart_reward_date($settings['ends']);
    if (($starts && $now < $starts) || ($ends && $now >= $ends)) $settings['enabled'] = false;
    return $settings;
}

function av_sanitize_cart_reward_settings($input): array {
    $saved = av_cart_reward_stored_settings();
    if (!is_array($input)) {
        add_settings_error('aurivita_cart_rewards', 'invalid_input', __('Ayarlar bir alan dizisi olmalıdır; kayıtlı değerler korundu.', 'aurivita'));
        return $saved;
    }
    $settings = $saved;
    $bad_dates = false;
    if (array_key_exists('enabled', $input)) {
        if (in_array($input['enabled'], [true, false, 0, 1, '0', '1'], true)) $settings['enabled'] = in_array($input['enabled'], [true, 1, '1'], true);
        else add_settings_error('aurivita_cart_rewards', 'enabled', __('Etkinlik alanı 0 veya 1 olmalıdır; kayıtlı değer korundu.', 'aurivita'));
    }
    if (array_key_exists('threshold', $input)) {
        $threshold = av_cart_reward_decimal($input['threshold']);
        if ($threshold !== null) $settings['threshold'] = $threshold;
        else add_settings_error('aurivita_cart_rewards', 'threshold', __('Hediye eşiğini mağaza para biriminde pozitif sayı olarak, binlik ayırıcı kullanmadan girin; kayıtlı değer korundu.', 'aurivita'));
    }
    foreach (['coupon_basis' => ['after', 'before'], 'tax_basis' => ['display', 'include', 'exclude']] as $key => $values) {
        if (!array_key_exists($key, $input)) continue;
        if (in_array($input[$key], $values, true)) $settings[$key] = $input[$key];
        else add_settings_error('aurivita_cart_rewards', $key, __('Geçerli bir hesaplama esası seçin; kayıtlı değer korundu.', 'aurivita'));
    }
    foreach (['starts', 'ends'] as $key) {
        if (!array_key_exists($key, $input)) continue;
        if ($input[$key] === '' || av_cart_reward_date($input[$key])) $settings[$key] = $input[$key];
        else {
            $bad_dates = true;
            add_settings_error('aurivita_cart_rewards', $key, __('Tarih geçersiz; kayıtlı tarih korundu ve kampanya kapatıldı.', 'aurivita'));
        }
    }
    $starts = av_cart_reward_date($settings['starts']);
    $ends = av_cart_reward_date($settings['ends']);
    if ($starts && $ends && $ends <= $starts) {
        $bad_dates = true;
        $settings['starts'] = $saved['starts'];
        $settings['ends'] = $saved['ends'];
        add_settings_error('aurivita_cart_rewards', 'date_order', __('Bitiş başlangıçtan sonra olmalıdır; kayıtlı tarihler korundu ve kampanya kapatıldı.', 'aurivita'));
    }
    foreach (['gift_ids', 'eligible_product_ids', 'eligible_category_ids', 'recommendation_ids', 'shipping_method_ids'] as $key) {
        if (!array_key_exists($key, $input)) continue;
        $ids = av_cart_reward_id_list($input[$key], $key === 'shipping_method_ids');
        if ($ids === null) {
            add_settings_error('aurivita_cart_rewards', $key, __('Bozuk seçim listesi gönderildi; kayıtlı seçimler korundu.', 'aurivita'));
            continue;
        }
        $candidate = $settings;
        $candidate[$key] = $ids;
        $errors = av_cart_reward_input_errors($candidate);
        // An empty gift is a deliberate clearing action; readiness still prevents activation.
        if (isset($errors[$key]) && !($key === 'gift_ids' && !$ids)) add_settings_error('aurivita_cart_rewards', $key, $errors[$key] . ' ' . __('Kayıtlı seçimler korundu.', 'aurivita'));
        else $settings[$key] = $ids;
    }
    $errors = array_diff_key(av_cart_reward_input_errors($settings), array_flip(['recommendation_ids', 'shipping_method_ids']));
    if ($bad_dates || $errors) {
        if ($settings['enabled']) {
            foreach ($errors as $key => $message) add_settings_error('aurivita_cart_rewards', 'inactive_' . $key, $message);
        }
        $settings['enabled'] = false;
    }
    return $settings;
}

function av_cart_reward_fields(): array {
    return [
        'enabled' => __('Hediye kampanyası', 'aurivita'), 'threshold' => __('Hediye eşiği', 'aurivita'),
        'gift_ids' => __('Hediye seçenekleri (sepet başına 1)', 'aurivita'), 'eligible_product_ids' => __('Uygun ürünler (isteğe bağlı)', 'aurivita'),
        'eligible_category_ids' => __('Uygun kategoriler (isteğe bağlı)', 'aurivita'),
        'coupon_basis' => __('Kupon esası', 'aurivita'), 'tax_basis' => __('Vergi esası', 'aurivita'),
        'starts' => __('Başlangıç (isteğe bağlı)', 'aurivita'), 'ends' => __('Bitiş (isteğe bağlı)', 'aurivita'),
        'recommendation_ids' => __('Sepet önerileri (en fazla 3)', 'aurivita'), 'shipping_method_ids' => __('Native ücretsiz kargo yöntemleri', 'aurivita'),
    ];
}

function av_cart_reward_product_label($product): string {
    $title = $product->get_name();
    if ($product->is_type('variation')) {
        $parent = wc_get_product($product->get_parent_id());
        $attributes = wc_get_formatted_variation($product, true, true, false);
        $title = ($parent ? $parent->get_name() : $title) . ($attributes !== '' ? ' — ' . $attributes : '');
    }
    return wp_strip_all_tags($title) . ' (#' . (int) $product->get_id() . ')';
}

function av_cart_reward_product_choices(): array {
    static $choices;
    if (isset($choices)) return $choices;
    $choices = [];
    if (!function_exists('wc_get_products')) return $choices;
    // Keep the form bounded, but retain selected products beyond the first native page.
    foreach (wc_get_products(['status' => 'publish', 'type' => ['simple', 'variable', 'variation', 'grouped', 'external'], 'limit' => 100, 'orderby' => 'title', 'order' => 'ASC']) as $product) {
        $choices[$product->get_id()] = $product;
    }
    $saved = av_cart_reward_stored_settings();
    foreach (['gift_ids', 'eligible_product_ids', 'recommendation_ids'] as $key) {
        foreach (av_cart_reward_id_list($saved[$key]) ?? [] as $id) {
            if (!isset($choices[$id])) {
                $product = wc_get_product($id);
                if ($product) $choices[$id] = $product;
            }
        }
    }
    return $choices;
}

function av_cart_settings_select(string $key, array $choices, array $selected_ids, bool $multiple = true): void {
    $name = 'aurivita_cart_rewards[' . $key . ']';
    if ($multiple) echo '<input type="hidden" name="' . esc_attr($name) . '[]" value="">';
    echo '<select id="av-cart-' . esc_attr($key) . '" name="' . esc_attr($name) . '[]"' . ($multiple ? ' multiple size="6"' : '') . ' style="min-width:280px;max-width:100%" aria-describedby="av-cart-' . esc_attr($key) . '-help">';
    if (!$multiple) echo '<option value="">' . esc_html__('Hediye seçilmedi', 'aurivita') . '</option>';
    foreach ($choices as $id => $label) echo '<option value="' . esc_attr((string) $id) . '"' . (in_array($id, $selected_ids, true) ? ' selected' : '') . '>' . esc_html($label) . '</option>';
    foreach (array_diff($selected_ids, array_keys($choices)) as $id) echo '<option value="' . esc_attr((string) $id) . '" selected>' . esc_html(sprintf(__('Geçersiz / artık kullanılamayan seçim (%s); kaldırın veya değiştirin.', 'aurivita'), $id)) . '</option>';
    echo '</select>';
}

function av_cart_settings_field(array $args): void {
    $key = $args['key'];
    $settings = av_cart_reward_stored_settings();
    $name = 'aurivita_cart_rewards[' . $key . ']';
    $value = is_scalar($settings[$key]) ? (string) $settings[$key] : '';
    $description = '';
    if ($key === 'enabled') {
        echo '<input type="hidden" name="' . esc_attr($name) . '" value="0"><label><input type="checkbox" id="av-cart-enabled" name="' . esc_attr($name) . '" value="1"' . (in_array($settings['enabled'], [true, 1, '1'], true) ? ' checked' : '') . '> ' . esc_html__('Hediye kampanyasını etkinleştir', 'aurivita') . '</label>';
        $description = __('Geçerli pozitif eşik ve gerçek hediye seçenekleri belirlenmeden kampanya ön yüzde etkinleşmez. Sepete yalnızca bir hediye, adet 1 eklenir.', 'aurivita');
    } elseif ($key === 'threshold') {
        echo '<input type="text" inputmode="decimal" class="wc_input_price" id="av-cart-threshold" name="' . esc_attr($name) . '" value="' . esc_attr(function_exists('wc_format_localized_price') ? wc_format_localized_price($value) : $value) . '" aria-describedby="av-cart-threshold-help">';
        $description = sprintf(__('Mağaza para birimi: %s. Pozitif tutar; binlik ayırıcı kullanmayın. Boş bırakıldığında hediye kampanyası kapalıdır.', 'aurivita'), get_woocommerce_currency());
    } elseif (in_array($key, ['gift_ids', 'eligible_product_ids', 'recommendation_ids'], true)) {
        $choices = [];
        foreach (av_cart_reward_product_choices() as $id => $product) {
            if (av_cart_reward_product((int) $id, $key === 'gift_ids')) $choices[$id] = av_cart_reward_product_label($product);
        }
        av_cart_settings_select($key, $choices, av_cart_reward_id_list($settings[$key]) ?? []);
        if ($key === 'gift_ids') $description = __('Yayınlanmış, satın alınabilir, gerçek medya görseli bulunan basit ürün veya varyasyon seçin. Varyasyonun yayınlanmış üst ürün görseli kullanılabilir. Yer tutucu görseller kabul edilmez.', 'aurivita');
        elseif ($key === 'recommendation_ids') $description = __('En fazla üç gerçek ürün seçin. Hediye ürünü de yapılandırılabilir; sepette bulunan ürün ve varyasyonlar önerilerde tekrar gösterilmez.', 'aurivita');
        else $description = __('Ürün veya varyasyon seçebilirsiniz. Ürün ve kategori seçimleri alternatif uygunluk sağlar (VEYA); ikisi de boşsa tüm ürünler uygundur.', 'aurivita');
        $description .= ' ' . __('İlk 100 yayınlanmış ürün/varyasyon ve kayıtlı seçimler listelenir. Çoklu seçim için Ctrl / Cmd kullanın.', 'aurivita');
    } elseif ($key === 'eligible_category_ids') {
        $choices = [];
        $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC']);
        if (!is_wp_error($terms)) foreach ($terms as $term) $choices[$term->term_id] = $term->name . ' (#' . $term->term_id . ')';
        av_cart_settings_select($key, $choices, av_cart_reward_id_list($settings[$key]) ?? []);
        $description = __('İsteğe bağlıdır. Ürün ve kategori seçimleri VEYA mantığıyla uygulanır; ikisi de boşsa tüm ürünler uygundur. Çoklu seçim için Ctrl / Cmd kullanın.', 'aurivita');
    } elseif ($key === 'coupon_basis' || $key === 'tax_basis') {
        $choices = $key === 'coupon_basis'
            ? ['after' => __('Kupon indiriminden sonra', 'aurivita'), 'before' => __('Kupon indiriminden önce', 'aurivita')]
            : ['display' => __('WooCommerce sepet gösterimine göre', 'aurivita'), 'include' => __('Vergi dahil', 'aurivita'), 'exclude' => __('Vergi hariç', 'aurivita')];
        echo '<select id="av-cart-' . esc_attr($key) . '" name="' . esc_attr($name) . '" aria-describedby="av-cart-' . esc_attr($key) . '-help">';
        foreach ($choices as $id => $label) echo '<option value="' . esc_attr($id) . '" ' . selected($value, $id, false) . '>' . esc_html($label) . '</option>';
        echo '</select>';
        $description = __('Hediye eşiğinin hesaplama esasıdır. Native kargo seçildiğinde kupon esası yöntemin ayarıyla eşleşmelidir. Native kargo kendi minimum tutarını ve WooCommerce sepet vergi gösterimini kullanır; hediye vergi esası kargo hesabını değiştirmez.', 'aurivita');
    } elseif ($key === 'starts' || $key === 'ends') {
        echo '<input type="datetime-local" id="av-cart-' . esc_attr($key) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" aria-describedby="av-cart-' . esc_attr($key) . '-help">';
        $description = sprintf(__('Site saat dilimi: %s. Boşsa bu yönde zaman sınırı yoktur. Bitiş başlangıçtan sonra olmalıdır.', 'aurivita'), wp_timezone_string());
    } elseif ($key === 'shipping_method_ids') {
        $choices = [];
        foreach (av_cart_reward_shipping_methods() as $id => $method) {
            if ($method['selectable']) $choices[$id] = av_cart_reward_shipping_label($method);
        }
        av_cart_settings_select($key, $choices, av_cart_reward_id_list($settings[$key], true) ?? []);
        $description = __('Yalnızca mevcut bölgelerdeki etkin, pozitif minimum tutarlı ve sadece minimum tutar koşullu ücretsiz kargo yöntemleri seçilir. VE / VEYA kupon koşulları desteklenmez. Yöntemin kendi eşiği geçerlidir; bu ayar kargo fiyatı veya yöntem oluşturmaz. Seçim isteğe bağlıdır ve hediye etkinliğinden bağımsızdır.', 'aurivita');
    }
    if ($description !== '') echo '<p class="description" id="av-cart-' . esc_attr($key) . '-help">' . esc_html($description) . '</p>';
}

function av_cart_reward_shipping_label(array $method): string {
    $policy = __('minimum tutar', 'aurivita');
    $basis = $method['coupon_basis'] === 'before' ? __('kupon öncesi', 'aurivita') : __('kupon sonrası', 'aurivita');
    return $method['zone'] . ' — ' . $method['title'] . ' (' . $method['id'] . ') · ' . wc_format_localized_price($method['min_amount']) . ' ' . get_woocommerce_currency() . ' · ' . $policy . ' · ' . $basis;
}

function av_cart_settings_page(): void {
    if (!current_user_can('manage_woocommerce')) wp_die(esc_html__('Bu sayfa için yetkiniz yok.', 'aurivita'));
    echo '<div class="wrap"><h1>' . esc_html__('Sepet ve hediyeler', 'aurivita') . '</h1>';
    settings_errors('aurivita_cart_rewards');
    if (!function_exists('wc_get_products')) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Bu ayarlar için WooCommerce etkin olmalıdır.', 'aurivita') . '</p></div></div>';
        return;
    }
    echo '<p>' . esc_html__('Gerçek bir hediye ürünü ve pozitif eşik seçilene kadar etkin hediye sayısı 0 olur; kampanya ön yüzde kapalıdır. Öneriler için en fazla üç gerçek ürün seçin. Hediye etkinliği yalnızca hediyeyi kontrol eder; seçili native kargo yöntemleri bağımsızdır.', 'aurivita') . '</p>';
    echo '<p><a href="' . esc_url(admin_url('edit.php?post_type=product')) . '">' . esc_html__('Native WooCommerce ürünlerini düzenle', 'aurivita') . '</a> · <a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=shipping')) . '">' . esc_html__('WooCommerce kargo bölgelerini düzenle', 'aurivita') . '</a></p>';
    $active = av_cart_reward_settings()['enabled'];
    echo '<p><strong>' . esc_html($active ? __('Hediye kampanyası şu anda etkin.', 'aurivita') : __('Hediye kampanyası şu anda kapalı.', 'aurivita')) . '</strong></p>';
    echo '<p>' . esc_html(sprintf(__('Etkin hediye sayısı: %d (adet 1).', 'aurivita'), $active ? 1 : 0)) . '</p>';
    echo '<form method="post" action="' . esc_url(admin_url('options.php')) . '">';
    settings_fields('aurivita_cart_rewards');
    do_settings_sections('aurivita-cart-settings');
    submit_button();
    echo '</form><h2>' . esc_html__('Mevcut native ücretsiz kargo yöntemleri', 'aurivita') . '</h2>';
    echo '<p>' . esc_html__('WooCommerce → Ayarlar → Kargo → ilgili bölge → Ücretsiz kargo → Düzenle yolundan minimum tutarı ve koşulu düzenleyin. Yalnızca minimum tutar koşulu desteklenir; VE / VEYA kupon koşulları ek şart gerektirir. “Kupon indiriminden önce uygula” seçeneği buradaki kupon esasıyla eşleşmelidir. Kargo vergisi hesabı WooCommerce sepet gösterimini izler; hediye eşiği kargo eşiğini değiştirmez. Hediye kampanyasının kapalı olması seçili native kargoyu kapatmaz.', 'aurivita') . '</p>';
    $methods = av_cart_reward_shipping_methods();
    if (!$methods) echo '<p>' . esc_html__('Mevcut bölgelerde native ücretsiz kargo yöntemi yok. İşletmenizin kargo politikasını WooCommerce kargo ayarlarında belirleyin.', 'aurivita') . '</p>';
    else {
        echo '<ul>';
        foreach ($methods as $method) {
            $label = $method['selectable'] ? av_cart_reward_shipping_label($method) : $method['zone'] . ' — ' . $method['title'] . ' (' . $method['id'] . ') · ' . __('Seçilemez: etkin, pozitif minimum tutarlı ve sadece minimum tutar koşullu yöntem gerekli.', 'aurivita');
            echo '<li>' . esc_html($label) . ' <a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=shipping&zone_id=' . $method['zone_id'])) . '">' . esc_html__('Native bölgede düzenle', 'aurivita') . '</a></li>';
        }
        echo '</ul>';
    }
    echo '</div>';
}
