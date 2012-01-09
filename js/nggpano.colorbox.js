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
        rel:'picture'
    });
    jQuery('a.colorboxpano').colorbox({
        width:'95%',
        height:'95%',
        scrolling : false,
        rel:'pano',
        onComplete:function(){ initializePano(); console.log('onComplete: colorbox has displayed the loaded content'); console.log(jQuery(this)); }
    });
    //jQuery('a.colorbox').colorbox({ opacity:0.5 , rel:'group1' });
});

