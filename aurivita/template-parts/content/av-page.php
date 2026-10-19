<?php defined('ABSPATH') || exit; $section=$args['section'] ?? []; $id=absint(av_section_value($section,'page')); $record=get_post($id); ?>
<article class="av-page"><h1><?php echo esc_html($record?->post_title ?? av_section_value($section,'heading','')); ?></h1><?php if($record)echo av_rich($record->post_content); else echo av_rich(av_section_value($section,'body','')); ?></article>
