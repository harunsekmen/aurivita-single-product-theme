<?php
defined('ABSPATH') || exit;

function av_checkout_policy_path(string $url): string {
    if ($url === '' || preg_match('/[\\\\\x00-\x20\x7f]/', $url)) return '';
    $home = wp_parse_url(home_url('/'));
    $parts = wp_parse_url($url);
    if (!is_array($home) || !is_array($parts) || isset($parts['user']) || isset($parts['pass'])) return '';
    if (isset($parts['host'])) {
        $scheme = strtolower($parts['scheme'] ?? $home['scheme']);
        $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
        $home_port = (int) ($home['port'] ?? ($home['scheme'] === 'https' ? 443 : 80));
        if (!in_array($scheme, ['http', 'https'], true) || $scheme !== $home['scheme']
            || strtolower($parts['host']) !== strtolower($home['host']) || $port !== $home_port) return '';
    } elseif (isset($parts['scheme']) || !str_starts_with($url, '/') || str_starts_with($url, '//')) {
        return '';
    }
    $path = wp_check_invalid_utf8(rawurldecode($parts['path'] ?? '/'));
    if ($path === '' || preg_match('/[\\\\\x00-\x1f\x7f]/', $path)) return '';
    $segments = [];
    foreach (explode('/', $path) as $segment) {
        if ($segment === '' || $segment === '.') continue;
        if ($segment === '..') array_pop($segments);
        else $segments[] = $segment;
    }
    $path = '/' . implode('/', $segments);
    $home_path = '/' . trim(rawurldecode($home['path'] ?? '/'), '/');
    if ($home_path !== '/' && $path !== $home_path && !str_starts_with($path, $home_path . '/')) return '';
    return $path;
}

function av_checkout_policy_page(int $id): ?WP_Post {
    if ($id <= 0) return null;
    $page = get_post($id);
    return $page instanceof WP_Post && $page->post_type === 'page' && $page->post_status === 'publish'
        && $page->post_password === '' && is_post_publicly_viewable($page) ? $page : null;
}

