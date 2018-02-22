<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class TitanFrameworkOptionImport extends TitanFrameworkOption {

	public $defaultSecondarySettings = array(
		'import' => '',
		'desc'   => "Warning: You must import the sample data file before customizing your theme.
			If you customize your theme, and later import a sample data file, all current contents entered in your site will be overwritten to the default settings of the file you are uploading! Please proceed with the utmost care, after exporting all current data!
			Note: If you get errors, please be sure that your server configured Memory Limit >=64MB and Execution Time >=60."
	);

	public function display() {
		#TODO: show list of demo to import
		/**
		 * include file list of demo data
		 */
		$demo_data_file_path = TP_THEME_DIR . 'inc' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'demo-data.php';
		$demo_data_dir_path  = TP_THEME_DIR . 'inc' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'data';
		if ( is_file( $demo_data_file_path ) ) {
			require $demo_data_file_path;
		} else {
			// create demo data
			$demo_datas = array();
		}
		$demo_data_file = TP_THEME_DIR . 'inc' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'demo-data.php';
		if ( is_file( $demo_data_file ) ) {
			require TP_THEME_DIR . 'inc' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'demo-data.php';
		}
//		echo json_encode($demo_datas);
		wp_enqueue_script( 'tp-import', TP_FRAMEWORK_LIBS_URI . '/titan-framework/js/tp-import.js', array(), false, true );
		if ( !empty( $this->owner->postID ) ) {
			return;
		}
		if ( empty( $this->settings['import'] ) ) {
			$this->settings['import'] = __( 'Import', 'thim' );
		}

		$memory_limit       = ini_get( 'memory_limit' );
		$max_execution_time = ini_get( 'max_execution_time' );
		?>
		<table class="wc_status_table widefat" id="status" cellspacing="0">
			<thead>
			<tr>
				<th colspan="3" data-export-label="Server Environment">Server Environment</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>PHP Memory Limit:</td>
				<td><?php
					if ( intval( $memory_limit ) < 64 ) {
						echo '<span style="color:red;font-weight: bold;">' . $memory_limit . '</span>';
					} else {
						echo '<span style="color:green;font-weight: bold;">' . $memory_limit . '</span>';
					}
					?></td>
				<td><?php
					if ( intval( $memory_limit ) < 64 ) {
						_e( 'Memory limit < 64M can be make importing errors', 'thim' );
					}
					?></td>
			</tr>
			<tr>
				<td>PHP Execution Time:</td>
				<td>
					<?php
					if ( intval( $max_execution_time ) < 64 ) {
						echo '<span style="color:red;font-weight: bold;">' . $max_execution_time . '</span>';
					} else {
						echo '<span style="color:green;font-weight: bold;">' . $max_execution_time . '</span>';
					}
					?>
				</td>
				<td>
					<?php
					if ( intval( $max_execution_time ) < 60 ) {
						_e( 'Execution Time < 60 can be make importing errors', 'thim' );
					}
					?>
				</td>
			</tr>
			</tbody>
		</table>
		<p class='submit'>
			<?php
			if ( is_file( $demo_data_file ) ) {
				?>
				<img width="300" style="display: none;" id="demodata-thumbnail" src="http://placehold.it/300x200" />
				<br />
				<select id="demodata-selecter">
					<option value=""><?php _e( 'Select demo data', 'thim' ); ?></option>
					<?php
					foreach ( $demo_datas as $key => $item ) {
						echo '<option value="' . $key . '" data-thumbnail-url="' . $item['thumbnail_url'] . '" >' . $item['title'] . '</option>';
					}
					?>
				</select>
				<?php
			}
			if ( !is_file( $demo_data_file ) && !is_dir( $demo_data_dir_path ) ) {
				?>
				<?php echo esc_html( 'Demo data not available!' ); ?>
				<?php
			} else {
				?>
				<span class="button button-primary tp-import-action">
                <?php echo ent2ncr( $this->settings['import'] ); ?>
            </span>
				<br />
				<br />
				<?php echo esc_html( $this->settings['desc'] ); ?>
				<?php
			}
			?>
		</p>
		<div class="tp_process_bar" style="display:none;">
			<div class="tpimport_download" style="display: none;">
				<span class="text_note tp_process_title">Download demo data package...</span>

				<div class="meter">
					<span style="width: 0px"></span>
				</div>
				<div class="tp_process_messase"></div>
			</div>
			<div class="tpimport_extract" style="display: none;">
				<span class="text_note">Extract demo data package...</span>

				<div class="meter">
					<span style="width: 0px"></span>
				</div>
				<div class="tp_process_messase"></div>
			</div>
			<div class="tpimport_core" style="display: none;">
				<span class="text_note">Import Pages, Ports, Categories..</span>

				<div class="meter">
					<span style="width: 0px"></span>
				</div>
				<div class="tp_process_messase"></div>
			</div>
			<div class="tpimport_widgets" style="display: none;">
				<span class="text_note">Add widgets...</span>

				<div class="meter">
					<span style="width: 0px"></span>
				</div>
				<div class="tp_process_messase"></div>
			</div>
			<div class="tpimport_setting" style="display: none;">
				<span class="text_note">Reset theme options...</span>

				<div class="meter">
					<span style="width: 0px"></span>
				</div>
				<div class="tp_process_messase"></div>
			</div>
			<div class="tpimport_menus" style="display: none;">
				<span class="text_note">Setup menus...</span>

				<div class="meter">
					<span style="width: 0px"></span>
				</div>
				<div class="tp_process_messase"></div>
			</div>
			<?php if ( class_exists( 'RevSlider' ) ) { ?>
				<div class="tpimport_slider" style="display: none;">
					<span class="text_note">Setup slider...</span>

					<div class="meter">
						<span style="width: 0px"></span>
					</div>
					<span class="text_note">If import slider don`t finish you can import manual, please view at <a href="<?php echo esc_url( 'http://thimpress.com/knowledge-base/import-revolution-sliders/' ); ?>">here</a></span>

					<div class="tp_process_messase"></div>
				</div>
			<?php } ?>
			<div id="tp_process_error_messase"></div>
		</div>

		<table class='form-table'>
			<tbody>
		<?php
	}

}
        
