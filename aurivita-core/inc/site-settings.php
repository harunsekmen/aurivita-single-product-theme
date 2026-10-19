<?php
defined('ABSPATH') || exit;

function av_register_site_settings(): void {
    add_action('admin_menu', static function () {
        add_theme_page(__('Marka ve Site', 'aurivita'), __('Marka ve Site', 'aurivita'), 'manage_options', 'aurivita-settings', 'av_site_settings_page');
    });
    add_action('admin_init', static function () {
        register_setting('aurivita_site_settings', 'aurivita_site', ['type' => 'array', 'sanitize_callback' => 'av_sanitize_site_settings']);
        register_setting('aurivita_site_settings', 'aurivita_main_product', ['type' => 'integer', 'sanitize_callback' => 'av_sanitize_main_product']);
        register_setting('aurivita_site_settings', 'aurivita_global', ['type' => 'array', 'sanitize_callback' => 'av_sanitize_site_colors']);
    });
    add_filter('pre_update_option_aurivita_site', static function ($site, $old) { av_site_settings_sync_policy_roles($old, $site); return $site; }, 10, 2);
    add_action('add_option_aurivita_site', static function ($option, $site) { av_site_settings_sync_policy_roles([], $site); }, 10, 2);
}

function av_site_settings_sync_policy_roles($old, $site): void {
    if (!current_user_can('manage_options') || !is_array($site) || ($_POST['option_page'] ?? '') !== 'aurivita_site_settings'
        || !is_string($_POST['_wpnonce'] ?? null) || !wp_verify_nonce(wp_unslash($_POST['_wpnonce']), 'aurivita_site_settings-options')) return;
    $submitted = $_POST['aurivita_site'] ?? [];
    if (!is_array($submitted)) return;
    foreach (['privacy_page' => 'wp_page_for_privacy_policy', 'terms_page' => 'woocommerce_terms_page_id'] as $key => $option) {
        if (!isset($submitted[$key]) || !is_scalar($submitted[$key])) continue;
        $id = absint($submitted[$key]);
        if ($id === absint($site[$key] ?? 0) && (!$id || get_post_type($id) === 'page')) update_option($option, $id);
    }
    foreach (['brand_name' => 'blogname', 'tagline' => 'blogdescription'] as $key => $option) {
        if (isset($submitted[$key]) && is_scalar($submitted[$key]) && is_string($site[$key] ?? null)
            && ($key !== 'brand_name' || trim($site[$key]) !== '')) update_option($option, $site[$key]);
    }
    if (isset($submitted['favicon']) && is_scalar($submitted['favicon']) && is_numeric($site['favicon'] ?? null)
        && absint($submitted['favicon']) === absint($site['favicon'])) update_option('site_icon', absint($site['favicon']));
    if (isset($submitted['blog_page']) && is_scalar($submitted['blog_page'])) {
        $blog = absint($submitted['blog_page']);
        if ($blog === absint($site['blog_page'] ?? 0) && (!$blog || get_post_type($blog) === 'page' && get_post_status($blog) === 'publish')) update_option('page_for_posts', $blog);
    }
}

function av_site_text_fields(): array {
    return [
        'brand_name' => __('Marka adı', 'aurivita'), 'tagline' => __('Slogan', 'aurivita'),
        'menu_title' => __('Menü başlığı', 'aurivita'), 'menu_note' => __('Menü alt metni', 'aurivita'),
        'footer_about' => __('Footer marka metni', 'aurivita'), 'footer_support_title' => __('Destek bağlantısı başlığı', 'aurivita'),
        'footer_links_title' => __('Footer bağlantıları başlığı', 'aurivita'), 'footer_policies_title' => __('Footer politikaları başlığı', 'aurivita'),
        'footer_copyright' => __('Telif metni', 'aurivita'), 'footer_note' => __('Footer alt notu', 'aurivita'),
        'home_description' => __('Ana sayfa açıklaması', 'aurivita'), 'checkout_title' => __('Ödeme başlığı', 'aurivita'),
        'checkout_description' => __('Ödeme açıklaması', 'aurivita'), 'checkout_return_text' => __('Alışverişe dön bağlantısı', 'aurivita'),
    ];
}

function av_site_page_fields(): array {
    return ['contact_page' => __('İletişim sayfası', 'aurivita'), 'privacy_page' => __('Gizlilik sayfası', 'aurivita'),
        'terms_page' => __('Kullanım koşulları sayfası', 'aurivita'), 'returns_page' => __('Kargo ve iade sayfası', 'aurivita'), 'blog_page' => __('Blog sayfası', 'aurivita')];
}