function av_checkout_policy_content(WP_Post $page, string $url): string {
    if (!av_checkout_policy_page((int) $page->ID)) return '';
    $context_keys = ['post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages'];
    $context = [];
    foreach ($context_keys as $key) $context[$key] = ['exists' => array_key_exists($key, $GLOBALS), 'value' => $GLOBALS[$key] ?? null];
    $block_remote_requests = static fn() => new WP_Error('av_policy_remote_request_disabled', 'Policy content uses saved local content.');
    $guard_page_block = static function($rendered, array $block) {
        if (($block['blockName'] ?? '') === 'aurivita/av-page') {
            $id = absint($block['attrs']['section']['settings']['page'] ?? 0);
            if ($id && !av_checkout_policy_page($id)) return '';
        }
        return $rendered;
    };
    $embed = $GLOBALS['wp_embed'] ?? null;
    $embed_handlers = $embed instanceof WP_Embed ? $embed->handlers : null;
    // Core oEmbed otherwise writes failed lookup caches even when HTTP is blocked.
    if ($embed instanceof WP_Embed) wp_embed_register_handler('av_checkout_policy_content', '~.*~s', static fn($matches, $attributes, $link) => '<a href="' . esc_url($link) . '">' . esc_html($link) . '</a>', PHP_INT_MIN);
    add_filter('pre_http_request', $block_remote_requests, PHP_INT_MAX);
    add_filter('pre_render_block', $guard_page_block, PHP_INT_MAX, 2);
    try {
        $GLOBALS['post'] = $page;
        setup_postdata($page);
        $content = (string) apply_filters('the_content', $page->post_content);
    } finally {
        remove_filter('pre_http_request', $block_remote_requests, PHP_INT_MAX);
        remove_filter('pre_render_block', $guard_page_block, PHP_INT_MAX);
        if ($embed instanceof WP_Embed) $embed->handlers = $embed_handlers;
        foreach ($context as $key => $saved) {
            if ($saved['exists']) $GLOBALS[$key] = $saved['value'];
            else unset($GLOBALS[$key]);
        }
    }
    if (!class_exists('DOMDocument')) return '';
    $document = new DOMDocument('1.0', 'UTF-8');
    if (!$document->loadHTML('<!doctype html><html><head><meta charset="UTF-8"></head><body>' . $content . '</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) return '';
    $xpath = new DOMXPath($document);
    $unsafe = $xpath->query('//script|//style|//form|//input|//textarea|//select|//button|//fieldset|//label|//iframe|//object|//embed|//svg|//math|//template|//dialog|//noscript|//base|//link|//meta');
    foreach ($unsafe as $node) $node->parentNode?->removeChild($node);
    $body = $document->getElementsByTagName('body')->item(0);
    $content = '';
    if ($body) foreach ($body->childNodes as $node) $content .= $document->saveHTML($node);
    $processor = new WP_HTML_Tag_Processor(wp_kses_post($content));
    while ($processor->next_tag()) {
        foreach (['id', 'name', 'for', 'headers', 'aria-labelledby', 'aria-describedby', 'aria-controls', 'aria-owns', 'aria-details', 'aria-activedescendant'] as $attribute) $processor->remove_attribute($attribute);
        foreach ($processor->get_attribute_names_with_prefix('data-') ?? [] as $attribute) $processor->remove_attribute($attribute);
        if ($processor->get_tag() === 'A') {
            $href = $processor->get_attribute('href');
            if (is_string($href) && str_starts_with($href, '#')) $processor->set_attribute('href', strtok($url, '#') . $href);
            if ($processor->get_attribute('target') === '_blank') $processor->set_attribute('rel', 'noopener noreferrer');
        }
    }
    return wp_kses_post($processor->get_updated_html());
}

/** @return array<int, array{id: int, path: string, title: string, url: string, content: string}> */
function av_checkout_policy_documents(): array {
    $locations = get_nav_menu_locations();
    $menu_id = absint($locations['checkout_policies'] ?? 0);
    $candidates = [];
    foreach ($menu_id ? (wp_get_nav_menu_items($menu_id) ?: []) : [] as $item) {
        $link = (string) $item->url;
        $path = av_checkout_policy_path($link);
        if ($path === '' || !empty($item->_invalid)) continue;
        if ($item->type === 'post_type' && $item->object === 'page') $id = absint($item->object_id);
        elseif ($item->type === 'custom') {
            if (!wp_parse_url($link, PHP_URL_HOST)) {
                $origin = wp_parse_url(home_url('/'));
                $link = $origin['scheme'] . '://' . $origin['host'] . (isset($origin['port']) ? ':' . $origin['port'] : '') . $link;
            } elseif (str_starts_with($link, '//')) $link = wp_parse_url(home_url('/'), PHP_URL_SCHEME) . ':' . $link;
            $id = (int) url_to_postid($link);
        } else continue;
        $candidates[] = ['id' => $id, 'path' => $path, 'title' => wp_strip_all_tags((string) $item->title)];
    }
    if (function_exists('wc_terms_and_conditions_page_id')) $candidates[] = ['id' => (int) wc_terms_and_conditions_page_id()];
    if (function_exists('wc_privacy_policy_page_id')) $candidates[] = ['id' => (int) wc_privacy_policy_page_id()];
    $candidates[] = ['id' => (int) get_option('wp_page_for_privacy_policy', 0)];
    $documents = [];
    $rendered = [];
    $home_path = av_checkout_policy_path(home_url('/'));
    foreach ($candidates as $candidate) {
        $page = av_checkout_policy_page($candidate['id']);
        if (!$page) continue;
        $url = (string) get_permalink($page);
        $path = av_checkout_policy_path($url);
        // Path-only matching cannot distinguish query-based page permalinks at the site root.
        if ($path === '' || $path === $home_path || isset($documents[$path])
            || isset($candidate['path']) && $candidate['path'] !== $path) continue;
        if (!isset($rendered[$page->ID])) $rendered[$page->ID] = av_checkout_policy_content($page, $url);
        $documents[$path] = [
            'id' => (int) $page->ID,
            'path' => $path,
            'title' => !empty($candidate['title']) ? $candidate['title'] : wp_strip_all_tags((string) get_the_title($page)),
            'url' => $url,
            'content' => $rendered[$page->ID],
        ];
    }
    return array_values($documents);
}

function av_checkout_policy_dialog(): void {
    static $rendered = false;
    if ($rendered || !function_exists('av_is_checkout') || !av_is_checkout()) return;
    $documents = av_checkout_policy_documents();
    if (!$documents) return;
    $rendered = true;
    foreach ($documents as $document): ?>
        <template data-av-policy-document data-policy-path="<?php echo esc_attr($document['path']); ?>" data-policy-title="<?php echo esc_attr($document['title']); ?>" data-policy-url="<?php echo esc_url($document['url']); ?>"><?php echo $document['content']; ?></template>
    <?php endforeach; ?>
    <dialog id="av-checkout-policy-dialog" class="av-checkout-policy-dialog" aria-labelledby="av-checkout-policy-title">
        <div class="av-checkout-policy-dialog__body" data-policy-scroll>
            <header class="av-checkout-policy-dialog__header">
                <h2 id="av-checkout-policy-title" data-policy-title><?php echo esc_html__('Shop bilgileri', 'aurivita'); ?></h2>
                <button type="button" class="av-checkout-policy-dialog__close" data-policy-close aria-label="<?php echo esc_attr__('Close', 'aurivita'); ?>" autofocus>
                    <svg aria-hidden="true" fill="none" focusable="false" viewBox="0 0 16 16"><path d="M3.5 3.5l9 9m0-9l-9 9" stroke="currentColor" stroke-linecap="round" stroke-width="1.6"></path></svg>
                </button>
            </header>
            <div class="av-checkout-policy-dialog__content" data-policy-content></div>
        </div>
    </dialog>
    <?php
}

function av_checkout_address_block_title(array $block): array {
    if (is_admin() || !function_exists('av_is_checkout') || !av_is_checkout()) return $block;
    $name = $block['blockName'] ?? '';
    if ($name === 'woocommerce/checkout-shipping-address-block') $block['attrs']['title'] = __('Shipping address', 'aurivita');
    elseif ($name === 'woocommerce/checkout-billing-address-block' && function_exists('WC') && WC()->cart) {
        $block['attrs']['title'] = WC()->cart->needs_shipping() ? __('Billing address', 'aurivita') : __('Shipping address', 'aurivita');
    }
    return $block;
}
add_filter('render_block_data', 'av_checkout_address_block_title', 20);

function av_checkout_invoice_block_layout(array $block): array {
    $name = $block['blockName'] ?? '';
    if (!in_array($name, ['woocommerce/checkout-fields-block', 'woocommerce/checkout-additional-information-block'], true)) return $block;
    if (is_admin() || !function_exists('av_is_checkout') || !av_is_checkout()
        || !function_exists('av_register_corporate_invoice_fields')) return $block;
    $fields = wc_get_container()->get(\Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class)->get_fields_for_location('order');
    $invoice_ids = ['aurivita/corporate-invoice', 'aurivita/company-name', 'aurivita/tax-number', 'aurivita/tax-office'];
    if (array_keys($fields) !== $invoice_ids) return $block;
    if ($name === 'woocommerce/checkout-additional-information-block') {
        $block['attrs']['className'] = trim(($block['attrs']['className'] ?? '') . ' av-checkout-invoice');
    } elseif ($name === 'woocommerce/checkout-fields-block') {
        $children = $block['innerBlocks'] ?? [];
        $names = array_column($children, 'blockName');
        $invoice = array_search('woocommerce/checkout-additional-information-block', $names, true);
        $billing = array_search('woocommerce/checkout-billing-address-block', $names, true);
        $slots = array_filter($block['innerContent'] ?? [], static fn($part) => $part === null);
        // Native blocks consume null placeholders in sequence; only reorder a complete child list.
        if ($invoice !== false && $billing !== false && $invoice > $billing && count($slots) === count($children)) {
            $moved = array_splice($children, $invoice, 1);
            array_splice($children, $billing + 1, 0, $moved);
            $block['innerBlocks'] = $children;
        }
    }
    return $block;
}
add_filter('render_block_data', 'av_checkout_invoice_block_layout', 25);
