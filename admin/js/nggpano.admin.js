//var menuId = $("ul.nav").first().attr("id");
//var request = $.ajax({
//  url: "script.php",
//  type: "POST",
//  data: {id : menuId},
//  dataType: "html"
//});
//
//request.done(function(msg) {
//  $("#log").html( msg );
//});
//
//request.fail(function(jqXHR, textStatus) {
//  alert( "Request failed: " + textStatus );
//});

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}


function show_props(obj, obj_name) { /* debug method to alert objs */
	var result = "";
	for (var i in obj) 
		result += obj_name + "." + i + " = " + obj[i] + "\n" ;
	return result;
}

/*
 *
 * Delete pano in ajax and change the td dom
 */

// If Jquery version change, check on() method and delegate() method
jQuery("a.delete-pano").live("click", function(e){
                   e.preventDefault();
				var container = jQuery(this).parents('.nggpano_krpano_fields');
				container.find('img.nggpano-pano-loader').show();
				
				var url = jQuery(this).attr('href');
                                
                                var currentdiv = jQuery(this);
				//url += '&ajaxify=1';
				
				jQuery.ajax({
						url: url,
						data: '',
                                                dataType : 'html',
						success: function(data, textStatus, XMLHttpRequest) {
                                                    //{"error":false,"message":"GPS datas successfully saved","gps_data":{"latitude":48.27710215,"longitude":-4.59594487998,"altitude":7,"timestamp":"9:44:45"}}
                                                        if(data) {
                                                            //$('#waiting').hide(500);
                                                            //container.find('div.nggpano-error').removeClass((data.error === true) ? 'success' : 'error').addClass((data.error === true) ? 'error' : 'success').text(data.message).show(1000,function(){jQuery(this).delay(2000).hide(500);});
                                                            container.html(data);
//                                                            if (data.error === false) {
//                                                                //remove thumbnail
//                                                                container.find('a.shutter').remove();
//                                                                
//                                                                //remove link to delete
//                                                                currentdiv.remove();
//                                                                
//                                                            }
                                                            //console.log('delete ok');
                                                            //jQuery('div.nggpano-error').show(1000,function(){jQuery(this).delay(2000).hide(500);});
                                                            //container.find('div.nggpano-error').removeClass('error').addClass('class').show(1000,function(){jQuery(this).delay(2000).hide(500);});
							
                                                        }
						},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
                                                        container.find('div.nggpano-error').removeClass('success').addClass('error').text('There was a problem saving your data, please try again in a few moments.').show(1000,function(){jQuery(this).delay(2000).hide(500);});
								//jQuery('div.nggpano-error').show();
								//jQuery('div.nggpano-error').html('There was a problem saving your data, please try again in a few moments.');
						},
						complete: function() {
                                                        container.find('img.nggpano-pano-loader').hide();
                                                        container.find('div.nggpano-error').removeClass('error').addClass('class').delay(2000).hide(500);
						}
				});
				
				return false;
});


/*
 *
 * Open dialog window via ajax
 */

// If Jquery version change, check on() method and delegate() method
jQuery("a.nggpano-dialog").live("click", function(e){
        e.preventDefault();
        
        if ( jQuery( "#spinner" ).length == 0)
            jQuery("body").append('<div id="spinner"></div>');
        var $this = jQuery(this);
        var results = new RegExp('[\\?&]w=([^&#]*)').exec(this.href);
	    var width  = ( results ) ? results[1] : 600;
        var results = new RegExp('[\\?&]h=([^&#]*)').exec(this.href);
	    var height = ( results ) ? results[1] : 440;
        jQuery('#spinner').fadeIn();
        var dialog = jQuery('<div id="nggpano-dialog" style="display:hidden"></div>').appendTo('body');
        // load the remote content
        dialog.load(
            this.href, 
            {},
            function () {
                jQuery('#spinner').hide();
                dialog.dialog({
                    title: ($this.attr('title')) ? $this.attr('title') : '',
                    width: width,
                    height: height,
                    modal: true,
                    resizable: false,
                    close: function() { dialog.remove(); }
                }).width(width - 30).height(height - 30);
            }
        );
           
        //prevent the browser to follow the link
        return false;
    });
   
   
   
   

jQuery(document).ready(function() {
		jQuery('a.extractgps').click(function(e) {
				var container = jQuery(this).parents('.nggpano_gps_fields');
                                
				container.find('img.nggpano-gps-loader').show();
				
				var url = jQuery(this).attr('href');
				//url += '&ajaxify=1';
				
				jQuery.ajax({
						url: url,
						data: '',
                                                dataType : 'json',
						success: function(data, textStatus, XMLHttpRequest) {
                                                    //{"error":false,"message":"GPS datas successfully saved","gps_data":{"latitude":48.27710215,"longitude":-4.59594487998,"altitude":7,"timestamp":"9:44:45"}}
                                                        if(data) {
                                                            //$('#waiting').hide(500);
                                                            container.find('div.nggpano-error').removeClass((data.error === true) ? 'success' : 'error').addClass((data.error === true) ? 'error' : 'success').text(data.message).show(1000,function(){jQuery(this).delay(2000).hide(500);});
//                                                            
                                                            if (data.error === false) {
                                                                var input_lat_name = '#nggpano_picture_lat_' + data.pid;
                                                                var input_lng_name = '#nggpano_picture_lng_' + data.pid;
                                                                var input_alt_name = '#nggpano_picture_alt_' + data.pid;
                                                              
                                                                 jQuery(eval('input_lat_name')).val(data.gps_data['latitude']);
                                                                 jQuery(eval('input_lng_name')).val(data.gps_data['longitude']);
                                                                 jQuery(eval('input_alt_name')).val(data.gps_data['altitude']);
                                                            }
   
                                                        }
						},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
								jQuery('div.nggpano-error').show();
								jQuery('div.nggpano-error').html('There was a problem saving your data, please try again in a few moments.');
						},
						complete: function() {
							container.find('img.nggpano-gps-loader').hide();
						}
				});
				e.preventDefault();
				return false;
		});

});


/* 
 * FUNCTIONS FOR BULK ACTIONS
 * 
 */
