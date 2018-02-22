jQuery(document).ready(function()
{
	var error = null;
	var data = null;
	var current_index_data_to_send = 0;
	var total_data_to_send = 0;
	var max_row_chunk = 50;
	var current_row_chunk = 0;
	var last_row_chunk = 0;
	jQuery('#impor-submit-button').click(transition_out);
	jQuery('#fileToUpload').on('change', wccm_load_csv_file);
	
	function transition_out(e)
	{
		e.preventDefault();
		e.stopImmediatePropagation();
		if(error !=null || data == null)
		{
			 alert('File is not valid');
			return false;
		}
		current_index_data_to_send = 0;
		last_row_chunk = 1;
		current_row_chunk = max_row_chunk;
		/* var loader = '<div id="wcam-loading" style="margin-top:10px;">';
		loader += '<img src="<?php echo WCCM_PLUGIN_PATH; ?>/images/ajax-loader.gif"></img>';
		loader += '</div>';
		jQuery('#upload-istruction-box').append(loader); */
		//jQuery('#upload-istruction-box').fadeOut('400', function(){jQuery('#wcam-loading').fadeIn('400', wcam_upload_data)});
		jQuery('#ajax-progress-title, #ajax-response-title').delay(700).fadeIn(800);
		jQuery('#upload-istruction-box').fadeOut(800, function(){setTimeout(wccm_start_upload_csv, 1000); });
		
		return false;
	}
	function wccm_browserSupportFileUpload() 
	{
        var isCompatible = false;
        if (window.File && window.FileReader && window.FileList && window.Blob) {
        isCompatible = true;
        }
        return isCompatible;
    }
 
    // Method that reads and processes the selected file
    function wccm_load_csv_file(evt) 
	{
		if (!wccm_browserSupportFileUpload()) {
        alert('The File APIs are not fully supported in this browser!');
        } else {
            data = new Array();
            var file = evt.target.files[0];
            var reader = new FileReader();
            reader.readAsText(file);
            reader.onload = function(event) {
                var csvData = event.target.result;
				try{
                //data = jQuery.csv.toArrays(csvData);
				var data_temp = wccm_CSVToArray(csvData);
				for(var i = 0; i < data_temp.length; i++)
				{
					data.push('"'+data_temp[i].join('","')+'"');
				}
				
				}catch(e){error = e;}
                if (data && data.length > 0) {
                  //alert('Imported -' + data.length + '- rows successfully!');
				  total_data_to_send = data.length;
				  //console.log();
                } else {
                    alert('No data to import! Error: '+error);
                }
            };
            reader.onerror = function() {
                alert('Unable to read ' + file.fileName);
            };
        }
    }
	
	function wccm_start_upload_csv()
	{
		//console.log("Preparing");
		var dataToSend =  [];
		dataToSend.push(data[0]);
		for(var i = last_row_chunk;  i < current_row_chunk; i++)
		{
			//console.log("Row: "+i);
			dataToSend.push(data[i]);
		}
		
		wccm_upload_csv(dataToSend);
	}
	function wccm_upload_csv(dataToSend)
	{
		var formData = new FormData();
		formData.append('action', 'upload_csv'); 
		formData.append('send-notification-email', jQuery('#wccm-send-notification-email').val()); 
		formData.append('csv', dataToSend.join("<#>")); 
		var perc_num = ((current_row_chunk/total_data_to_send)*100);
		perc_num = perc_num > 100 ? 100:perc_num;
		
		var perc = Math.floor(perc_num);
		jQuery('#ajax-progress').html("<p>computing data, please wait...<strong>"+perc+"% done</strong></p>");
		jQuery( "#progressbar" ).progressbar({
					  value: perc
					});
					
		jQuery.ajax({
			url: ajax_url, //defined in php
			type: 'POST',
			data: formData,//{action: 'upload_csv', csv: data_to_send},
			async: false,
			success: function (data) {
				//alert(data);
				wccm_check_response(data);
			},
			error: function (data) {
				//alert("error: "+data);
				wccm_check_response(data);
			},
			cache: false,
			contentType: false,
			processData: false
		});
			
	}
	
	function wccm_check_response(data)
	{
		if(typeof data.responseText !== 'undefined')
		{
			jQuery('#ajax-response').append("<p>"+data.status+": "+data.statusText+"</p>");
			jQuery('#ajax-response').append("<p>"+data.responseText+"</p>");
		}
		else
			jQuery('#ajax-response').append("<p>"+data+"</p>");
				
		if(current_row_chunk < total_data_to_send)
		{
			last_row_chunk = current_row_chunk;
			current_row_chunk += max_row_chunk;
			if(current_row_chunk > total_data_to_send)
				current_row_chunk = total_data_to_send;
			//console.log(current_row_chunk+" "+total_data_to_send);
			wccm_start_upload_csv();
		}
		else
		{
			jQuery( "#progressbar" ).progressbar({
			  value: 100
			});
			jQuery('#ajax-progress').append("<p>100% done</p> <h3>end!</h3>");
		}
	}
	 // This will parse a delimited string into an array of
    // arrays. The default delimiter is the comma, but this
    // can be overriden in the second argument.
    function wccm_CSVToArray( strData, strDelimiter ){
        // Check to see if the delimiter is defined. If not,
        // then default to comma.
        strDelimiter = (strDelimiter || ",");
        // Create a regular expression to parse the CSV values.
        var objPattern = new RegExp(
            (
                // Delimiters.
                "(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +
                // Quoted fields.
                "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +
                // Standard fields.
                "([^\"\\" + strDelimiter + "\\r\\n]*))"
            ),
            "gi"
            );
        // Create an array to hold our data. Give the array
        // a default empty first row.
        var arrData = [[]];
        // Create an array to hold our individual pattern
        // matching groups.
        var arrMatches = null;
        // Keep looping over the regular expression matches
        // until we can no longer find a match.
        while (arrMatches = objPattern.exec( strData )){
            // Get the delimiter that was found.
            var strMatchedDelimiter = arrMatches[ 1 ];
            // Check to see if the given delimiter has a length
            // (is not the start of string) and if it matches
            // field delimiter. If id does not, then we know
            // that this delimiter is a row delimiter.
            if (
                strMatchedDelimiter.length &&
                (strMatchedDelimiter != strDelimiter)
                ){
                // Since we have reached a new row of data,
                // add an empty row to our data array.
                arrData.push( [] );
            }
            // Now that we have our delimiter out of the way,
            // let's check to see which kind of value we
            // captured (quoted or unquoted).
            if (arrMatches[ 2 ]){
                // We found a quoted value. When we capture
                // this value, unescape any double quotes.
                var strMatchedValue = arrMatches[ 2 ].replace(
                    new RegExp( "\"\"", "g" ),
                    "\""
                    );
            } else {
                // We found a non-quoted value.
                var strMatchedValue = arrMatches[ 3 ];
            }
            // Now that we have our value string, let's add
            // it to the data array.
            arrData[ arrData.length - 1 ].push( strMatchedValue );
        }
        // Return the parsed data.
        return( arrData );
    }
});