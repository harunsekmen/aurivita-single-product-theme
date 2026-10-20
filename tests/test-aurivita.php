<?php
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/aurivita/functions.php';

echo "Testing Aurivita Single Product Theme...
";
$styles = av_style_files();
assert(is_array($styles) && count($styles) >= 15, "Aurivita style files list check");
assert(in_array('fw-styles-av-product.css', $styles), "Must include product styles");
assert(in_array('fw-styles-av-sticky.css', $styles), "Must include sticky ATC styles");

echo "PASS: aurivita-single-product-theme verified.
";
