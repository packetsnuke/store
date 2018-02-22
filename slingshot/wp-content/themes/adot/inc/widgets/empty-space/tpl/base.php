<?php
$css        = '';
$css        = $instance['height'] ? 'height:' . $instance['height'] . 'px;' : 'height:20px';
$else_class = $instance['else_class'] ? $instance['else_class'] : '';
$css = $css ? ' style="' . $css . '"' : '';
echo '<div class="empty-space ' . $else_class . '" ' . $css . ' ></div>';