<?php 

// include DomPDF autoloader
require_once ( ULTIMATEWOO_MODULES_DIR . "/pdf-invoice/lib/dompdf_config.inc.php" );

// reference the Dompdf namespace
use Dompdf\Dompdf;

$current_user = wp_get_current_user();

$server_configs = array(
  "PHP Version" => array(
    "required" => "5.6",
    "value"    => phpversion(),
    "result"   => version_compare(phpversion(), "5.6"),
  ),
  "DOMDocument extension" => array(
    "required" => true,
    "value"    => phpversion("DOM"),
    "result"   => class_exists("DOMDocument"),
  ),
  "PCRE" => array(
    "required" => true,
    "value"    => phpversion("pcre"),
    "result"   => function_exists("preg_match") && @preg_match("/./u", "a"),
    "failure"  => "PCRE is required with Unicode support (the \"u\" modifier)",
  ),
  "Zlib" => array(
    "required" => true,
    "value"    => phpversion("zlib"),
    "result"   => function_exists("gzcompress"),
    "fallback" => "Recommended to compress PDF documents",
  ),
  "Iconv extension" => array(
    "required" => true,
    "value"    => ICONV_VERSION,
    "result"   => extension_loaded('iconv'),
    "failure"  => "ICONV is required, please contact your host to have it installed.",
  ),
  "Multibyte Support" => array(
    "required" => true,
    "value"    => extension_loaded('mbstring'),
    "result"   => extension_loaded('mbstring'),
    "failure"  => "Multibyte Support is required, please contact your host to have it installed.",
  ),
  "GD" => array(
    "required" => true,
    "value"    => phpversion("gd"),
    "result"   => function_exists("imagecreate"),
    "fallback" => "Required if you have images in your documents",
  ),
  "APC" => array(
    "required" => "For better performances",
    "value"    => phpversion("apc"),
    "result"   => function_exists("apc_fetch"),
    "fallback" => "Recommended for better performances",
  ),
  "GMagick or IMagick" => array(
    "required" => "Better with transparent PNG images",
    "value"    => null,
    "result"   => extension_loaded("gmagick") || extension_loaded("imagick"),
    "fallback" => "Recommended for better performances",
  ),
);

if (($gm = extension_loaded("gmagick")) || ($im = extension_loaded("imagick"))) {
  $server_configs["GMagick or IMagick"]["value"] = ($im ? "IMagick ".phpversion("imagick") : "GMagick ".phpversion("gmagick"));
}

?>

<table class="pdfsetup form-table">
  <tr class="pdfheaderrow">
    <th></th>
    <th>Required</th>
    <th>Present</th>
  </tr>
  
  <?php 
  $row 		= 'even';
  $rowcount = 0;
  foreach( $server_configs as $label => $server_config ) { 
  
  	$rowcount++;
	$row = ($rowcount % 2 == 0 ? 'even' : 'odd');
  ?>
    <tr class="pdf-<?php echo $row; ?>">
      <th class="title"><?php echo $label; ?></th>
      <td><?php echo ($server_config["required"] === true ? "Yes" : $server_config["required"]); ?></td>
      <td class="<?php echo ($server_config["result"] ? "ok" : (isset($server_config["fallback"]) ? "warning" : "failed")); ?>">
        <?php
        echo $server_config["value"];
        if ($server_config["result"] && !$server_config["value"]) echo "Yes";
        if (!$server_config["result"]) {
          if (isset($server_config["fallback"])) {
            echo "<div>No. ".$server_config["fallback"]."</div>";
          }
          if (isset($server_config["failure"])) {
            echo "<div>".$server_config["failure"]."</div>";
          }
        }
        ?>
      </td>
    </tr>
  <?php } ?>
  
</table>

<h3 id="dompdf-config"><?php _e("Send test email with PDF attachment" , 'ultimatewoo-pro' ); ?></h3>
                    
<form method="post" action="" >
<table>
	<tr>
    	<th><?php _e("Enter email address" , 'ultimatewoo-pro' ); ?></th>
        <td><input type="email" name="pdfemailtest-emailaddress" /><?php wp_nonce_field('pdf_test_nonce_action','pdf_test_nonce'); ?></td>
        <td><input type="hidden" name="pdfemailtest" value="1" /><input type="submit" value="<?php _e("Send test email with PDF Attachment" , 'ultimatewoo-pro' ); ?>" /></td>
	</tr>
</table>
</form>
<?php if( in_array('administrator', $current_user->roles) ) { ?>
<h3 id="dompdf-config-2"><?php _e("Delete Invoice Information" , 'ultimatewoo-pro' ); ?></h3>
<p><?php _e("This is an unrecoverable option, use with caution." , 'ultimatewoo-pro' ); ?></p>
<p><?php _e('You can delete the invoice information store in each order. The information can only be recovered using a backup of your database. USE WITH CAUTION!"' , 'ultimatewoo-pro' ); ?></p>
<form method="post" action="" >
<table>
  <tr>
      <th><?php _e("Type 'confirm' to confirm you understand that this will delete all of the invoice information stored in each order." , 'ultimatewoo-pro' ); ?></th>
        <td><input type="text" name="pdfdelete-confirmaion" /><?php wp_nonce_field('pdf_delete_nonce_action','pdf_delete_nonce'); ?></td>
        <td><input type="hidden" name="pdfdelete" value="1" /><input type="submit" value="<?php _e("Delete invoice information from orders and reset invoice numbers" , 'ultimatewoo-pro' ); ?>" /></td>
  </tr>
</table>
</form>
<?php } ?>