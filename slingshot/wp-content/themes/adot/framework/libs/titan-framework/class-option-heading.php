<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TitanFrameworkOptionHeading extends TitanFrameworkOption {

	/*
	 * Display for options and meta
	 */
	public function display() {
		$class = isset($this->settings['row_class'])?$this->settings['row_class']:'';
		?>
		<tr valign="top" class="even first tf-heading <?php echo esc_attr($class); ?>">
		<th scope="row" class="first last" colspan="2">
		<h3><?php echo esc_html($this->settings['name']) ?></h3>
		</th>
		</tr>
		<?php
	}
}
