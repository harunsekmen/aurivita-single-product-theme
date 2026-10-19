<?php
defined('ABSPATH') || exit;

function av_site_defaults(): array {
    return [
        'version' => 1, 'profile' => 'generic', 'reference_product_id' => 0,
        'brand_name' => (string) get_bloginfo('name'), 'tagline' => (string) get_bloginfo('description'),
        'header_logo' => 0, 'footer_logo' => 0, 'favicon' => (int) get_option('site_icon', 0),
        'announcement' => '', 'menu_title' => '', 'menu_note' => '', 'footer_about' => '',
        'footer_support_title' => __('Support', 'aurivita'), 'footer_links_title' => __('Shop', 'aurivita'),
        'footer_policies_title' => __('Policies', 'aurivita'), 'footer_disclosures' => ['', '', ''],
        'footer_copyright' => '', 'footer_note' => '', 'contact_email' => '', 'contact_page' => 0,
        'privacy_page' => (int) get_option('wp_page_for_privacy_policy', 0),
        'terms_page' => (int) get_option('woocommerce_terms_page_id', 0), 'returns_page' => 0,
        'blog_page' => (int) get_option('page_for_posts', 0), 'home_description' => '',
        'checkout_title' => __('Complete your order', 'aurivita'),
        'checkout_description' => __('Enter your details and review your order summary.', 'aurivita'),
        'checkout_return_text' => __('Back to shopping', 'aurivita'),
    ];
}

function av_site(?string $key = null) {
    $saved = get_option('aurivita_site', []);
    $defaults = av_site_defaults();
    $settings = array_replace($defaults, is_array($saved) ? $saved : []);
    foreach ($defaults as $name => $default) {
        if (is_string($default)) $settings[$name] = is_scalar($settings[$name]) ? (string) $settings[$name] : $default;
        elseif (is_int($default) && !in_array($name, ['header_logo', 'footer_logo', 'favicon'], true)) $settings[$name] = is_scalar($settings[$name]) ? absint($settings[$name]) : $default;
    }
    foreach (['header_logo', 'footer_logo', 'favicon'] as $name) if (!is_scalar($settings[$name])) $settings[$name] = $defaults[$name];
    $settings['profile'] = $settings['profile'] === 'aurivita' ? 'aurivita' : 'generic';
    $disclosures = is_array($settings['footer_disclosures']) ? array_values($settings['footer_disclosures']) : [];
    $settings['footer_disclosures'] = array_map(static fn($value) => is_scalar($value) ? (string) $value : '', array_pad(array_slice($disclosures, 0, 3), 3, ''));
    return $key === null ? $settings : ($settings[$key] ?? null);
}

function av_brand_name(): string {
    $value = av_site('brand_name');
    return is_string($value) && trim($value) !== '' ? $value : (string) get_bloginfo('name');
}

function av_site_text(string $key): string {
    $value = av_site($key);
    return is_scalar($value) ? strtr((string) $value, ['{year}' => wp_date('Y'), '{brand}' => av_brand_name()]) : '';
}

function av_site_url(string $role): string {
    if ($role === 'home') return home_url('/');
    if ($role === 'product') {
        $product = function_exists('av_main_product') ? av_main_product() : null;
        return $product && $product->get_status() === 'publish' ? $product->get_permalink() : av_site_url('shop');
    }
    $commerce = ['shop' => 'shop', 'cart' => 'cart', 'checkout' => 'checkout', 'account' => 'myaccount', 'myaccount' => 'myaccount'];
    if (isset($commerce[$role])) return function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink($commerce[$role]) : '';
    $keys = ['contact' => 'contact_page', 'privacy' => 'privacy_page', 'terms' => 'terms_page', 'returns' => 'returns_page', 'blog' => 'blog_page'];
    $key = $keys[$role] ?? (in_array($role, $keys, true) ? $role : '');
    $id = $key ? absint(av_site($key)) : 0;
    return $id && get_post_type($id) === 'page' && get_post_status($id) === 'publish' ? (string) get_permalink($id) : '';
}

