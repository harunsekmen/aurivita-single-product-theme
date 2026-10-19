<?php
defined('ABSPATH') || exit;

function av_asset_version(string $relative): string {
    static $versions = [];
    if (!isset($versions[$relative])) {
        $path = get_template_directory() . '/' . ltrim($relative, '/');
        $versions[$relative] = is_file($path) ? substr((string) hash_file('sha256', $path), 0, 16) : 'missing';
    }
    return $versions[$relative];
}

function av_asset_manifest(): array {
    static $manifest;
    if ($manifest === null) {
        $path = get_template_directory() . '/assets/build/manifest.json';
        $decoded = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
        $manifest = is_array($decoded) && ($decoded['schema_version'] ?? 0) === 1 ? $decoded : [];
    }
    return $manifest;
}

function av_asset_reference_section(array $section): bool {
    if (($section['presentation'] ?? '') !== 'aurivita'
        || (function_exists('av_site') && av_site('profile') !== 'aurivita')) return false;
    if (function_exists('is_product') && is_product()) {
        $id = get_queried_object_id();
        return function_exists('av_is_reference_product') && av_is_reference_product(wc_get_product($id))
            && (int) ($section['owner_product'] ?? 0) === $id;
    }
    return true;
}

function av_asset_block_types(array $blocks, array &$seen, int $depth = 0): array {
    $types = [];
    if ($depth > 32) return ['__unknown__'];
    foreach ($blocks as $block) {
        if (!is_array($block)) continue;
        $name = (string) ($block['blockName'] ?? '');
        if (str_starts_with($name, 'aurivita/')) {
            $type = substr($name, 9);
            if (!in_array($type, ['av-page', 'av-header', 'av-footer'], true)) {
                $content = ['av-reviews', 'av-problem', 'av-mechanism', 'av-routine', 'av-timeline',
                    'av-stats', 'av-compare', 'av-marquee', 'av-faq', 'av-science', 'av-sticky'];
                $section = $block['attrs']['section'] ?? [];
                $generic_content = in_array($type, $content, true)
                    && !av_asset_reference_section(is_array($section) ? $section : []);
                $types[] = str_starts_with($type, 'av-home-') || $generic_content ? 'home' : $type;
            }
        }
        $types = array_merge($types, av_asset_block_types($block['innerBlocks'] ?? [], $seen, $depth + 1));
        if ($name === 'core/block' && !empty($block['attrs']['ref'])) {
            $id = (int) $block['attrs']['ref'];
            if (isset($seen[$id])) continue;
            $seen[$id] = true;
            $post = get_post($id);
            if ($post instanceof WP_Post) {
                $types = array_merge($types, av_asset_block_types(parse_blocks($post->post_content), $seen, $depth + 1));
            }
        }
        if ($name === 'core/template-part' && function_exists('get_block_template')) {
            $slug = (string) ($block['attrs']['slug'] ?? '');
            $theme = (string) ($block['attrs']['theme'] ?? get_stylesheet());
            $key = 'template:' . $theme . '/' . $slug;
            if ($slug !== '' && !isset($seen[$key])) {
                $seen[$key] = true;
                $template = get_block_template($theme . '/' . $slug, 'wp_template_part');
                if ($template && !empty($template->content)) {
                    $types = array_merge($types, av_asset_block_types(parse_blocks($template->content), $seen, $depth + 1));
                }
            }
        }
    }
    return array_values(array_unique($types));
}

function av_asset_context_types(): array {
    global $wp_query;
    $seen = [];
    $types = [];
    $object = get_queried_object();
    $posts = $object instanceof WP_Post ? [$object] : ($wp_query->posts ?? []);
    foreach ($posts as $post) {
        if ($post instanceof WP_Post) {
            $types = array_merge($types, av_asset_block_types(parse_blocks($post->post_content), $seen));
        }
    }
    if (function_exists('is_product') && is_product()) $types[] = 'av-product';
    $types = apply_filters('aurivita_asset_types', array_values(array_unique($types)));
    return is_array($types) ? array_values(array_unique(array_filter($types, 'is_string'))) : ['__unknown__'];
}

function av_asset_profile(array $types, array $manifest, bool $generic = false): array {
    if (!$types) return [];
    if ($generic) {
        return $manifest['profiles'][in_array('av-product', $types, true) ? 'generic-product' : 'generic-content'] ?? [];
    }
    $best = [];
    foreach ($manifest['profiles'] ?? [] as $name => $profile) {
        if ($name === 'common' || !is_array($profile) || !isset($profile['types'], $profile['bytes'], $profile['file'])) continue;
        if (($profile['renderer'] ?? 'reference') !== 'reference') continue;
        if (array_diff($types, $profile['types'])) continue;
        if (!$best || (int) $profile['bytes'] < (int) $best['bytes']) $best = $profile;
    }
    return $best;
}

function av_asset_bundle_exists(array $entry): bool {
    $file = $entry['file'] ?? '';
    return is_string($file) && preg_match('/^assets\/build\/[a-z0-9-]+\.[a-f0-9]{16}\.css$/', $file)
        && is_file(get_template_directory() . '/' . $file);
}

