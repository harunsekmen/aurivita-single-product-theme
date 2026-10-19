<?php
defined('ABSPATH') || exit;
function av_is_checkout(): bool { return function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url(); }
function av_data(string $file): array {
    static $cache = [];
    if (!isset($cache[$file])) {
        $path = get_template_directory() . '/data/' . basename($file) . '.json';
        $cache[$file] = is_file($path) ? (json_decode((string) file_get_contents($path), true) ?: []) : [];
    }
    return $cache[$file];
}
function av_global(): array { return get_option('aurivita_global', av_data('global')); }
function av_asset(string $filename): string { return get_template_directory_uri() . '/assets/source/' . rawurlencode(basename($filename)); }
function av_url(string $path): string {
    if ($path === '' || str_starts_with($path, '#')) return $path;
    $host = wp_parse_url($path, PHP_URL_HOST);
    if ($host && !in_array($host, ['gwd5d1-x1.myshopify.com', wp_parse_url(home_url(), PHP_URL_HOST)], true)) return $path;
    $parts = wp_parse_url($path); $route = $parts['path'] ?? '/';
    $legacy_ids = get_option('aurivita_legacy_resource_ids', []);
    if (!empty($legacy_ids[$route]) && get_post_status((int)$legacy_ids[$route]) === 'publish') $url = get_permalink((int)$legacy_ids[$route]);
    elseif (str_starts_with($route, '/products/')) { $slug = basename($route); $post = get_page_by_path($slug, OBJECT, 'product'); $url = $post ? get_permalink($post) : wc_get_page_permalink('shop'); }
    elseif (str_starts_with($route, '/pages/')) $url = home_url('/' . basename($route) . '/');
    elseif (str_starts_with($route, '/blogs/rehber/')) $url = get_option('aurivita_article_id') && basename($route) === 'packs-secim-rehberi' ? get_permalink((int)get_option('aurivita_article_id')) : home_url('/' . basename($route) . '/');
    elseif ($route === '/blogs/rehber') $url = home_url('/rehber/');
    elseif (str_starts_with($route, '/collections')) $url = wc_get_page_permalink('shop');
    elseif ($route === '/account' || str_starts_with($route, '/account/')) {
        $endpoint = trim(substr($route,strlen('/account')), '/');
        $url = $endpoint ? wc_get_endpoint_url($endpoint,'',wc_get_page_permalink('myaccount')) : wc_get_page_permalink('myaccount');
    }
    elseif ($route === '/cart') $url = wc_get_cart_url();
    elseif ($route === '/checkout') $url = wc_get_checkout_url();
    elseif ($route === '/search') $url = home_url('/?s=');
    else $url = home_url($route);
    if (!empty($parts['query'])) $url .= (str_contains($url, '?') ? '&' : '?') . $parts['query'];
    return $url . (!empty($parts['fragment']) ? '#' . $parts['fragment'] : '');
}
function av_section_value(array $section, string $key, $default = '') { return $section['settings'][$key] ?? $default; }
function av_block_value(array $section, string $type, string $key, $default = '') {
    foreach (av_section_blocks($section) as $block) if (($block['type'] ?? '') === $type) return $block['settings'][$key] ?? $default;
    return ($section['presentation'] ?? '') === 'aurivita' ? '' : $default;
}
function av_block_has_setting(array $section, string $type, string $key): bool {
    foreach (av_section_blocks($section) as $block) if (($block['type'] ?? '') === $type) return array_key_exists($key, $block['settings'] ?? []);
    return false;
}
function av_block_allows_fallback(array $section, string $type, string $key): bool {
    foreach (av_section_blocks($section) as $block) if (($block['type'] ?? '') === $type) return !array_key_exists($key, $block['settings'] ?? []);
    return false;
}
function av_section_blocks(array $section): array {
    $blocks = $section['blocks'] ?? []; $ordered = [];
    foreach ($section['block_order'] ?? array_keys($blocks) as $id) if (isset($blocks[$id])) $ordered[] = $blocks[$id] + ['id'=>$id];
    return $ordered;
}
function av_main_product(): ?WC_Product { return function_exists('wc_get_product') ? wc_get_product((int) get_option('aurivita_main_product')) ?: null : null; }
function av_money($amount): string { return html_entity_decode(wp_strip_all_tags(wc_price((float)$amount)), ENT_QUOTES, 'UTF-8') . ' ' . get_woocommerce_currency(); }
function av_variations(?WC_Product $product = null): array {
    $product ??= av_main_product(); if (!$product || !$product->is_type('variable')) return [];
    $result = [];
    foreach ($product->get_children() as $id) {
        $v = wc_get_product($id); if (!$v || $v->get_price() === '') continue;
        $attributes = []; foreach ($v->get_attributes() as $key=>$value) $attributes['attribute_'.$key] = $value;
        $result[] = ['id'=>$id,'source_id'=>(string)get_post_meta($id,'_av_shopify_id',true),'servings'=>(int)$v->get_attribute('pa_porsiyon'),'packs'=>(int)$v->get_attribute('pa_paket'),'price'=>(float)wc_get_price_to_display($v),'regular_price'=>(float)wc_get_price_to_display($v,['price'=>$v->get_regular_price()]),'available'=>$v->is_purchasable() && $v->is_in_stock(),'stock_quantity'=>$v->get_stock_quantity(),'image'=>wp_get_attachment_image_url($v->get_image_id() ?: $product->get_image_id(),'full') ?: wc_placeholder_img_src(),'image_id'=>$v->get_image_id() ?: $product->get_image_id(),'image_alt'=>$product->get_name(),'attributes'=>$attributes,'sku'=>$v->get_sku()];
    }
    return $result;
}
function av_product_data(?WC_Product $product = null): array {
    $product ??= av_main_product(); if (!$product) return [];
    $variations = av_variations($product); $selected = null;
    $legacy = sanitize_text_field(wp_unslash($_GET['variant'] ?? ''));
    $defaults = $product->get_default_attributes();
    foreach ($variations as $v) {
        $matches = $legacy !== '' && ($legacy === $v['source_id'] || $legacy === (string)$v['id']);
        $query = true; $has = false;
        foreach ($v['attributes'] as $key=>$value) {
            if (isset($_GET[$key]) && is_string($_GET[$key])) {
                $has=true;
                $attribute = substr($key, 10);
                $wanted = wc_clean(wp_unslash($_GET[$key]));
                $wanted = taxonomy_exists($attribute) ? sanitize_title($wanted) : $wanted;
                $query = $query && ($value === '' || $wanted === $value);
            }
        }
        if ($matches || ($has && $query)) { $selected=$v; break; }
        if (!$selected && $defaults) {
            $default_matches = true;
            foreach ($defaults as $key => $value) $default_matches = $default_matches && isset($v['attributes']['attribute_'.$key]) && ($v['attributes']['attribute_'.$key] === '' || $v['attributes']['attribute_'.$key] === $value);
            if ($default_matches) $selected=$v;
        }
    }
    return ['id'=>$product->get_id(),'title'=>$product->get_name(),'url'=>$product->get_permalink(),'description'=>$product->get_description(),'image'=>wp_get_attachment_image_url($product->get_image_id(),'full') ?: wc_placeholder_img_src(),'variations'=>$variations,'selected'=>$selected ?? ($variations[0] ?? null)];
}
function av_product_section(): array {
    $product = is_product() ? wc_get_product(get_the_ID()) : av_main_product();
    $post = get_post($product?->get_id() ?? 0);
    if ($post) foreach (parse_blocks($post->post_content) as $block) if (($block['blockName'] ?? '') === 'aurivita/av-product') return $block['attrs']['section'] ?? [];
    return av_is_reference_product($product) ? (av_data('product')['sections']['product'] ?? []) : ['type'=>'av-product','settings'=>[]];
}
function av_render_section(array $section, string $id = ''): string {
    $type = $section['type'] ?? ''; $id = $id ?: wp_unique_id('av-');
    if (!preg_match('/^av-[a-z-]+$/', $type)) return '';
    $path = str_starts_with($type,'av-home-') ? 'home/' . substr($type,8) : (in_array($type,['av-product','av-sticky'],true) ? 'product/' . $type : 'content/' . $type);
    $reference_content = ($section['presentation'] ?? '') === 'aurivita' && av_site('profile') === 'aurivita';
    if (is_product()) $reference_content = $reference_content && av_is_reference_product(wc_get_product(get_the_ID())) && (int)($section['owner_product'] ?? 0) === get_the_ID();
    if (!$reference_content && !str_starts_with($type,'av-home-') && !in_array($type,['av-product','av-page'],true)) $path = 'content/generic';
    ob_start(); get_template_part('template-parts/' . $path, null, ['section'=>$section,'section_id'=>$id]); return ob_get_clean();
}
function av_render_template(string $name): void {
    $data = av_data($name); foreach ($data['order'] ?? [] as $id) if (isset($data['sections'][$id])) echo av_render_section($data['sections'][$id],$id);
}
function av_rich(string $html): string {
    $processor = new WP_HTML_Tag_Processor(wp_kses_post($html));
    while ($processor->next_tag('A')) { $href=$processor->get_attribute('href'); if (is_string($href)) $processor->set_attribute('href',av_url($href)); }
    return $processor->get_updated_html();
}