function av_brand_logo(string $location): string {
    $footer = $location === 'footer';
    $value = av_site($footer ? 'footer_logo' : 'header_logo');
    if (is_numeric($value) && (int) $value > 0 && wp_get_attachment_image_url((int) $value, 'full')) {
        return wp_get_attachment_image((int) $value, 'full', false, ['class'=>$footer ? 'footer-logo' : 'av-header-logo','sizes'=>$footer ? '180px' : '(max-width:749px) 138px, 150px','loading'=>$footer ? 'lazy' : 'eager','alt'=>av_brand_name()]);
    }
    $url = is_numeric($value) ? wp_get_attachment_image_url((int) $value, 'full') : (is_string($value) ? $value : '');
    if (!$url || !preg_match('~^https?://~i', $url)) return '<span class="av-brand-text">' . esc_html(av_brand_name()) . '</span>';
    $metadata = is_numeric($value) ? wp_get_attachment_metadata((int) $value) : [];
    $width = absint($metadata['width'] ?? ($footer ? 600 : 500));
    $height = absint($metadata['height'] ?? ($footer ? 142 : 118));
    return '<img' . ($footer ? ' class="footer-logo" loading="lazy" sizes="180px"' : '') . ' src="' . esc_url($url) . '" width="' . $width . '" height="' . $height . '" alt="' . esc_attr(av_brand_name()) . '">';
}

function av_is_reference_product(WC_Product|false|null $product): bool {
    return $product instanceof WC_Product && av_site('profile') === 'aurivita'
        && $product->get_id() > 0 && $product->get_id() === absint(av_site('reference_product_id'));
}

function av_site_menu(string $location): string {
    if (!has_nav_menu($location)) return '';
    $markup = (string) wp_nav_menu(['theme_location' => $location, 'container' => false, 'menu_class' => 'av-site-menu', 'items_wrap' => '<ul class="%2$s">%3$s</ul>', 'fallback_cb' => false, 'echo' => false]);
    if ($location !== 'primary') return $markup;
    $reference = function_exists('wc_get_product') ? wc_get_product(absint(av_site('reference_product_id'))) : null;
    $reference_url = av_is_reference_product($reference) ? $reference->get_permalink() . '#product-science' : '';
    $processor = new WP_HTML_Tag_Processor($markup);
    while ($processor->next_tag('A')) {
        $processor->set_attribute('data-nav', '');
        if ($reference_url && $processor->get_attribute('href') === $reference_url
            && (!function_exists('is_product') || !is_product() || av_is_reference_product(wc_get_product(get_the_ID())))) $processor->set_attribute('data-csi-open-target', '');
    }
    return $processor->get_updated_html();
}

function av_site_announcement(): string {
    $text = av_site('announcement');
    return is_string($text) ? wp_kses($text, ['span' => ['class' => true], 'strong' => [], 'em' => [], 'sup' => [], 'br' => []]) : '';
}

function av_site_brand_css(): string {
    $colors = function_exists('av_global') ? av_global() : get_option('aurivita_global', []);
    $css = ':root{';
    foreach (['av_ink', 'av_accent', 'av_action', 'av_dark', 'av_surface', 'av_soft', 'av_gold', 'av_muted', 'av_border'] as $key) {
        $color = is_array($colors) ? ($colors[$key] ?? '') : '';
        if (is_string($color) && preg_match('/^#[0-9a-f]{6}$/i', $color)) $css .= '--' . str_replace('_', '-', $key) . ':' . $color . ';';
    }
    return $css . '--muted:var(--av-muted,#834449);--border:var(--av-border,#D9C6C7)}';
}

function av_site_favicon(): void {
    $value = av_site('favicon');
    if (av_site('profile') !== 'aurivita' || !is_string($value) || !preg_match('~^https?://~i', $value)
        || (function_exists('has_site_icon') && has_site_icon())) return;
    echo '<link rel="icon" href="' . esc_url($value) . '">';
}
add_action('wp_head', 'av_site_favicon', 99);

function av_site_legacy_section(string $name): array {
    $group = get_option('aurivita_' . $name, null);
    if (!is_array($group) && function_exists('av_data')) $group = av_data($name . '-group');
    $sections = is_array($group) && is_array($group['sections'] ?? null) ? $group['sections'] : [];
    foreach ($sections as $section) if (is_array($section)) return $section;
    return [];
}

function av_site_legacy_field(array $section, int $number): string {
    foreach (is_array($section['blocks'] ?? null) ? $section['blocks'] : [] as $block) {
        if (is_array($block) && ($block['type'] ?? '') === 'content_0') {
            $value = $block['settings']['field_' . $number] ?? '';
            return is_scalar($value) ? (string) $value : '';
        }
    }
    return '';
}

