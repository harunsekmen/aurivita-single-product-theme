<?php
/**
 * Plugin Name: Aurivita Core
 * Description: Aurivita için Gutenberg bölümleri, tema ayarları, sepet hediyeleri ve kurumsal fatura alanları.
 * Version: 1.2.2
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 */
defined('ABSPATH') || exit;
function av_core_schemas(): array {
    static $schemas;
    if (isset($schemas)) return $schemas;
    $profile = get_option('aurivita_site', [])['profile'] ?? '';
    $file = get_template_directory().'/data/'.($profile === 'aurivita' ? 'schemas' : 'generic-schemas').'.json';
    return $schemas = is_file($file) ? (json_decode(file_get_contents($file),true) ?: []) : [];
}
add_action('init', function() {
    foreach(av_core_schemas() as $type=>$schema) {
        if(in_array($type,['av-header','av-footer'],true)) continue;
        register_block_type('aurivita/'.$type, ['api_version'=>3,'title'=>$schema['title'] ?? $type,'category'=>'aurivita','attributes'=>['section'=>['type'=>'object','default'=>['type'=>$type,'settings'=>[]]],'sectionId'=>['type'=>'string','default'=>'']],'supports'=>['html'=>false,'anchor'=>true,'align'=>false],'render_callback'=>function($attrs) use($type) { if(!function_exists('av_render_section'))return ''; $section=$attrs['section'] ?? []; $section['type']=$type;return av_render_section($section,$attrs['sectionId'] ?? ''); }]);
    }
});
add_filter('block_categories_all',function($categories){array_unshift($categories,['slug'=>'aurivita','title'=>'Aurivita']);return $categories;});
add_filter('use_block_editor_for_post_type',function($use,$type){return $type==='product' ? true : $use;},1000,2);
add_action('init',function(){add_post_type_support('product','editor');},20);
add_action('enqueue_block_editor_assets',function(){
    wp_enqueue_script('aurivita-editor',plugins_url('assets/editor.js',__FILE__),['wp-blocks','wp-element','wp-components','wp-block-editor','wp-server-side-render','wp-i18n'],(string) filemtime(__DIR__.'/assets/editor.js'),true);
    $resources = ['product'=>[], 'page'=>[], 'blog'=>[]];
    foreach (['product'=>'product','page'=>'page'] as $key=>$type) {
        foreach (get_posts(['post_type'=>$type,'post_status'=>['publish','draft'],'numberposts'=>-1,'orderby'=>'title','order'=>'ASC']) as $post) $resources[$key][] = ['label'=>$post->post_title,'value'=>(string)$post->ID];
    }
    foreach (get_categories(['hide_empty'=>false]) as $term) $resources['blog'][] = ['label'=>$term->name,'value'=>(string)$term->term_id];
    $editor_schemas = av_core_schemas();
    $editing = get_post(absint($_GET['post'] ?? 0));
    if (!$editing || !str_contains($editing->post_content, '"presentation":"aurivita"')) {
        $generic_file = get_template_directory().'/data/generic-schemas.json';
        if (is_file($generic_file)) $editor_schemas = json_decode(file_get_contents($generic_file),true) ?: [];
    }
    wp_localize_script('aurivita-editor','AurivitaEditor',['schemas'=>$editor_schemas,'resources'=>$resources]);
});
require_once __DIR__.'/inc/site-settings.php';
av_register_site_settings();
require_once __DIR__.'/inc/cart-settings.php';
require_once __DIR__.'/inc/cart-rewards.php';
require_once __DIR__.'/inc/corporate-invoice.php';
require_once __DIR__.'/inc/turkey-checkout.php';
av_register_cart_settings();
av_register_cart_rewards();