function av_enqueue_asset_script(string $name, array $dependencies = []): void {
    $relative = 'assets/' . $name . '.js';
    if (!is_file(get_template_directory() . '/' . $relative)) return;
    wp_enqueue_script('aurivita-' . $name, get_template_directory_uri() . '/' . $relative,
        $dependencies, av_asset_version($relative), ['in_footer' => true, 'strategy' => 'defer']);
}

function av_enqueue_assets(): void {
    $manifest = av_asset_manifest();
    $checkout = function_exists('av_is_checkout') ? av_is_checkout() : (function_exists('is_checkout') && is_checkout());
    $types = $checkout ? [] : av_asset_context_types();
    $generic = function_exists('av_site') && av_site('profile') !== 'aurivita';
    if (function_exists('is_product') && is_product() && function_exists('av_is_reference_product')) {
        $generic = $generic || !av_is_reference_product(wc_get_product(get_queried_object_id()));
    }
    $common = $manifest['profiles']['common'] ?? [];
    $profile = av_asset_profile($types, $manifest, $generic);
    $bundled = av_asset_bundle_exists($common) && (!$types || av_asset_bundle_exists($profile));
    wp_enqueue_style('aurivita-fonts', get_template_directory_uri() . '/assets/fonts.css', [], av_asset_version('assets/fonts.css'));
    $variables = ':root{';
    foreach (av_global() as $key => $value) {
        if (is_string($value) && preg_match('/^#[0-9a-f]{6}$/i', $value)) {
            $variables .= '--' . str_replace('_', '-', sanitize_key((string) $key)) . ':' . $value . ';';
        }
    }
    wp_add_inline_style('aurivita-fonts', $variables . '}');
    $previous = 'aurivita-fonts';
    if ($bundled) {
        wp_enqueue_style('aurivita-common', get_template_directory_uri() . '/' . $common['file'],
            [$previous], av_asset_version($common['file']));
        $previous = 'aurivita-common';
        if ($profile) {
            wp_enqueue_style('aurivita-sections', get_template_directory_uri() . '/' . $profile['file'],
                [$previous], av_asset_version($profile['file']));
            $previous = 'aurivita-sections';
        }
    } else {
        // A missing build or unknown custom block must preserve the source cascade.
        foreach (av_style_files() as $filename) {
            $handle = 'aurivita-' . sanitize_title($filename);
            wp_enqueue_style($handle, av_asset($filename), [$previous], av_asset_version('assets/source/' . $filename));
            $previous = $handle;
        }
        foreach (['site', 'generic'] as $name) {
            $relative = 'assets/' . $name . '.css';
            if (!is_file(get_template_directory() . '/' . $relative)) continue;
            $handle = 'aurivita-' . $name;
            wp_enqueue_style($handle, get_template_directory_uri() . '/' . $relative, [$previous], av_asset_version($relative));
            $previous = $handle;
        }
    }
    wp_enqueue_style('aurivita-wp', get_template_directory_uri() . '/assets/wp.css', [$previous], av_asset_version('assets/wp.css'));
    if ($checkout) {
        wp_enqueue_style('aurivita-checkout', get_template_directory_uri() . '/assets/checkout.css',
            ['aurivita-wp', 'wc-blocks-style', 'wc-blocks-style-checkout', 'wc-blocks-packages-style'],
            av_asset_version('assets/checkout.css'));
        av_enqueue_asset_script('checkout');
        return;
    }
    if (function_exists('is_account_page') && is_account_page()) {
        wp_enqueue_style('aurivita-account', get_template_directory_uri() . '/assets/account.css',
            ['aurivita-wp', 'woocommerce-general'], av_asset_version('assets/account.css'));
        av_enqueue_asset_script('account');
    }
    wp_enqueue_style('aurivita-cart-drawer', get_template_directory_uri() . '/assets/cart-drawer.css',
        ['aurivita-wp'], av_asset_version('assets/cart-drawer.css'));
    av_enqueue_asset_script('shell');
    av_enqueue_asset_script('cart', ['aurivita-shell']);
    if (in_array('home', $types, true)) {
        $path = get_template_directory() . '/assets/home.js';
        $source = is_file($path) ? (string) file_get_contents($path) : '';
        $code = preg_replace('~/\*.*?\*/|//[^\r\n]*~s', '', $source);
        if (trim((string) $code) !== '') av_enqueue_asset_script('home', ['aurivita-cart']);
    }
    if (in_array('av-product', $types, true)) av_enqueue_asset_script('product', ['aurivita-cart', 'aurivita-shell']);
    $interactive = $manifest['interactive_content_types'] ??
        ['av-reviews', 'av-problem', 'av-timeline', 'av-stats', 'av-compare', 'av-faq', 'av-science'];
    if (!$generic && array_intersect($types, $interactive)) av_enqueue_asset_script('content', ['aurivita-cart', 'aurivita-shell']);
    wp_localize_script('aurivita-cart', 'AurivitaConfig', [
        'storeApi' => esc_url_raw(rest_url('wc/store/v1/')), 'nonce' => wp_create_nonce('wc_store_api'),
        'restNonce' => wp_create_nonce('wp_rest'), 'checkout' => wc_get_checkout_url(), 'cart' => wc_get_cart_url(),
        'locale' => str_replace('_', '-', get_locale()), 'currency' => get_woocommerce_currency(),
        'cartTaxIncluded' => get_option('woocommerce_tax_display_cart') === 'incl',
    ]);
}