function av_site_legacy_logo(array $section, string $fallback) {
    foreach (is_array($section['blocks'] ?? null) ? $section['blocks'] : [] as $block) {
        if (is_array($block) && ($block['type'] ?? '') === 'content_1') {
            $value = $block['settings']['field_0'] ?? '';
            if (is_numeric($value) && (int) $value > 0) return (int) $value;
            if (is_string($value) && $value !== '') return $value;
        }
    }
    return function_exists('av_asset') ? av_asset($fallback) : 0;
}

function av_site_legacy_page(string $slug): int {
    $page = get_page_by_path($slug, OBJECT, 'page');
    return $page && $page->post_status === 'publish' ? (int) $page->ID : 0;
}

function av_site_legacy_policy_page(string $option, string $slug): int {
    $current = (int) get_option($option, 0);
    return $current && get_post_type($current) === 'page' && get_post_status($current) === 'publish' ? $current : av_site_legacy_page($slug);
}

function av_seed_site_settings(): void {
    $main_id = (int) get_option('aurivita_main_product', 0);
    $product = $main_id > 0 && function_exists('wc_get_product') ? wc_get_product($main_id) : null;
    $legacy = $product instanceof WC_Product && ($product->get_slug() === 'aurivita-cayenne-pepper'
        || (string) get_post_meta($product->get_id(), '_av_shopify_id', true) === '10450797822234');
    if (get_option('aurivita_site', null) === null) {
        $settings = av_site_defaults();
        if ($legacy) {
            $header = av_site_legacy_section('header');
            $footer = av_site_legacy_section('footer');
            $h = static fn($n) => av_site_legacy_field($header, $n);
            $f = static fn($n) => av_site_legacy_field($footer, $n);
            $global = get_option('aurivita_global', []);
            if (!is_array($global)) $global = [];
            $settings = array_replace($settings, [
                'profile' => 'aurivita', 'reference_product_id' => $product->get_id(), 'menu_title' => $h(4), 'menu_note' => $h(17),
                'header_logo' => av_site_legacy_logo($header, 'av-oie_2bNc7YhnbyGh-622ac49ff6.webp'),
                'footer_logo' => av_site_legacy_logo($footer, 'av-aurivita-footer-logo.webp'),
                'favicon' => get_option('site_icon', 0) ?: (function_exists('av_asset') ? av_asset('av-favicon.svg') : 0),
                'footer_about' => $f(0), 'footer_support_title' => $f(1), 'footer_links_title' => $f(2),
                'footer_policies_title' => $f(7), 'footer_disclosures' => [$f(13), $f(14), $f(15)],
                'footer_copyright' => str_replace([(string) wp_date('Y'), av_brand_name()], ['{year}', '{brand}'], $f(16)),
                'footer_note' => $f(17), 'contact_email' => (string) get_option('admin_email', ''),
                'contact_page' => av_site_legacy_page('iletisim'), 'privacy_page' => av_site_legacy_policy_page('wp_page_for_privacy_policy', 'gizlilik'),
                'terms_page' => av_site_legacy_policy_page('woocommerce_terms_page_id', 'kullanim-kosullari'), 'returns_page' => av_site_legacy_page('kargo-ve-iade'),
                'blog_page' => (int) get_option('page_for_posts', 0),
                'home_description' => is_string($global['av_home_description'] ?? null) ? $global['av_home_description'] : '',
            ]);
            if ($h(0) !== '' || $h(1) !== '' || $h(2) !== '' || $h(3) !== '') {
                $settings['announcement'] = '<span>' . esc_html($h(0)) . ' <strong>' . esc_html($h(1)) . '</strong><sup>‡</sup></span><span class="local-divider"></span><span><strong>' . esc_html($h(2)) . '</strong> ' . esc_html($h(3)) . '<sup>‡</sup></span>';
            }
        }
        add_option('aurivita_site', $settings);
    }
    if ($legacy && av_site('profile') === 'aurivita') {
        if (!get_option('aurivita_site_policies_migrated', false)) {
            foreach (['privacy_page' => 'wp_page_for_privacy_policy', 'terms_page' => 'woocommerce_terms_page_id'] as $key => $option) {
                $current = (int) get_option($option, 0);
                $target = absint(av_site($key));
                if ((!$current || get_post_type($current) !== 'page' || get_post_status($current) !== 'publish')
                    && $target && get_post_type($target) === 'page' && get_post_status($target) === 'publish') update_option($option, $target);
            }
            update_option('aurivita_site_policies_migrated', true);
        }
        if (!get_option('aurivita_site_menus_migrated', false)) av_seed_site_menus($product);
    }
}

