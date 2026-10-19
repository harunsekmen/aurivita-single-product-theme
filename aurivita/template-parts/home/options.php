<?php
defined('ABSPATH') || exit;
$section = $args['section'] ?? [];
$section_id = sanitize_html_class((string) ($args['section_id'] ?? 'options'));
$product = $args['product'] ?? av_main_product();
if (!$product instanceof WC_Product) return;
require_once get_template_directory() . '/template-parts/product/helpers.php';
$all = $product->is_type('variable') ? av_variations($product) : [['id'=>$product->get_id(), 'attributes'=>[], 'price'=>wc_get_price_to_display($product), 'regular_price'=>wc_get_price_to_display($product,['price'=>$product->get_regular_price()]), 'available'=>$product->is_purchasable() && $product->is_in_stock(), 'image_id'=>$product->get_image_id()]];
$limit = max(1, min(12, (int) av_section_value($section, 'option_count', 6)));
$options = array_slice($all, 0, $limit);
?>
<section class="av-home av-home-packs av-home-packs--generic<?php echo count($options)===1 ? ' av-home-packs--single' : ''; ?>" id="AvHomePackages" aria-labelledby="AvHomePacks-<?php echo esc_attr($section_id); ?>">
 <div class="av-home__container">
  <div class="av-home__heading">
   <p class="av-home__eyebrow"><?php echo esc_html(av_section_value($section,'eyebrow','OPTIONS')); ?></p>
   <h2 id="AvHomePacks-<?php echo esc_attr($section_id); ?>" class="av-home__title"><?php echo esc_html(av_section_value($section,'heading','Choose the option that suits you.')); ?></h2>
   <div class="av-home__copy"><?php echo av_rich((string) av_section_value($section,'copy','')); ?></div>
  </div>
  <div class="av-home-packs__group">
   <?php foreach ($options as $option):
      $label = avp_variation_label($option,$product);
      $link = add_query_arg($option['attributes'],$product->get_permalink());
      $price = (float) $option['price']; $regular = (float) $option['regular_price']; ?>
    <article class="av-home-packs__card">
     <?php if ($option['image_id']): echo wp_get_attachment_image((int)$option['image_id'],'medium_large',false,['loading'=>'lazy','sizes'=>'(min-width: 750px) 28vw, 80vw','alt'=>$label]); endif; ?>
     <h3><?php echo esc_html($product->get_name()); ?></h3>
     <?php if ($product->is_type('variable')): ?><p class="av-home-packs__description"><?php echo esc_html($label); ?></p><?php endif; ?>
     <div class="av-home-packs__prices"><strong><?php echo av_money($price); ?></strong><?php if ($regular > $price): ?><s><?php echo av_money($regular); ?></s><?php endif; ?></div>
     <?php if ($option['available']): ?><a class="av-home__button" href="<?php echo esc_url($link); ?>"><?php echo esc_html(av_section_value($section,'button','View product')); ?></a><?php else: ?><span class="av-home-packs__unavailable">Sold out</span><?php endif; ?>
    </article>
   <?php endforeach; ?>
  </div>
  <?php if (count($all)>count($options)): ?><div class="av-home__actions"><a class="av-home__link" href="<?php echo esc_url($product->get_permalink()); ?>">View all options</a></div><?php endif; ?>
  <?php if (av_section_value($section,'note','') !== ''): ?><p class="av-home-packs__note"><?php echo esc_html(av_section_value($section,'note')); ?></p><?php endif; ?>
 </div>
</section>
