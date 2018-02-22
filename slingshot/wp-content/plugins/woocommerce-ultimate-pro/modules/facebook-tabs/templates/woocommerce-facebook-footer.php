</div>
<?php
ob_start();
wp_footer();
$foot = ob_get_clean();
echo $foot;
if(!preg_match('#FB\.init#',$foot)){
    $tab_id = 1;
    $app_id = get_option('dtbaker_fbtab'.$tab_id.'_app_id','');
    if($app_id){ ?>
    <div id="fb-root"></div>
    <script src="//connect.facebook.net/en_US/all.js"></script>
    <script>
        FB.init({
            appId : '<?php echo $app_id;?>',
            status : true, // check login status
            cookie : true, // enable cookies to allow the server to access the session
            xfbml : false // parse XFBML
        });
    </script>
    <?php }
}
?>
</body>
</html>