function av_seed_site_menus(WC_Product $product): void {
    $header = av_site_legacy_section('header');
    $footer = av_site_legacy_section('footer');
    $h = static fn($n) => av_site_legacy_field($header, $n);
    $f = static fn($n) => av_site_legacy_field($footer, $n);
    $page = static fn($label, $id) => ['label' => $label, 'type' => 'post_type', 'object' => 'page', 'id' => $id];
    $url = static fn($label, $target) => ['label' => $label, 'type' => 'custom', 'url' => $target];
    $product_link = $product->get_permalink();
    $contact = absint(av_site('contact_page'));
    $returns = absint(av_site('returns_page'));
    $privacy = absint(av_site('privacy_page'));
    $terms = absint(av_site('terms_page'));
    $account = function_exists('wc_get_page_id') ? wc_get_page_id('myaccount') : 0;
    $menus = [
        'primary' => [
            $url($h(5), $product_link . '#product'), $url($h(6), $product_link . '#product-science'), $url($h(7), $product_link . '#reviews'),
            $page($h(8), $contact), $page($h(9), $account), $page($h(10), $account),
            $page($h(11), function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0), $url($h(12), home_url('/?s=')),
            $page($h(13), av_site_legacy_page('hakkimizda')), $page($h(14), $returns), $page($h(15), av_site_legacy_page('sss')),
            $page($h(16), absint(av_site('blog_page'))),
        ],
        'footer_support' => [$page($f(1), $contact)],
        'footer_links' => [$url($f(3), $product_link . '#product'), $page($f(4), $contact), $page($f(5), av_site_legacy_page('sss')), $page($f(6), $account)],
        'footer_policies' => [$page($f(8), $terms), $page($f(9), $privacy), $page($f(10), $returns), $page($f(11), $returns), $page($f(12), av_site_legacy_page('abonelik'))],
        'checkout_policies' => [$page(__('Shipping and returns', 'aurivita'), $returns), $page(__('Privacy policy', 'aurivita'), $privacy), $page(__('Terms of use', 'aurivita'), $terms), $page(__('Contact', 'aurivita'), $contact)],
    ];
    $locations = get_theme_mod('nav_menu_locations', []);
    if (!is_array($locations)) $locations = [];
    foreach ($menus as $location => $items) {
        if (!empty($locations[$location]) && wp_get_nav_menu_object((int) $locations[$location])) continue;
        $items = array_values(array_filter($items, static fn($item) => $item['label'] !== '' && ($item['type'] === 'custom'
            ? !empty($item['url']) : !empty($item['id']) && get_post_type($item['id']) === 'page' && get_post_status($item['id']) === 'publish')));
        if (!$items) continue;
        $owned = get_terms(['taxonomy' => 'nav_menu', 'hide_empty' => false, 'meta_key' => '_av_site_seed_location', 'meta_value' => $location]);
        $menu_id = !is_wp_error($owned) && $owned ? (int) $owned[0]->term_id : 0;
        if (!$menu_id) {
            $title = av_brand_name() . ' · ' . $location;
            $existing = wp_get_nav_menu_object($title);
            if ($existing) $title .= ' · ' . wp_unique_id();
            $created = wp_create_nav_menu($title);
            if (is_wp_error($created)) return;
            $menu_id = (int) $created;
            update_term_meta($menu_id, '_av_site_seed_location', $location);
        }
        $existing_items = wp_get_nav_menu_items($menu_id) ?: [];
        $keys = array_map(static fn($item) => (string) get_post_meta($item->ID, '_av_site_seed_item', true), $existing_items);
        foreach ($items as $position => $item) {
            $key = $location . ':' . $position;
            if (in_array($key, $keys, true)) continue;
            $values = ['menu-item-title' => $item['label'], 'menu-item-status' => 'publish', 'menu-item-type' => $item['type'], 'menu-item-position' => $position + 1];
            if ($item['type'] === 'post_type') $values += ['menu-item-object' => 'page', 'menu-item-object-id' => $item['id']];
            else $values['menu-item-url'] = $item['url'];
            $item_id = wp_update_nav_menu_item($menu_id, 0, $values);
            if (is_wp_error($item_id)) return;
            update_post_meta($item_id, '_av_site_seed_item', $key);
        }
        $locations[$location] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
    update_option('aurivita_site_menus_migrated', true);
}
