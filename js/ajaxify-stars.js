function show_props(obj, obj_name) { /* debug method to alert objs */
	var result = "";
	for (var i in obj) 
		result += obj_name + "." + i + " = " + obj[i] + "\n" ;
	return result;
}

jQuery(document).ready(function() {
		jQuery('ul.star-rating li a').click(function(e) {
				var container = jQuery(this).parents('.nggv_container');
				
				container.find('img.nggv-star-loader').show();
				
				var url = jQuery(this).attr('href');
				url += '&ajaxify=1';
				
				jQuery.ajax({
						url: url,
						data: '',
						success: function(data, textStatus, XMLHttpRequest) {
							var start = data.indexOf("<!--#NGGV START AJAX RESPONSE#-->") + 33; //find the start of the outputting by the ajax url (stupid wordpress and poor buffering options blah blah)
							var end = data.indexOf("<!--#NGGV END AJAX RESPONSE#-->");
							
							eval(data.substr(start, (end-start))); //the array of voters gets echoed out at the ajax url
							
							if(typeof(nggv_js) == 'object') {
								var msg = '';
								if(nggv_js.saved) {
									jQuery(document).focus();
									container.html(nggv_js.nggv_container);
								}else{
									if(nggv_js.msg) {
										msg = nggv_js.msg
									}else{ //there should always be a msg, but just in case lets default one
										msg = 'There was a problem saving your vote, please try again in a few moments.';
									}
								}
							}else{
								msg = 'There was a problem saving your vote, please try again in a few moments.';
							}
							
							if(msg) {
								//the 'continer' div and 'nggv-error' div are on the same dom level, making them siblings
								container.siblings('div.nggv-error').show();
								container.siblings('div.nggv-error').html(msg);
							}
						},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
								jQuery('div.nggv-error').show();
								jQuery('div.nggv-error').html('There was a problem saving your vote, please try again in a few moments.');
						},
						complete: function() {
							container.find('img.nggv-star-loader').hide();
						}
				});
				
				e.preventDefault();
				return false;
		});
});