function av_site_color_fields(): array {
    return ['av_ink' => __('Metin', 'aurivita'), 'av_accent' => __('Vurgu', 'aurivita'), 'av_action' => __('Buton', 'aurivita'),
        'av_dark' => __('Koyu alan', 'aurivita'), 'av_surface' => __('Yüzey', 'aurivita'), 'av_soft' => __('Açık alan', 'aurivita'),
        'av_gold' => __('İkincil vurgu', 'aurivita'), 'av_muted' => __('İkincil metin', 'aurivita'), 'av_border' => __('Çizgi', 'aurivita')];
}

function av_sanitize_site_settings($input): array {
    $saved = function_exists('av_site') ? av_site() : get_option('aurivita_site', []);
    if (!is_array($saved)) $saved = [];
    if (!is_array($input)) return $saved;
    $site = $saved;
    $site['version'] = 1;
    foreach (av_site_text_fields() as $key => $label) {
        if (array_key_exists($key, $input) && is_scalar($input[$key])) $site[$key] = sanitize_textarea_field((string) $input[$key]);
    }
    if (isset($input['announcement']) && is_scalar($input['announcement'])) {
        $site['announcement'] = wp_kses((string) $input['announcement'], ['span' => ['class' => true], 'strong' => [], 'em' => [], 'sup' => [], 'br' => []]);
    }
    foreach (['header_logo', 'footer_logo', 'favicon'] as $key) {
        if (!array_key_exists($key, $input) || !is_scalar($input[$key])) continue;
        $value = trim((string) $input[$key]);
        if ($value === '' || $value === '0') $site[$key] = 0;
        elseif (ctype_digit($value) && get_post_type((int) $value) === 'attachment' && wp_attachment_is_image((int) $value)) $site[$key] = (int) $value;
        elseif ($value === (string) ($saved[$key] ?? '') && preg_match('~^https?://~i', $value)) $site[$key] = esc_url_raw($value);
        else add_settings_error('aurivita_site', $key, __('Logo veya simge için medya kütüphanesinden bir görsel seçin.', 'aurivita'));
    }
    foreach (av_site_page_fields() as $key => $label) {
        if (!array_key_exists($key, $input) || !is_scalar($input[$key])) continue;
        $id = absint($input[$key]);
        if ($id === 0 || get_post_type($id) === 'page') $site[$key] = $id;
        else add_settings_error('aurivita_site', $key, $label . ': ' . __('Geçerli bir sayfa seçin.', 'aurivita'));
    }
    if (isset($input['contact_email']) && is_scalar($input['contact_email'])) {
        $email = trim((string) $input['contact_email']);
        if ($email === '' || is_email($email)) $site['contact_email'] = sanitize_email($email);
        else add_settings_error('aurivita_site', 'contact_email', __('Geçerli bir iletişim e-postası girin.', 'aurivita'));
    }
    if (isset($input['footer_disclosures']) && is_array($input['footer_disclosures'])) {
        $site['footer_disclosures'] = [];
        for ($i = 0; $i < 3; $i++) $site['footer_disclosures'][] = isset($input['footer_disclosures'][$i]) && is_scalar($input['footer_disclosures'][$i]) ? sanitize_textarea_field((string) $input['footer_disclosures'][$i]) : '';
    }
    // The imported reference identity survives ordinary brand and featured-product edits.
    $site['profile'] = ($saved['profile'] ?? '') === 'aurivita' ? 'aurivita' : 'generic';
    $site['reference_product_id'] = absint($saved['reference_product_id'] ?? 0);
    return $site;
}

function av_sanitize_main_product($input): int {
    $id = is_scalar($input) ? absint($input) : 0;
    if (!$id || get_post_type($id) === 'product') return $id;
    add_settings_error('aurivita_site', 'main_product', __('Geçerli bir WooCommerce ürünü seçin.', 'aurivita'));
    return (int) get_option('aurivita_main_product', 0);
}

function av_sanitize_site_colors($input): array {
    $saved = function_exists('av_global') ? av_global() : get_option('aurivita_global', []);
    if (!is_array($saved)) $saved = [];
    if (!is_array($input)) return $saved;
    foreach (av_site_color_fields() as $key => $label) {
        if (!isset($input[$key]) || !is_scalar($input[$key])) continue;
        $color = sanitize_hex_color((string) $input[$key]);
        if ($color && strlen($color) === 7) $saved[$key] = $color;
        else add_settings_error('aurivita_site', $key, $label . ': ' . __('Altı haneli bir renk seçin.', 'aurivita'));
    }
    return $saved;
}

