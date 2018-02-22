<?php
/**
 * Welcome Page Class
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * CP_Admin_Welcome class
 */
class CP_Admin_Welcome {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'cp_welcome' ) );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {

		if ( empty( $_GET['page'] ) ) {
			return;
		}

		$welcome_page_name  = __( 'About Chained Products', SA_WC_Chained_Products::$text_domain );
		$welcome_page_title = __( 'Welcome to Chained Products', SA_WC_Chained_Products::$text_domain );

		switch ( $_GET['page'] ) {
			case 'cp-about' :
				add_submenu_page( 'edit.php?post_type=product', $welcome_page_title, $welcome_page_name, 'manage_options', 'cp-about', array( $this, 'about_screen' ) );
				break;
			case 'cp-shortcode' :
				add_submenu_page( 'edit.php?post_type=product', $welcome_page_title, $welcome_page_name, 'manage_options', 'cp-shortcode', array( $this, 'shortcode_screen' ) );
				break;
			case 'cp-faqs' :
			 	add_submenu_page( 'edit.php?post_type=product', $welcome_page_title, $welcome_page_name, 'manage_options', 'cp-faqs', array( $this, 'faqs_screen' ) );
				break;
		}
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 */
	public function admin_head() {
		remove_submenu_page( 'edit.php?post_type=product', 'cp-about' );
		remove_submenu_page( 'edit.php?post_type=product', 'cp-shortcode' );
		remove_submenu_page( 'edit.php?post_type=product', 'cp-faqs' );

		?>
		<style type="text/css">
			/*<![CDATA[*/
			.about-wrap h3 {
				margin-top: 1em;
				margin-right: 0em;
				margin-bottom: 0.1em;
				font-size: 1.25em;
				line-height: 1.3em;
			}
			.about-wrap .button-primary {
				margin-top: 18px;
			}
			.about-wrap .button-hero {
				color: #FFF!important;
				border-color: #03a025!important;
				background: #03a025 !important;
				box-shadow: 0 1px 0 #03a025;
				font-size: 1em;
				font-weight: bold;
			}
			.about-wrap .button-hero:hover {
				color: #FFF!important;
				background: #0AAB2E!important;
				border-color: #0AAB2E!important;
			}
			.about-wrap p {
				margin-top: 0.6em;
				margin-bottom: 0.8em;
				line-height: 1.6em;
				font-size: 14px;
			}
			.about-wrap .feature-section {
				padding-bottom: 5px;
			}
			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Intro text/links shown on all about pages.
	 */
	private function intro() {

		if ( is_callable( 'SA_WC_Chained_Products::get_chained_products_plugin_data' ) ) {
			$plugin_data = SA_WC_Chained_Products::get_chained_products_plugin_data();
			$version = $plugin_data['Version'];
		} else {
			$version = '';
		}

		?>
		<h1><?php printf( __( 'Welcome to Chained Products %s', SA_WC_Chained_Products::$text_domain ), $version ); ?></h1>

		<h3><?php _e("Thanks for installing! We hope you enjoy using Chained Products.", SA_WC_Chained_Products::$text_domain); ?></h3>

		<div class="feature-section col two-col" style="margin-bottom:30px!important;">
			<div class="col">
				<p>
					<a href="<?php echo admin_url('post-new.php?post_type=product'); ?>" class="button button-hero"><?php _e( 'Create combo!', SA_WC_Chained_Products::$text_domain ); ?></a>
				</p>
			</div>

			<div class="col last-feature">
				<p align="right">
					<a href="<?php echo esc_url( apply_filters( 'chained_products_docs_url', 'http://docs.woothemes.com/document/chained-products/', SA_WC_Chained_Products::$text_domain ) ); ?>" class="docs button button-primary" target="_blank"><?php _e( 'Docs', SA_WC_Chained_Products::$text_domain ); ?></a>
				</p>
			</div>
		</div>

		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php if ( $_GET['page'] == 'cp-about' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'cp-about' ), 'admin.php' ) ) ); ?>">
				<?php _e( "Know Chained Products", SA_WC_Chained_Products::$text_domain ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'cp-shortcode' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'cp-shortcode' ), 'admin.php' ) ) ); ?>">
				<?php _e( "Shortcode", SA_WC_Chained_Products::$text_domain ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'cp-faqs' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'cp-faqs' ), 'admin.php' ) ) ); ?>">
				<?php _e( "FAQ's", SA_WC_Chained_Products::$text_domain ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Output the about screen.
	 */
	public function about_screen() {
		?>

		<script type="text/javascript">
			jQuery(document).on('ready', function(){
				jQuery('#menu-posts-product').find('a[href="edit.php?post_type=product"]').addClass('current');
				jQuery('#menu-posts-product').find('a[href="edit.php?post_type=product"]').parent().addClass('current');
			});
		</script>

		<div class="wrap about-wrap">

		<?php $this->intro(); ?>

			<div>
				<center><h3><?php echo __( 'Terminologies', SA_WC_Chained_Products::$text_domain ); ?></h3></center>
				<div class="feature-section col two-col" >
					<div class="col">
						<h4><?php echo __( 'Main Product / Chained Parent', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'This is the product to which other products will be attached. On adding this product to cart, all the attached products will be automatically added to cart or order with price zero.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
					<div class="col last-feature">
						<h4><?php echo __( 'Chained Item / Chained Child', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'Products which are attached to any other product are termed as Chained item. Chained item will always be added automatically to cart when its parent is added to cart or order.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
				</div>
				<center><h3><?php echo __( 'Chained Products', SA_WC_Chained_Products::$text_domain ); ?></h3></center>
				<div class="feature-section col three-col">
					<div class="col">
						<h4><?php echo __( 'What is Chained Products?', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'It\'s a WooCommerce add-on, which allows you to add any other WooCommerce product to an existing product in such a way that it creates a chain. In any order, when the main product is availble, all its chained item will also be present.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
					<div class="col">
						<h4><?php echo __( 'What\'s the final result?', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'Whenever a product, to which other products are chained, and it will be added to cart, all the product which are attached to main product will be added to cart.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
					<div class="col last-feature">
						<h4><?php echo __( 'What\'s new?', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'All those products which are attached to main product, when added to cart, their price will be removed, i.e. it will be added as price zero', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
				</div>
				<center><h3><?php echo __( 'What is possible', SA_WC_Chained_Products::$text_domain ); ?></h3></center>
				<div class="feature-section col three-col" >
					<div class="col">
						<h4><?php echo __( 'Create combos & packs', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'A combo product is a collection of multiple product. Generally combos are created to encourage customer to buy many products.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
						<p>
							<?php echo __( 'You can create a separate product & include all those product which you want in that combos, set a price for this combo. Now, if enabled, the plugin will also handle inventory for all products of combo.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
					<div class="col">
						<h4><?php echo __( 'Giveaway a product to all your existing customer', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'There can be cases that you want to giveaway a product for free to your existing customer & also new customer. Chained Products provides you a setting to add newly added chained items to all existing order which includes chained parent.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
					<div class="col last-feature">
						<h4><?php echo __( 'Buy 1 Get X', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'Since Chained Products allows you to set quantity for chained items, you can create combo such as: Buy 1 Get 1 Free, Buy 1 Get 2 Free & so on...', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
				</div>
				<div class="feature-section col three-col" >
					<div class="col">
						<h4><?php echo __( 'Display Chained items info on Product page', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'Chained Products provides you a shortcode <b>[chained_products]</b> using which you can easily display chained items information on product page.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
					<div class="col">
						<h4><?php echo __( 'Works well with other Product Types', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'Chained Products also works with <b>WooCommerce Product Bundles, WooCommerce Give Products, WooCommerce Composite Products, WooCommerce Mix \'n Match Products</b>.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
						<p>
							<?php echo __( 'You can set a Chained Parent as an item in any of the above product type. Now, whenever the product will be added to cart or order, chained parent along with its chained item will also be added to cart or order.', SA_WC_Chained_Products::$text_domain );?>
						</p>
					</div>
					<div class="col last-feature">
						<h4><?php echo __( 'Stock Dependency', SA_WC_Chained_Products::$text_domain ); ?></h4>
						<p>
							<?php echo __( 'Stock Management feature of Chained Products can be very powerful if your store is selling an assembled item. Though an assembled item is a single unit but it has many parts & in multiple quantity. You may have inventory for those individual item also. Chained Products can play very important role here.', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
						<p>
							<?php echo __( 'Let\'s take an example: You are selling Desktop PC. You\'ve created many Desktop PCs with different configuration. Each configuration individually is a separate product which is chained to Desktop PC. So, whenever any customer will order 1 Desktop PC, inventory of its parts will be reduced automatically', SA_WC_Chained_Products::$text_domain ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Output the Shortcode screen.
	 */
	public function shortcode_screen() {
		?>
		<script type="text/javascript">
			jQuery(document).on('ready', function(){
				jQuery('#menu-posts-product').find('a[href="edit.php?post_type=product"]').addClass('current');
				jQuery('#menu-posts-product').find('a[href="edit.php?post_type=product"]').parent().addClass('current');
			});
		</script>

		<div class="wrap about-wrap">

			<?php $this->intro(); ?>

            <h2 align="center"><em><code>[chained_products]</code></em></h2>

            <div>
            	<div class="feature-section col two-col">
            		<div class="col">
            			<p><?php echo __( 'Chained Products shortcode is created to show all chained products on a product page. Previously when shortcode feature was not available, all chained products were showing in an additional tab on product\'s page.', SA_WC_Chained_Products::$text_domain ); ?></p>
            		</div>
            		<div class="col last-feature">
            			<p><?php echo __( 'Now the shortcode gives you more flexibility & control on "How & where to display chained products". You can also set whether to show chained products in list format or grid format, show/hide price & quantity.', SA_WC_Chained_Products::$text_domain ); ?></p>
            		</div>
            	</div>
            </div>
            <div>
            	<h3 align="center"><?php echo __( 'Possible Usage', SA_WC_Chained_Products::$text_domain ); ?></h3><br>
            	<div>
            		<div class="feature-section col three-col">
            			<div class="col">
            				<p><code>[chained_products]</code></p>
            				<img src="http://docs.woothemes.com/wp-content/uploads/2012/05/default-shortcode.png" />
            			</div>
            			<div class="col">
            				<p><code>[chained_products price="no"]</code></p>
            				<img src="http://docs.woothemes.com/wp-content/uploads/2012/05/cp-shortcode-price-no.png" />
            			</div>
            			<div class="col last-feature">
            				<p><code>[chained_products price="yes" quantity="no" style="grid"]</code></p>
            				<img src="http://docs.woothemes.com/wp-content/uploads/2012/05/cp-shortcode-price-yes-qty-no-style-grid.png" />
            			</div>
            		</div>
            		<div class="feature-section col three-col">
            			<div class="col">
            				<p><code>[chained_products style="list"]</code></p>
            				<img src="http://docs.woothemes.com/wp-content/uploads/2012/05/cp-shortcode-style-list.png" />
            			</div>
            			<div class="col">
            				<p><code>[chained_products quantity="no" style="list"]</code></p>
            				<img src="http://docs.woothemes.com/wp-content/uploads/2012/05/cp-shortcode-qty-no-style-list.png" />
            			</div>
            			<div class="col last-feature">
            				<p><code>[chained_products price="no" quantity="yes" style="list"]</code></p>
            				<img src="http://docs.woothemes.com/wp-content/uploads/2012/05/cp-shortcode-price-no-qty-yes-style-list.png" />
            			</div>
            		</div>
            	</div>
            </div>
            <div>
				<h3 align="center"><?php echo __( 'Shortcode Attributes', SA_WC_Chained_Products::$text_domain ); ?></h3><br>
            	<div>
            		<table class="wp-list-table widefat striped">
            			<thead>
            				<tr>
            					<th><?php echo __( 'Attributes', SA_WC_Chained_Products::$text_domain ); ?></th>
            					<th><?php echo __( 'Values', SA_WC_Chained_Products::$text_domain ); ?></th>
            					<th><?php echo __( 'Default', SA_WC_Chained_Products::$text_domain ); ?></th>
            					<th><?php echo __( 'Description', SA_WC_Chained_Products::$text_domain ); ?></th>
            				</tr>
            			</thead>
            			<tbody>
            				<tr>
								<td><code>price</code></td>
								<td><code>yes</code> / <code>no</code></td>
								<td><code>yes</code></td>
								<td><?php echo __( 'show / hide prices of chained products', SA_WC_Chained_Products::$text_domain ); ?></td>
							</tr>
							<tr>
								<td><code>quantity</code></td>
								<td><code>yes</code> / <code>no</code></td>
								<td><code>yes</code></td>
								<td><?php echo __( 'show / hide quantities of chained products', SA_WC_Chained_Products::$text_domain ); ?></td>
							</tr>
							<tr>
								<td><code>style</code></td>
								<td><code>grid</code> / <code>list</code></td>
								<td><code>grid</code></td>
								<td><?php echo __( 'Display chained products in Grid view / List view', SA_WC_Chained_Products::$text_domain ); ?></td>
							</tr>
							<tr>
								<td><code>css_class</code></td>
								<td><?php echo __( 'any custom value', SA_WC_Chained_Products::$text_domain ); ?></td>
								<td></td>
								<td><?php echo __( 'You can add your custom CSS classes here. It\'ll be applicable on container which holds chained products. You can add CSS properties to your custom class in your theme', SA_WC_Chained_Products::$text_domain ); ?></td>
							</tr>
            			</tbody>
            		</table>
            	</div>
            </div>
		</div>

		<?php
	}


	/**
	 * Output the FAQ's screen.
	 */
	public function faqs_screen() {
		?>
		<script type="text/javascript">
			jQuery(document).on('ready', function(){
				jQuery('#menu-posts-product').find('a[href="edit.php?post_type=product"]').addClass('current');
				jQuery('#menu-posts-product').find('a[href="edit.php?post_type=product"]').parent().addClass('current');
			});
		</script>

		<div class="wrap about-wrap">

			<?php $this->intro(); ?>

            <h3><?php echo __("FAQ / Common Problems", SA_WC_Chained_Products::$text_domain); ?></h3>

            <?php
            	$faqs = array(
            				array(
            						'que' => __( 'Chained Products\' fields are broken', SA_WC_Chained_Products::$text_domain ),
            						'ans' => __( 'Make sure you are using latest version of Chained Products. If the issue still persist, deactivate all plugins except WooCommerce & Chained Products. Recheck the issue, if the issue still persists, contact us. If the issue goes away, re-activate other plugins one-by-one & re-checking the fields, to find out which plugin is conflicting. Inform us about this issue.', SA_WC_Chained_Products::$text_domain )
            					),
            				array(
            						'que' => __( 'Chained Products\' not visible on product page', SA_WC_Chained_Products::$text_domain ),
            						'ans' => sprintf(__( 'Re-check product\'s setting & try to find shortcode %s in description or short description field. If the shortcode is not available add it manually. You may also get a notification asking to insert shortcode for chained products. If so, click the link on notification & save the product. If shorcode is already available, then remove it, & type it again.', SA_WC_Chained_Products::$text_domain ), '<code>[chained_products]</code>' )
            					),
            				array(
            						'que' => __( 'Chained Products\' are not loading on product page OR it is showing incorrect data related to chained items', SA_WC_Chained_Products::$text_domain ),
            						'ans' => __( 'First you need to verify that it is not conflicting with any other plugin. You can follow same steps as mentioned in earlier FAQ which asks to deactivate all plugins except Chained Products. It can also be related to themes. To verify this, you can switch to other themes.', SA_WC_Chained_Products::$text_domain )
            					),
            				array(
            						'que' => __( 'Unable to increase quantity or add product to cart', SA_WC_Chained_Products::$text_domain ),
            						'ans' => __( 'A Chained parent can be attached with many other products. Manage stocks might be enabled in those products. If child item is available in limited quantity, it\'ll allow you to add main product only upto that limit, even if main product has sufficient stock.', SA_WC_Chained_Products::$text_domain )
            					)

            			);

				$faqs = array_chunk( $faqs, 2 );

				echo '<div>';
            	foreach ( $faqs as $fqs ) {
            		echo '<div class="two-col">';
            		foreach ( $fqs as $index => $faq ) {
            			echo '<div' . ( ( $index == 1 ) ? ' class="col last-feature"' : ' class="col"' ) . '>';
            			echo '<h4>' . $faq['que'] . '</h4>';
            			echo '<p>' . $faq['ans'] . '</p>';
            			echo '</div>';
            		}
            		echo '</div>';
            	}
            	echo '</div>';
            ?>

		</div>
		<div class="clear"></div>
		<div align="center">
			<p><?php echo sprintf(__( 'If you are facing any other issues, either search on %s & if not found submit a ticket from there itself.', SA_WC_Chained_Products::$text_domain ), '<a href="https://support.woothemes.com/">' . __( 'WooCommerce', SA_WC_Chained_Products::$text_domain ) . '</a>' ); ?></p>
		</div>

		<?php
	}


	/**
	 * Sends user to the welcome page on first activation.
	 */
	public function cp_welcome() {

       	if ( ! get_transient( '_chained_products_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_chained_products_activation_redirect' );

		wp_redirect( admin_url( 'admin.php?page=cp-about' ) );
		exit;

	}
}

new CP_Admin_Welcome();
