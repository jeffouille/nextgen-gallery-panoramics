/*
 *  ColorBox Customusation for NGGPano Plugin 
 *  Version   : 1.0
 *  Date      : 2012-01-05
 *  Author    : Geoffroy DELEURY

 *   
 *  More information in this url : http://jacklmoore.com/notes/colorbox-for-beginners/
 * 
 */

jQuery(document).ready(function(){
    jQuery('a.colorbox').colorbox({
        width:'95%',
        onComplete: function(){
            jQuery('#cboxLoadedContent').zoom({grab: true});
        }
    });
    jQuery('a.colorboxpano').colorbox({
        width:'95%',
        height:'95%',
        scrolling : false,
        onComplete:function(){ initializePano(); }
    });
    jQuery('a.colorboxmap').colorbox({
        scrolling : false,
        width:'600',
        height:'600',
        onComplete:function(){ initializeMap(); }
    });
    //jQuery('a.colorbox').colorbox({ opacity:0.5 , rel:'group1' });
});