function av_site_settings_text_row(string $key, string $label, array $site, bool $multiline = false): void {
    $value = is_scalar($site[$key] ?? null) ? (string) $site[$key] : '';
    echo '<tr><th scope="row"><label for="av-site-' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td>';
    if ($multiline) echo '<textarea id="av-site-' . esc_attr($key) . '" name="aurivita_site[' . esc_attr($key) . ']" rows="3" class="large-text">' . esc_textarea($value) . '</textarea>';
    else echo '<input id="av-site-' . esc_attr($key) . '" name="aurivita_site[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" type="text" class="regular-text">';
    echo '</td></tr>';
}

function av_site_settings_media_row(string $key, string $label, array $site): void {
    $value = is_scalar($site[$key] ?? null) ? (string) $site[$key] : '0';
    $preview = is_numeric($value) ? wp_get_attachment_image_url((int) $value, 'medium') : $value;
    echo '<tr><th scope="row">' . esc_html($label) . '</th><td class="av-site-media">';
    echo '<input type="hidden" name="aurivita_site[' . esc_attr($key) . ']" value="' . esc_attr($value) . '">';
    echo '<img alt="" style="display:' . ($preview ? 'block' : 'none') . ';max-width:180px;max-height:80px;margin-bottom:10px" src="' . esc_url($preview ?: '') . '">';
    echo '<button type="button" class="button av-site-media-select" aria-label="' . esc_attr($label . ' · ' . __('Görsel seç', 'aurivita')) . '">' . esc_html__('Görsel seç', 'aurivita') . '</button> ';
    echo '<button type="button" class="button av-site-media-clear">' . esc_html__('Kaldır', 'aurivita') . '</button></td></tr>';
}

