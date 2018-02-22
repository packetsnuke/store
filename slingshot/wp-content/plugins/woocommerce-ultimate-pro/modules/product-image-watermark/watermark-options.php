<?php
$upload_dir   = wp_upload_dir();
if(isset($_REQUEST['dtbaker_watermark_action'])){
    switch($_REQUEST['dtbaker_watermark_action']){
        case 'save':
            foreach(array('main','popup','thumbnail','catalog') as $type){
                update_option('watermark_'.$type.'_image',$_REQUEST['watermark_'.$type.'_image']);
                update_option('watermark_'.$type.'_position',$_REQUEST['watermark_'.$type.'_position']);
            }
            break;
    }
}
//ini_set('display_errors',true);
//ini_set('error_reporting',E_ALL);
?>
<script type="text/javascript">
    function watermark_plugin_info(){
        tb_show('', 'plugin-install.php?tab=plugin-information&plugin=regenerate-thumbnails&TB_iframe=true&width=640&height=846');
    }
    jQuery(document).ready(function() {

        var watermark_current_type = '';
        <?php foreach(array('main','popup','thumbnail','catalog') as $type){ ?>
        jQuery('#upload_image_button_<?php echo $type;?>').click(function() {
            watermark_current_type = '<?php echo $type;?>';
            formfield = jQuery('#upload_image_<?php echo $type;?>').attr('name');
            tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
            return false;
        });
        <?php } ?>


        window.send_to_editor = function(html) {
            var imgurl = jQuery('img',html).attr('src');
	        if(typeof imgurl == 'undefined'){
		        var h = jQuery(html);
		        imgurl = h.attr('src');
	        }
            imgurl = imgurl.replace('<?php echo $upload_dir['baseurl']; ?>','');
            jQuery('#upload_image_'+watermark_current_type).val(imgurl);
            tb_remove();
        }

    });
</script>


<div class="wrap woocommerce">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2><?php _e('Watermark Settings','woocommerce-watermark');?></h2>
    <em><?php _e('Important: Please use a PNG image for the watermark','woocommerce-watermark');?></em>

    <form method="post" action="">
        <input type="hidden" name="dtbaker_watermark_action" value="save">
        <table class="form-table">
            <tbody>
            <?php
            $thumbnail_types = array(
                array('main',__('This watermark is applied to the main featured image on a product page','woocommerce-watermark')),
                array('popup',__('Applied to larger product images that display in lightbox when clicking thumbnails','woocommerce-watermark')),
                array('thumbnail',__('Applied to small product thumbnails images on product page','woocommerce-watermark')),
                array('catalog',__('Applied to small product images displayed in main category listings','woocommerce-watermark'))
            );
            while(count($thumbnail_types)>0){ ?>
            <tr>
                <?php for($tx=0;$tx<2;$tx++){
                $type = array_shift($thumbnail_types);
                ?>
                <td valign="top" style="border:1px solid #EFEFEF">
                    <?php if($type){ ?>
                    <strong><?php echo sprintf(__('%s Watermark Image','woocommerce-watermark'),ucwords($type[0]));?></strong> <br/>
                    <span class="description"><?php echo $type[1];?></span> <br/>

                    <fieldset>
                        <label for="upload_image">
                            <input id="upload_image_<?php echo $type[0];?>" type="text" size="36" name="<?php echo 'watermark_'.$type[0].'_image';?>" value="<?php echo esc_attr(get_option('watermark_'.$type[0].'_image'));?>" /> <br/>
                            <input id="upload_image_button_<?php echo $type[0];?>" type="button" value="<?php _e('Choose Watermark Image');?>" />

                        </label>
                    </fieldset>
                    <span class="description"><?php _e('Choose watermark alignment');?></span> <br/>
                    <fieldset>
                        <table id="watermark_position" border="1">
                            <?php $watermark_position = get_option('watermark_'.$type[0].'_position'); ?>
                            <?php foreach(array('t','m','b') as $y) : ?>
                            <tr>
                                <?php foreach(array('l','c','r') as $x) : ?>
                                <?php $checked = $watermark_position == $y . $x; ?>

                                <td title="<?php echo ucfirst($y . ' ' . $x); ?>">
                                    <input name="watermark_<?php echo $type[0];?>_position" type="radio" value="<?php echo $y . $x; ?>"<?php echo $checked ? ' checked="checked"' : null; ?> />
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3">
                                    <input name="watermark_<?php echo $type[0];?>_position" type="radio" value="none"<?php echo (!$watermark_position || $watermark_position == 'none') ? ' checked="checked"' : null; ?> /> <?php _e('No watermark');?>
                                </td>
                            </tr>
                        </table>


                    </fieldset>
                    <?php } ?>
                </td>
                <?php } ?>
            </tr>
                <?php } ?>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes','woocommerce-watermark');?>" />
        </p>
        <p>
            <?php echo sprintf(__('Need to regenerate your thumbnails after updating watermark settings? Try the free "Regenerate Thumbnails" plugin. <a href="#" onclick="%s">Click here</a> for more details.'),'watermark_plugin_info(); return false;');?>
        </p>
    </form>
</div>
