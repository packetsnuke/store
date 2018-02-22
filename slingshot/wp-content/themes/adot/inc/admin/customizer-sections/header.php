<?php
$number[0] = 'Select';
for ($i = 10; $i < 51; $i++) {
    $number[$i . 'px'] = $i . 'px';
}
$font_sizes = $number;
/*
 * Creating a Header Options
 */
$header = $titan->createThimCustomizerSection(array(
    'name' => __('Header', 'thim'),
    'position' => 2,
    'id' => 'display-header',
));
