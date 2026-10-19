<?php
defined('ABSPATH') || exit;

$section = $args['section'] ?? [];
$section_id = sanitize_html_class((string) ($args['section_id'] ?? 'home_story'));
$copy = new WP_HTML_Tag_Processor(wp_kses_post(av_section_value($section, 'copy', '<p>Explore our brand and shopping terms.</p>')));
while ($copy->next_tag('A')) {
    $href = $copy->get_attribute('href');
    if (is_string($href)) {
        $copy->set_attribute('href', av_url($href));
    }
}
?>
<section class="av-home av-home-story" aria-labelledby="AvHomeStory-<?php echo esc_attr($section_id); ?>">
  <div class="av-home__container">
    <div class="av-home-story__top">
      <div><p class="av-home__eyebrow"><?php echo esc_html(av_section_value($section, 'eyebrow', 'MARKAMIZ')); ?></p><h2 id="AvHomeStory-<?php echo esc_attr($section_id); ?>" class="av-home__title"><?php echo nl2br(esc_html(av_section_value($section, 'heading', "A simpler\ndaily routine."))); ?></h2></div>
      <div class="av-home__copy"><?php echo $copy->get_updated_html(); ?></div>
    </div>
    <div class="av-home-story__cards">
      <?php foreach (av_section_blocks($section) as $index => $block) :
          $settings = $block['settings'] ?? [];
          $page = $settings['page'] ?? '';
          $page_link = is_numeric($page) ? get_permalink((int) $page) : (string) $page;
          if ($page_link !== '' && !preg_match('~^(?:[a-z][a-z0-9+.-]*:|/|#)~i', $page_link)) {
              $page_link = '/pages/' . $page_link;
          }
          $page_link = $page_link ?: (string) ($settings['link'] ?? '');
      ?>
        <div class="av-home-story__card">
          <span class="av-home-story__number" aria-hidden="true"><?php echo esc_html('0' . ($index + 1)); ?></span>
          <h3><?php echo esc_html($settings['title'] ?? ''); ?></h3>
          <p><?php echo esc_html($settings['text'] ?? ''); ?></p>
          <?php if ($page_link !== '') : ?><a class="av-home__link" href="<?php echo esc_url(av_url($page_link)); ?>"><?php echo esc_html($settings['button'] ?? 'Daha fazla bilgi'); ?><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M4 12h15m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
