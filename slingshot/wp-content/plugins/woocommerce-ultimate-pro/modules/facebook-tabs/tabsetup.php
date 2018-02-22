<?php
global $wp_query;

if(isset($_REQUEST['dtbaker_fbtab_action'])){
    switch($_REQUEST['dtbaker_fbtab_action']){
        case 'save_scripts_styles':
            $tab_id = (int)$_REQUEST['dtbaker_fbtab_id']; // todo: add support for multiple tabs in the future
            $enabled_scripts = @unserialize(get_option('dtbaker_fbtab'.$tab_id.'_enabled_scripts',''));
		    $enabled_styles = @unserialize(get_option('dtbaker_fbtab'.$tab_id.'_enabled_styles',''));
		    if(!is_array($enabled_scripts))$enabled_scripts=array();
		    if(!is_array($enabled_styles))$enabled_styles=array();
			// disable everything
			foreach($enabled_scripts as $enabled_script => $tf){
				$enabled_scripts[$enabled_script] = 0;
			}
			foreach($enabled_styles as $enabled_style => $tf){
				$enabled_styles[$enabled_style] = 0;
			}
			if(isset($_POST['enabled_scripts']) && is_array($_POST['enabled_scripts'])){
				foreach($_POST['enabled_scripts'] as $enabled_script => $tf){
					$enabled_scripts[$enabled_script] = $tf;
				}
			}
			if(isset($_POST['enabled_styles']) && is_array($_POST['enabled_styles'])){
				foreach($_POST['enabled_styles'] as $enabled_style => $tf){
					$enabled_styles[$enabled_style] = $tf;
				}
			}
			update_option('dtbaker_fbtab'.$tab_id.'_enabled_scripts',serialize($enabled_scripts));
            update_option('dtbaker_fbtab'.$tab_id.'_enabled_styles',serialize($enabled_styles));
            break;
        case 'save':
            $tab_id = (int)$_REQUEST['dtbaker_fbtab_id']; // todo: add support for multiple tabs in the future
            update_option('dtbaker_fbtab'.$tab_id.'_app_id',$_REQUEST['app_id']);
            update_option('dtbaker_fbtab'.$tab_id.'_app_secret',$_REQUEST['app_secret']);
            update_option('dtbaker_fbtab'.$tab_id.'_product_cat',isset($_REQUEST['product_cat']) ? $_REQUEST['product_cat'] : '');
            break;
    }
}
?>
<div class="wrap woocommerce">
    <h2><?php _e('Facebook Tab','woocommerce-facebook-tab');?></h2>
    <p>
        <strong><?php _e('How to setup Facebook tabs for WooCommerce products:','woocommerce-facebook-tab');?></strong>
    </p>
    <form action="" method="post">
        <?php $tab_id=1; // todo: add support for multiple tabs in the future ?>
        <input type="hidden" name="dtbaker_fbtab_id" value="<?php echo $tab_id;?>">
        <input type="hidden" name="dtbaker_fbtab_action" value="save">
        <ol>
            <?php if(!is_ssl()){ ?>
            <li><?php _e('<strong>IMPORTANT: </strong> SSL Security must be enabled for Facebook integration. Please talk with your hosting provider to setup SSL on this wordpress blog. To check if you have SSL please change "http://" to "https://" in your address bar and see if it wordpress loads without errors.','woocommerce-facebook-tab');?> </li>
            <?php } ?>
            <li><?php _e('Signup for a Facebook account if you don\'t already have one.','woocommerce-facebook-tab');?></li>
            <li><?php _e('Create a Facebook page for your business if you don\'t already have one (<a href="https://www.facebook.com/pages/create.php">click here for help</a>)','woocommerce-facebook-tab');?></li>
            <li><?php _e('Visit the <a href="https://developers.facebook.com/apps">Facebook Developer App</a>. If you haven\'t created an application before you will be prompted to add the Developer Application (ie: Press Allow)','woocommerce-facebook-tab');?></li>
            <li><?php _e('Click the "Add a New App" button','woocommerce-facebook-tab');?></li>
            <li><?php _e('Choose the "Website" option','woocommerce-facebook-tab');?></li>
            <li><?php _e('Enter your website name (eg: Bobs Online Gift Shop) and click "Create New Facebook App ID"','woocommerce-facebook-tab');?></li>
            <li><?php _e('Choose a Category and click "Create App ID"','woocommerce-facebook-tab');?></li>
            <li><?php _e('Click "Skip Quick Start" (top right)','woocommerce-facebook-tab');?></li>
	        <li><?php _e('Choose the "Settings" tab on the left','woocommerce-facebook-tab');?></li>
	        <li><?php _e('Click "Add Platform" and choose the "Page Tab" option','woocommerce-facebook-tab');?></li>
	        <li><?php printf(__('In the "App Domains" box enter your domain name (eg: %s)','woocommerce-facebook-tab'), $_SERVER["HTTP_HOST"]);?></li>
	        <li>In the "Secure Page Tab URL:" box enter this special URL: <strong><?php echo site_url('?post_type=product&'._WC_FB_TAB_KEY, 'https');?></strong></li>
	        <li><?php _e('In the "Page Tab Name:" box enter a short name that will display to users (eg: Online Shop, Our Products)','woocommerce-facebook-tab');?></li>
            <li>In the "Page Tab Edit URL:" box enter this special URL: <strong><?php echo admin_url();?>admin.php?page=woocommerce_facebook</strong></li>
            <li><?php _e('Click the "Save Changes" button.','woocommerce-facebook-tab');?></li>


            <li><?php _e('(optional) Go to the "App Details" tab and upload a logo to match your website/business logo. This logo will be displayed on the Facebook tab.','woocommerce-facebook-tab');?></li>
            <li><?php _e('Go back to the "Dashboard" tab and copy the "App ID" and "App Secret" into the boxes below:','woocommerce-facebook-tab');?></li>
            <li><?php _e('Enter your App ID:','woocommerce-facebook-tab');?>
                <input type="text" name="app_id" value="<?php echo esc_attr(get_option('dtbaker_fbtab'.$tab_id.'_app_id',''));?>" size="100">
            </li>
            <li><?php _e('Enter your App Secret:','woocommerce-facebook-tab');?>
                <input type="text" name="app_secret" value="<?php echo esc_attr(get_option('dtbaker_fbtab'.$tab_id.'_app_secret',''));?>" size="100">
            </li>
            <li><?php _e('Once you have entered the above settings please click this save button:','woocommerce-facebook-tab');?>
                <input type="submit" name="save" value="Save Facebook Settings">
            </li>
            <li>
                <?php _e('When you have entered the above settings, and pressed the save button, and are ready to add this to your Facebook Page','woocommerce-facebook-tab');?> <a href="https://www.facebook.com/dialog/pagetab?app_id=<?php echo esc_attr(get_option('dtbaker_fbtab'.$tab_id.'_app_id',''));?>&next=<?php echo urlencode(site_url().'/?'._WC_FB_TAB_KEY);?>"><?php _e('please click here','woocommerce-facebook-tab');?></a> <?php _e('to continue','woocommerce-facebook-tab');?>.
            </li>
        </ol>
    </form>

	<?php
	$enabled_scripts = @unserialize(get_option('dtbaker_fbtab'.$tab_id.'_enabled_scripts',''));
    $enabled_styles = @unserialize(get_option('dtbaker_fbtab'.$tab_id.'_enabled_styles',''));
    if(!is_array($enabled_scripts))$enabled_scripts=array();
    if(!is_array($enabled_styles))$enabled_styles=array();
	if(count($enabled_styles) || count($enabled_styles)){
	?>
	<form action="" method="post">
        <input type="hidden" name="dtbaker_fbtab_id" value="<?php echo $tab_id;?>">
        <input type="hidden" name="dtbaker_fbtab_action" value="save_scripts_styles">
    <h2><?php _e('Enabled Scripts and Styles','woocommerce-facebook-tab');?></h2>
		<p>These scripts and styles will be loaded on the Facebook page. If there are layout issues or errors please try to enable/disable some items below:</p>
		<ul>
		<?php foreach($enabled_scripts as $enabled_script => $tf){ ?>
			<li>Script: <strong><?php echo $enabled_script;?></strong> <input type="checkbox" name="enabled_scripts[<?php echo esc_attr($enabled_script);?>]" value="1"<?php echo $tf ? ' checked' : '';?>> </li>
		<?php } ?>
		<?php foreach($enabled_styles as $enabled_style => $tf){ ?>
			<li>Style: <strong><?php echo $enabled_style;?></strong> <input type="checkbox" name="enabled_styles[<?php echo esc_attr($enabled_style);?>]" value="1"<?php echo $tf ? ' checked' : '';?>> </li>
		<?php } ?>
		</ul>
		<input type="submit" name="save" value="Save Scripts and Styles">
	</form>
	<?php } ?>

    <h2><?php _e('Customisation','woocommerce-facebook-tab');?></h2>

    <p>Please feel free to customise the Facebook Tab layout by copying these <strong>default plugin template files</strong> from the plugin folder into your <strong>main theme directory</strong> (<?php echo get_stylesheet_directory();?>) and then customising them:</p>
    <ul>
        <li><?php echo 'plugins/woocommerce-facebook/templates/woocommerce-facebook-archive.php';?></li>
        <li><?php echo'plugins/woocommerce-facebook/templates/woocommerce-facebook-breadcrumb.php';?></li>
        <li><?php echo 'plugins/woocommerce-facebook/templates/woocommerce-facebook-cart.php';?></li>
        <li><?php echo 'plugins/woocommerce-facebook/templates/woocommerce-facebook-footer.php';?></li>
        <li><?php echo 'plugins/woocommerce-facebook/templates/woocommerce-facebook-header.php';?></li>
        <li><?php echo 'plugins/woocommerce-facebook/templates/woocommerce-facebook-single.php';?></li>
    </ul>
    <p>Please feel free to create a CSS file called <strong>woocommerce-facebook.css</strong> in your main theme directory and this will be loaded on the facebook tabs. By creating this file you can overwrite the default layout styles to match your needs.</p>

</div>