function av_site_settings_page(): void {
    if (!current_user_can('manage_options')) wp_die(esc_html__('Bu sayfa için yetkiniz yok.', 'aurivita'));
    if (!function_exists('av_site')) { echo '<div class="wrap"><h1>' . esc_html__('Marka ve Site', 'aurivita') . '</h1></div>'; return; }
    $site = av_site();
    wp_enqueue_media();
    $fields = av_site_text_fields();
    echo '<div class="wrap"><h1>' . esc_html__('Marka ve Site', 'aurivita') . '</h1>';
    settings_errors();
    echo '<p>' . esc_html__('Ürün fiyatı, stok ve seçenekleri WooCommerce ürün editöründen; sayfa içerikleri blok editöründen; bağlantılar WordPress menülerinden düzenlenir.', 'aurivita') . '</p>';
    echo '<p><a href="' . esc_url(admin_url('nav-menus.php')) . '">' . esc_html__('Menüleri düzenle', 'aurivita') . '</a> · <a href="' . esc_url(admin_url('edit.php?post_type=product')) . '">' . esc_html__('Ürünleri düzenle', 'aurivita') . '</a> · <a href="' . esc_url(admin_url('edit.php?post_type=page')) . '">' . esc_html__('Sayfaları düzenle', 'aurivita') . '</a></p>';
    echo '<form method="post" action="' . esc_url(admin_url('options.php')) . '">';
    settings_fields('aurivita_site_settings');
    echo '<h2>' . esc_html__('Marka', 'aurivita') . '</h2><table class="form-table" role="presentation">';
    foreach (['brand_name', 'tagline'] as $key) av_site_settings_text_row($key, $fields[$key], $site);
    foreach (['header_logo' => __('Header logosu', 'aurivita'), 'footer_logo' => __('Footer logosu', 'aurivita'), 'favicon' => __('Site simgesi', 'aurivita')] as $key => $label) av_site_settings_media_row($key, $label, $site);
    av_site_settings_text_row('announcement', __('Duyuru (isteğe bağlı)', 'aurivita'), $site, true);
    av_site_settings_text_row('home_description', $fields['home_description'], $site, true);
    echo '<tr><th scope="row"><label for="av-main-product">' . esc_html__('Ana ürün', 'aurivita') . '</label></th><td><select id="av-main-product" name="aurivita_main_product"><option value="0">' . esc_html__('Ürün seçilmedi', 'aurivita') . '</option>';
    foreach (get_posts(['post_type' => 'product', 'post_status' => ['publish', 'draft', 'pending', 'private'], 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']) as $product) {
        echo '<option value="' . (int) $product->ID . '" ' . selected((int) get_option('aurivita_main_product', 0), $product->ID, false) . '>' . esc_html($product->post_title . ' (#' . $product->ID . ')') . '</option>';
    }
    echo '</select><p class="description">' . esc_html__('Ana ürün seçimi kaynak ürün bağını değiştirmez.', 'aurivita') . '</p></td></tr>';
    $reference_id = absint($site['reference_product_id']);
    echo '<tr><th scope="row">' . esc_html__('Ürün düzeni', 'aurivita') . '</th><td>' . esc_html($site['profile'] === 'aurivita' ? __('Aurivita kaynak düzeni', 'aurivita') : __('Genel ürün düzeni', 'aurivita'));
    if ($reference_id) echo ' · ' . esc_html(get_the_title($reference_id) . ' (#' . $reference_id . ')');
    echo '<p class="description">' . esc_html__('Kurulumda belirlenen kaynak ürün bağı salt okunurdur.', 'aurivita') . '</p></td></tr></table>';
    echo '<h2>' . esc_html__('Renkler', 'aurivita') . '</h2><table class="form-table" role="presentation"><tr><th scope="row">' . esc_html__('Site renkleri', 'aurivita') . '</th><td style="display:flex;flex-wrap:wrap;gap:16px">';
    $colors = function_exists('av_global') ? av_global() : [];
    foreach (av_site_color_fields() as $key => $label) {
        $color = is_array($colors) && is_string($colors[$key] ?? null) ? sanitize_hex_color($colors[$key]) : '';
        echo '<label style="display:grid;gap:6px">' . esc_html($label) . '<input type="color" name="aurivita_global[' . esc_attr($key) . ']" value="' . esc_attr($color ?: '#333333') . '"></label>';
    }
    echo '</td></tr></table><h2>' . esc_html__('Menü ve footer', 'aurivita') . '</h2><table class="form-table" role="presentation">';
    foreach (['menu_title', 'menu_note', 'footer_about', 'footer_support_title', 'footer_links_title', 'footer_policies_title', 'footer_copyright', 'footer_note'] as $key) av_site_settings_text_row($key, $fields[$key], $site, in_array($key, ['menu_note', 'footer_about', 'footer_note'], true));
    echo '<tr><th scope="row">' . esc_html__('Footer açıklamaları (isteğe bağlı)', 'aurivita') . '</th><td>';
    foreach ($site['footer_disclosures'] as $i => $text) echo '<p><label for="av-disclosure-' . (int) $i . '">' . esc_html(sprintf(__('Açıklama %d', 'aurivita'), $i + 1)) . '</label><textarea id="av-disclosure-' . (int) $i . '" name="aurivita_site[footer_disclosures][' . (int) $i . ']" rows="2" class="large-text">' . esc_textarea($text) . '</textarea></p>';
    echo '</td></tr></table><h2>' . esc_html__('İletişim ve sayfalar', 'aurivita') . '</h2><table class="form-table" role="presentation"><tr><th scope="row"><label for="av-contact-email">' . esc_html__('İletişim e-postası', 'aurivita') . '</label></th><td><input type="email" id="av-contact-email" class="regular-text" name="aurivita_site[contact_email]" value="' . esc_attr($site['contact_email']) . '"></td></tr>';
    foreach (av_site_page_fields() as $key => $label) {
        echo '<tr><th scope="row"><label for="av-site-' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td>';
        wp_dropdown_pages(['name' => 'aurivita_site[' . $key . ']', 'id' => 'av-site-' . $key, 'selected' => absint($site[$key]), 'show_option_none' => __('Sayfa seçilmedi', 'aurivita'), 'option_none_value' => '0', 'post_status' => ['publish', 'draft', 'private'], 'sort_column' => 'post_title']);
        if ($site[$key]) echo ' <a href="' . esc_url(get_edit_post_link(absint($site[$key]))) . '">' . esc_html__('Düzenle', 'aurivita') . '</a>';
        echo '</td></tr>';
    }
    echo '</table><details><summary>' . esc_html__('Ödeme metinleri', 'aurivita') . '</summary><table class="form-table" role="presentation">';
    foreach (['checkout_title', 'checkout_description', 'checkout_return_text'] as $key) av_site_settings_text_row($key, $fields[$key], $site, $key === 'checkout_description');
    echo '</table></details>';
    submit_button();
    echo '</form></div>';
    ?>
    <script>
    document.querySelectorAll('.av-site-media').forEach(function (row) {
        var input = row.querySelector('input'), preview = row.querySelector('img'), frame;
        row.querySelector('.av-site-media-select').addEventListener('click', function () {
            if (!frame) {
                frame = wp.media({title: <?php echo wp_json_encode(__('Görsel seç', 'aurivita')); ?>, button: {text: <?php echo wp_json_encode(__('Görseli kullan', 'aurivita')); ?>}, library: {type: 'image'}, multiple: false});
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    input.value = attachment.id;
                    preview.src = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                    preview.style.display = 'block';
                });
            }
            frame.open();
        });
        row.querySelector('.av-site-media-clear').addEventListener('click', function () {
            input.value = '0'; preview.removeAttribute('src'); preview.style.display = 'none';
        });
    });
    </script>
    <?php
}
