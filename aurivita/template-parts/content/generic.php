<?php
defined('ABSPATH') || exit;
$section = $args['section'] ?? [];
$type = $section['type'] ?? '';
$id = sanitize_html_class($args['section_id'] ?? wp_unique_id('section-'));
$heading = (string) av_section_value($section,'heading','');
$copy = (string) av_section_value($section,'copy','');
$image = av_section_value($section,'image',0);
$cards = av_section_blocks($section);
if ($type === 'av-sticky') {
    $product = av_main_product();
    if (!$product) return;
    echo '<aside class="av-generic-sticky"><span>'.esc_html($product->get_name()).'</span><span>'.wp_kses_post($product->get_price_html()).'</span><a class="local-primary" href="#product">'.esc_html(av_section_value($section,'button','View product')).'</a></aside>';
    return;
}
if (!$heading && !$copy && !$image && !$cards) return;
?>
<section class="av-home av-generic-section" id="<?php echo esc_attr($id); ?>" <?php if ($heading): ?>aria-labelledby="<?php echo esc_attr($id.'-title'); ?>"<?php endif; ?>>
 <div class="av-home__container">
  <div class="av-home__heading">
   <?php if (av_section_value($section,'eyebrow','')): ?><p class="av-home__eyebrow"><?php echo esc_html(av_section_value($section,'eyebrow')); ?></p><?php endif; ?>
   <?php if ($heading): ?><h2 class="av-home__title" id="<?php echo esc_attr($id.'-title'); ?>"><?php echo esc_html($heading); ?></h2><?php endif; ?>
   <?php if ($copy): ?><div class="av-home__copy"><?php echo av_rich($copy); ?></div><?php endif; ?>
  </div>
  <?php if (is_numeric($image) && (int)$image > 0): echo wp_get_attachment_image((int)$image,'large',false,['loading'=>'lazy','class'=>'av-generic-media','sizes'=>'(min-width: 750px) 50vw, 90vw']); elseif (is_string($image) && $image): ?><img class="av-generic-media" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($heading); ?>" loading="lazy" width="1200" height="900"><?php endif; ?>
  <div class="<?php echo $type === 'av-faq' ? 'av-home-faq__list' : 'av-generic-cards'; ?>">
   <?php foreach ($cards as $card): $values = $card['settings'] ?? []; $title = $values['title'] ?? ''; $text = $values['text'] ?? ''; $media = (int)($values['image'] ?? 0);
    if (!$title && !$text && !$media) continue;
    if ($type === 'av-faq'): ?><details><summary><?php echo esc_html($title); ?></summary><div class="av-home-faq__answer"><?php echo av_rich($text); ?></div></details>
    <?php else: ?><article class="av-generic-card">
     <?php if ($media): echo wp_get_attachment_image($media,'medium_large',false,['loading'=>'lazy','sizes'=>'(min-width: 750px) 28vw, 90vw']); endif; ?>
     <?php if ($title): ?><h3><?php echo esc_html($title); ?></h3><?php endif; ?>
     <?php if ($text): ?><div><?php echo av_rich($text); ?></div><?php endif; ?>
     <?php if (!empty($values['url']) && !empty($values['button'])): ?><a class="av-home__link" href="<?php echo esc_url(av_url($values['url'])); ?>"><?php echo esc_html($values['button']); ?></a><?php endif; ?>
    </article><?php endif; ?>
   <?php endforeach; ?>
  </div>
 </div>
</section>
