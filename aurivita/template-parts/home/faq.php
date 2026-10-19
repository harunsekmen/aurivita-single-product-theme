<?php
defined('ABSPATH') || exit;

$section = $args['section'] ?? [];
$section_id = sanitize_html_class((string) ($args['section_id'] ?? 'home_faq'));
$resource = av_main_product();
$selection = av_section_value($section, 'product');
if ($selection instanceof WC_Product) {
    $resource = $selection;
} elseif ($selection !== '') {
    $record = is_numeric($selection) ? null : get_page_by_path(basename((string) wp_parse_url((string) $selection, PHP_URL_PATH)), OBJECT, 'product');
    $selected_product = wc_get_product(is_numeric($selection) ? (int) $selection : ($record->ID ?? 0));
    if ($selected_product) {
        $resource = $selected_product;
    }
}
$page = av_section_value($section, 'page', 'sss');
$faq_link = is_numeric($page) ? get_permalink((int) $page) : (string) $page;
if ($faq_link !== '' && !preg_match('~^(?:[a-z][a-z0-9+.-]*:|/|#)~i', $faq_link)) {
    $faq_link = '/pages/' . $faq_link;
}
$secondary = (string) av_section_value($section, 'secondary', 'All questions and answers');
$finish_heading = (string) av_section_value($section, 'finish_heading');
$copy = new WP_HTML_Tag_Processor(wp_kses_post(av_section_value($section, 'copy', '<p>Quick answers about bundles and shopping. Explore our guides for more detail.</p>')));
while ($copy->next_tag('A')) {
    $href = $copy->get_attribute('href');
    if (is_string($href)) {
        $copy->set_attribute('href', av_url($href));
    }
}
?>
<section class="av-home av-home-faq" aria-labelledby="AvHomeFaq-<?php echo esc_attr($section_id); ?>">
  <div class="av-home__container">
    <div class="av-home-faq__grid">
      <div>
        <p class="av-home__eyebrow"><?php echo esc_html(av_section_value($section, 'eyebrow', 'FREQUENTLY ASKED QUESTIONS')); ?></p>
        <h2 id="AvHomeFaq-<?php echo esc_attr($section_id); ?>" class="av-home__title"><?php echo esc_html(av_section_value($section, 'heading', 'Your questions, answered.')); ?></h2>
        <div class="av-home__copy"><?php echo $copy->get_updated_html(); ?></div>
        <?php if ($faq_link !== '' && $secondary !== '') : ?><div class="av-home__actions"><a class="av-home__link" href="<?php echo esc_url(av_url($faq_link)); ?>"><?php echo esc_html($secondary); ?><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M4 12h15m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a></div><?php endif; ?>
      </div>
      <div class="av-home-faq__list">
        <?php foreach (av_section_blocks($section) as $block) :
            $settings = $block['settings'] ?? [];
            $answer = new WP_HTML_Tag_Processor(wp_kses_post($settings['answer'] ?? ''));
            while ($answer->next_tag('A')) {
                $href = $answer->get_attribute('href');
                if (is_string($href)) {
                    $answer->set_attribute('href', av_url($href));
                }
            }
        ?>
          <details><summary><?php echo esc_html($settings['question'] ?? ''); ?></summary><div class="av-home-faq__answer"><?php echo $answer->get_updated_html(); ?></div></details>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if ($finish_heading !== '' && $resource) : ?>
      <div class="av-home-faq__finish">
        <h2 class="av-home__title"><?php echo nl2br(esc_html($finish_heading)); ?></h2>
        <a class="av-home__button" href="<?php echo esc_url(av_url($resource->get_permalink())); ?>"><?php echo esc_html(av_section_value($section, 'button', 'View product')); ?><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M4 12h15m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
      </div>
    <?php endif; ?>
  </div>
</section>
