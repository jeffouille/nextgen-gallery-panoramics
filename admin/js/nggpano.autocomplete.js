/*
 * Implementation of jQuery UI Autocomplete
 * see http://jqueryui.com/demos/autocomplete/
 * Version:  1.0.1
 * Author : Geoffroy Deleury
 */ 
jQuery.fn.nggpanoAutocomplete = function ( args ) { 
    
    var defaults = { type: 'image',
                     domain: '',
                     limit: 50 };
    
    var s = jQuery.extend( {}, defaults, args);
    
    var settings = { method: 'autocomplete',
                    type: s.type,
                    format: 'json',
                    callback: 'json',
                    limit: s.limit };
                     
    var obj = this.selector;
    var id  = jQuery(this).attr('id');
    var cache = {}, lastXhr;
    
    // get current value of drop down field
    var c_text = jQuery(obj + ' :selected').text();
    var c_val  = jQuery(obj).val();
    // IE7 / IE 8 didnt get often the correct width
    if (s.width == undefined)  
        var c_width = jQuery(this).width();
    else
        var c_width = s.width;
    //hide first the drop down field
    jQuery(obj).hide();
    jQuery(obj).after('<input name="' + id + '_ac" type="text" id="' + id + '_ac"/>');
    // Fill up current value & style
    jQuery(obj + "_ac").val(c_text);
    jQuery(obj + "_ac").css('width', c_width);
    // Add the dropdown icon
    jQuery(obj + "_ac").addClass('ui-autocomplete-start')
    jQuery(obj + "_ac").autocomplete({
		source: function( request, response ) {
			var term = request.term;
			if ( term in cache ) {
				response( cache[ term ] );
				return;
			}
            // adding more $_GET parameter
            request = jQuery.extend( {}, settings, request);
			lastXhr = jQuery.getJSON( s.domain, request, function( data, status, xhr ) {
				// add term to cache
                cache[ term ] = data;
				if ( xhr === lastXhr )
					response( data );
			});
        },
        minLength: 0,
        select: function( event, ui ) {
            // adding this to the dropdown list
            jQuery(obj).append( new Option(ui.item.label, ui.item.id) );
            // now select it
            jQuery(obj).val(ui.item.id);
            jQuery(obj + "_ac").removeClass('ui-autocomplete-start');
	   }
	});

   	jQuery(obj + "_ac").click(function() {
   	    
   	    var search = jQuery(obj + "_ac").val();
        // if the value is prefilled, we pass a empty string
        if ( search == c_text)
            search = '';            
        // pass empty string as value to search for, displaying all results
        jQuery(obj + "_ac").autocomplete('search', search );
	});
}


jQuery.fn.nggpanoAutocompleteMultiple = function ( args ) { 
    
    var defaults = { type: 'image',
                     domain: '',
                     limit: 50 ,
                     multiple: false
                   };
    
    var s = jQuery.extend( {}, defaults, args);
    
    var settings = { method: 'autocomplete',
                    type: s.type,
                    format: 'json',
                    callback: 'json',
                    limit: s.limit };
                     
    var obj = this.selector;
    var id  = jQuery(this).attr('id');
    var cache = {}, lastXhr;
    
    var split = function ( val ) {
        return val.split( /,\s*/ );
    };
    
    var extractLast = function ( term ) {
        return split( term ).pop();
    };
    
    // get current value of drop down field
    var c_text = jQuery(obj + ' :selected').text();
    var c_val  = jQuery(obj).val();
    // IE7 / IE 8 didnt get often the correct width
    if (s.width == undefined)  
        var c_width = jQuery(this).width();
    else
        var c_width = s.width;
    //hide first the drop down field
    //jQuery(obj).hide();
    //jQuery(obj).after('<input name="' + id + '_ac" type="text" id="' + id + '_ac"/>');
    // Fill up current value & style
    //jQuery(obj + "_ac").val(c_text);
    //jQuery(obj + "_ac").css('width', c_width);
    // Add the dropdown icon
    //jQuery(obj + "_ac").addClass('ui-autocomplete-start')
    
    jQuery(obj)
    // don't navigate away from the field on tab when selecting an item
			/*
                        .bind( "keydown", function( event ) {
				if ( event.keyCode === jQuery.ui.keyCode.TAB &&	jQuery( this ).data( "autocomplete" ).menu.active ) {
					event.preventDefault();
				}
			})*/
			.autocomplete({
				source: function( request, response ) {
					jQuery.getJSON( "search.php", {
						term: extractLast( request.term )
					}, response );
				},
				search: function() {
					// custom minLength
					var term = extractLast( this.value );
					if ( term.length < 2 ) {
						return false;
					}
				},
				focus: function() {
					// prevent value inserted on focus
					return false;
				},
				select: function( event, ui ) {
					var terms = split( this.value );
					// remove the current input
					terms.pop();
					// add the selected item
					terms.push( ui.item.value );
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( ", " );
					return false;
				}
			});
    
   /* 
    jQuery(obj + "_ac").autocomplete({
        source: function( request, response ) {
            var term = request.term;
            if ( term in cache ) {
                response( cache[ term ] );
                return;
            }
            // adding more $_GET parameter
            request = jQuery.extend( {}, settings, request);
            lastXhr = jQuery.getJSON( s.domain, request, function( data, status, xhr ) {
                // add term to cache
                cache[ term ] = data;
                if ( xhr === lastXhr )
                    response( data );
            });
        },
        minLength: 0,
                            `/
        /*select: function( event, ui ) {
            // adding this to the dropdown list
            jQuery(obj).append( new Option(ui.item.label, ui.item.id) );
            // now select it
            jQuery(obj).val(ui.item.id);
            jQuery(obj + "_ac").removeClass('ui-autocomplete-start');
	   }
      */
        /*
        select: function( event, ui ) {
            var terms = split( this.value );
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push( ui.item.value );
            // add placeholder to get the comma-and-space at the end
            terms.push( "" );
            this.value = terms.join( ", " );
            return false;
        }

    });*/
/*
   	jQuery(obj).click(function() {
   	    
   	    var search = jQuery(obj).val();
        // if the value is prefilled, we pass a empty string
        if ( search == c_text)
            search = '';            
        // pass empty string as value to search for, displaying all results
        jQuery(obj).autocomplete('search', search );
	});*/
}
