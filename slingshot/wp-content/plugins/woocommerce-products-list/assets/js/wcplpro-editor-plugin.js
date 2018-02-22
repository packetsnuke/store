(function() {
      
    // Register plugin
    tinymce.create( 'tinymce.plugins.wcplpro', {
      
      init: function( editor, url )  {

        editor.addButton( 'wcplpro', {
            title: 'Woocommerce Products List Pro',
            image : url+ '/../../images/editor.png',
            cmd: 'wcplpro_command'
        });
        
        
        // Called when we click the Insert Gistpen button
        editor.addCommand( 'wcplpro_command', function() {
          // Calls the pop-up modal
          editor.windowManager.open({
            
            title: 'Insert Woocommerce Products List',
            width: 500,
            height: 400,
            inline: 1,
            id: 'wcplpro-insert-dialog',
            
            buttons: [{
              text: 'Insert',
              id: 'plugin-wcplpro-button-insert',
              classes: 'widget btn primary first abs-layout-item',
              onclick: function( e ) {
                insert_wcplpro(editor);
              },
            },
            {
              text: 'Cancel',
              id: 'plugin-wcplpro-button-cancel',
              onclick: 'close'
            }]
          });
          
          appendwcplproInsertDialog(url);
          
        });
        
        
        
      
      }
  });
  
  
  tinymce.PluginManager.add( 'wcplpro', tinymce.plugins.wcplpro );
  
  
  function insert_wcplpro(editor) {
    
    var shortcode_atts = '';
    
    jQuery('table#wcplpro_shorcode_table tr').each(function() {
      
      if (jQuery(this).find('td.setting_value input').length > 0 && jQuery(this).find('td.setting_value input').val() != '' && jQuery(this).find('td.setting_value input').attr('id').indexOf('s2id') === -1) {
        shortcode_atts = shortcode_atts + ' ' + jQuery(this).find('td.setting_value input').attr('id').replace('wcplpro_', '') + '="' + jQuery(this).find('td.setting_value input').val() +'"';
      }
      if (jQuery(this).find('td.setting_value select').length > 0 && jQuery(this).find('td.setting_value select').val() != '' && jQuery(this).find('td.setting_value select').val() != null && jQuery(this).find('td.setting_value select').attr('id') != 's2id_autogen2') {
        shortcode_atts = shortcode_atts + ' ' + jQuery(this).find('td.setting_value select').attr('id').replace('wcplpro_', '') + '="' + jQuery(this).find('td.setting_value select').val() +'"';
      }
      
    });

    
    editor.insertContent( '[wcplpro '+ shortcode_atts +']');
    editor.windowManager.close();
  }
  
  
  
  
  function appendwcplproInsertDialog(url) {
    
            
    var dialogBody = jQuery( '#wcplpro-insert-dialog-body' );
    
		// Get the form template from WordPress
		jQuery.post( ajaxurl, {
			action: 'wcplpro_insert_dialog'
		}, function( response ) {
			template = response;
      
     

			dialogBody.append( template );
      
      jQuery(".wcprpro-enhanced-select").select2({
          allowClear: true,
          placeholder: jQuery(this).data('placeholder'),
      });    
      
		});

	}
  
  
})();