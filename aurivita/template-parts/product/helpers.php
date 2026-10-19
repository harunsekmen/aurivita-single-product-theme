<?php
defined('ABSPATH') || exit;

if (!function_exists('avp_context')) {
    function avp_context(array $args): array
    {
        $product = $args['product'] ?? null;
        if (!$product instanceof WC_Product) {
            $selection = $args['section']['settings']['product'] ?? '';
            if ($selection !== '') {
                $record = is_numeric($selection) ? null : get_page_by_path(basename((string)wp_parse_url((string)$selection,PHP_URL_PATH)),OBJECT,'product');
                $product = wc_get_product(is_numeric($selection) ? (int)$selection : ($record->ID ?? 0));
                if (!$product) return [];
            }
        }
        if (!$product instanceof WC_Product) {
            $current = $GLOBALS['product'] ?? null;
            $product = is_product() && $current instanceof WC_Product ? $current : av_main_product();
        }
        if (!$product instanceof WC_Product) {
            return [];
        }
        $data = av_product_data($product);
        $variations = $data['variations'] ?? av_variations($product);
        $selected = $data['selected'] ?? null;
        if (!is_array($selected)) {
            $selected = current(array_filter($variations, static fn($v) => (int) $v['id'] === (int) $selected)) ?: null;
        }
        $reference = av_is_reference_product($product) && $product->is_type('variable');
        if ($reference) {
            $axes = array_keys($product->get_variation_attributes());
            $reference = count($axes) === 2 && !array_diff($axes,['pa_porsiyon','pa_paket']);
            foreach ($variations as $variation) $reference = $reference && in_array($variation['servings'],[30,60],true) && in_array($variation['packs'],[1,3,5],true);
        }
        foreach ($variations as &$variation) {
            $variation['money'] = html_entity_decode(wp_strip_all_tags(av_money($variation['price'])), ENT_QUOTES, 'UTF-8');
            $variation['compare_money'] = html_entity_decode(wp_strip_all_tags(av_money($variation['regular_price'])), ENT_QUOTES, 'UTF-8');
            $variation['label'] = avp_variation_label($variation, $product);
        }
        unset($variation);
        if ($selected) {
            foreach ($variations as $variation) {
                if ((int) $variation['id'] === (int) $selected['id']) {
                    $selected = $variation;
                    break;
                }
            }
        }
        $query_attributes = [];
        foreach ($product->is_type('variable') ? $product->get_variation_attributes() : [] as $attribute => $values) {
            $key = 'attribute_' . sanitize_title($attribute);
            if (isset($_GET[$key]) && is_string($_GET[$key])) {
                $value = wc_clean(wp_unslash($_GET[$key]));
                $query_attributes[$key] = taxonomy_exists($attribute) ? sanitize_title($value) : $value;
            }
        }
        if ($query_attributes) {
            $selected = null;
            foreach ($variations as $variation) {
                $matches = true;
                foreach ($query_attributes as $key => $value) {
                    $matches = $matches && isset($variation['attributes'][$key]) && ($variation['attributes'][$key] === '' || $variation['attributes'][$key] === $value);
                }
                if ($matches) {
                    $selected = $variation;
                    break;
                }
            }
        }
        if ($selected) {
            foreach ($selected['attributes'] as $key => &$value) {
                if ($value === '') {
                    $attribute = preg_replace('/^attribute_/', '', $key);
                    $values = $product->get_variation_attributes()[$attribute] ?? [];
                    $value = $query_attributes[$key] ?? $product->get_default_attributes()[$attribute] ?? ($values[0] ?? '');
                }
            }
            unset($value);
        }
        $data['variations'] = $variations;
        $data['selected'] = $selected;
        $data['id'] = $product->get_id();
        $data['title'] = $product->get_name();
        $data['url'] = $product->get_permalink();
        $data['variable'] = $product->is_type('variable');
        $data['available'] = $product->is_purchasable() && $product->is_in_stock();
        $data['price'] = (float) wc_get_price_to_display($product);
        $data['regular_price'] = (float) wc_get_price_to_display($product, ['price'=>$product->get_regular_price()]);
        $data['money'] = html_entity_decode(wp_strip_all_tags(av_money($data['price'])), ENT_QUOTES, 'UTF-8');
        $data['compare_money'] = html_entity_decode(wp_strip_all_tags(av_money($data['regular_price'])), ENT_QUOTES, 'UTF-8');
        $section = $args['section'] ?? av_product_section();
        $section_id = sanitize_html_class((string) ($args['section_id'] ?? ('product-' . $product->get_id())));
        $available = $data['variable'] ? !empty($selected['available']) : $data['available'];
        return compact('product', 'data', 'variations', 'selected', 'reference', 'section', 'section_id', 'available');
    }

    function avp_variation_label(array $variation, WC_Product $product): string
    {
        $labels = [];
        foreach (($variation['attributes'] ?? []) as $key => $value) {
            $taxonomy = preg_replace('/^attribute_/', '', $key);
            $term = taxonomy_exists($taxonomy) ? get_term_by('slug', $value, $taxonomy) : false;
            $labels[] = $term && !is_wp_error($term) ? $term->name : (string) $value;
        }
        return implode(' · ', array_filter($labels)) ?: $product->get_name();
    }

    function avp_card(array $variations, ?array $selected, int $packs): ?array
    {
        $servings = (int) ($selected['servings'] ?? 30);
        foreach ($variations as $variation) {
            if ((int) ($variation['servings'] ?? 0) === $servings && (int) ($variation['packs'] ?? 0) === $packs) {
                return $variation;
            }
        }
        return null;
    }

    function avp_media_url($value): string
    {
        if (is_numeric($value)) {
            return (string) wp_get_attachment_url((int) $value);
        }
        return is_string($value) ? $value : '';
    }

    function avp_media_image($value, string $class, string $alt, string $sizes): string
    {
        if (is_numeric($value)) {
            return (string) wp_get_attachment_image((int) $value, 'full', false, compact('class', 'alt', 'sizes') + ['loading' => 'lazy']);
        }
        $url = avp_media_url($value);
        return $url ? '<img src="' . esc_url($url) . '" class="' . esc_attr($class) . '" alt="' . esc_attr($alt) . '" sizes="' . esc_attr($sizes) . '" loading="lazy" />' : '';
    }

    function avp_media_video($value): string
    {
        $url = avp_media_url($value);
        return $url ? '<video class="cc-pdp__video-video" src="' . esc_url($url) . '" muted playsinline preload="none" controls></video>' : '';
    }

    function avp_gallery(WC_Product $product, bool $reference): array
    {
        $gallery = [];
        foreach (array_unique(array_filter(array_merge([$product->get_image_id()], $product->get_gallery_image_ids()))) as $id) {
            $url = wp_get_attachment_image_url($id, 'full');
            if ($url) {
                $gallery[] = ['id' => $id, 'url' => $url, 'attachment' => $id, 'alt' => get_post_meta($id, '_wp_attachment_image_alt', true) ?: $product->get_name()];
            }
        }
        if (!$gallery && $reference) {
            foreach ([
                'av-hf_20260911_212915_1215f0f4-3814-4cb8-a663-c76faaa5b7ca-efbdad5590.webp',
                'av-Aurivita_package_replace_3-d42a7d9e65.webp',
                'av-Aurivita_package_replace_2-c7db27a39f.webp',
                'av-Product_Images_2-6b7a437c8c.webp',
                'av-New_PDP_Images_1-eaf4d9112c.webp',
                'av-hf_20260830_222941_dc763611-16cb-42bf-b636-4f3c73e70bfd-93bd4ac9fe.webp',
            ] as $index => $filename) {
                $gallery[] = ['id' => 'source-' . $index, 'url' => av_asset($filename), 'filename' => $filename, 'width' => in_array($index, [0, 5], true) ? 1200 : 1080, 'alt' => $product->get_name()];
            }
        }
        if (!$gallery) {
            $gallery[] = ['id' => 'placeholder', 'url' => wc_placeholder_img_src(), 'alt' => $product->get_name()];
        }
        return $gallery;
    }

    function avp_gallery_image(array $image, string $class, string $sizes, bool $first = false): string
    {
        if (!empty($image['attachment'])) {
            return (string) wp_get_attachment_image((int) $image['attachment'], $class === 'cc-pdp__thumb-image' ? 'woocommerce_gallery_thumbnail' : 'full', false, [
                'class' => $class, 'alt' => $image['alt'], 'sizes' => $sizes, 'loading' => $first ? 'eager' : 'lazy', 'fetchpriority' => $first ? 'high' : 'low',
            ]);
        }
        $srcset = '';
        if (!empty($image['filename'])) {
            $stem = pathinfo($image['filename'], PATHINFO_FILENAME);
            foreach ([160, 320, 480, 640, 900] as $width) {
                $srcset .= av_asset($stem . '-w' . $width . '.webp') . ' ' . $width . 'w, ';
            }
            $srcset .= $image['url'] . ' ' . $image['width'] . 'w';
        }
        return '<img src="' . esc_url($image['url']) . '"' . ($srcset ? ' srcset="' . esc_attr($srcset) . '"' : '') . ' class="' . esc_attr($class) . '" alt="' . esc_attr($image['alt']) . '" sizes="' . esc_attr($sizes) . '" loading="' . ($first ? 'eager' : 'lazy') . '" fetchpriority="' . ($first ? 'high' : 'low') . '"' . (isset($image['width']) ? ' width="' . (int) $image['width'] . '" height="' . (int) $image['width'] . '"' : '') . ' />';
    }

    function avp_native_picker(array $context): void
    {
        extract($context, EXTR_SKIP);
        if (!$product->is_type('variable')) {
            return;
        }
        ?>
        <noscript>
            <style>av-product .cc-pdp-dqs, av-product [data-av-options]{display:none!important} av-product .cc-pdp-faqs__answer-wrap{grid-template-rows:1fr!important} av-product .cc-pdp-faqs__answer{opacity:1!important;transform:none!important} av-product video{pointer-events:auto}</style>
            <form method="get" action="<?php echo esc_url($product->get_permalink()); ?>" class="fw-native-options">
                <?php foreach ($product->get_variation_attributes() as $attribute => $values) :
                    $key = 'attribute_' . sanitize_title($attribute); ?>
                    <label for="<?php echo esc_attr('AvNative-' . $section_id . '-' . $key); ?>"><?php echo esc_html(wc_attribute_label($attribute, $product)); ?></label>
                    <select class="av-native-select" id="<?php echo esc_attr('AvNative-' . $section_id . '-' . $key); ?>" name="<?php echo esc_attr($key); ?>" required>
                        <?php foreach ($values as $value) :
                            $term = taxonomy_exists($attribute) ? get_term_by('slug', $value, $attribute) : false; ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($selected['attributes'][$key] ?? (isset($_GET[$key]) && is_string($_GET[$key]) ? wc_clean(wp_unslash($_GET[$key])) : ''), $value); ?>><?php echo esc_html($term && !is_wp_error($term) ? $term->name : $value); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endforeach; ?>
                <button type="submit" class="av-button">Update selection and price</button>
            </form>
        </noscript>
        <?php
    }
}
