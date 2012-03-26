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
    
    jQuery('body').delegate('a.colorbox', 'click' ,function(event){

        jQuery.colorbox({
            href: this.href,
            rel:this.rel,
            title:this.title,
            width:'95%',
            onComplete: function(){
                jQuery('#cboxLoadedContent').zoom({grab: true});
            }
        });

        event.preventDefault();
    });


    jQuery('body').delegate('a.colorboxpano', 'click' ,function(event){
        jQuery.colorbox({
            href:this.href,
            rel:this.rel,
            title:this.title,
            width:'95%',
            height:'95%',
            scrolling : false,
            onComplete:function(){
                initializePano();
            }
        });
        event.preventDefault();
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
    jQuery('body').delegate('a.colorboxmap', 'click' ,function(event){
        jQuery.colorbox({
            href:this.href,
            rel:this.rel,
            title:this.title,
            width:'600',
            height:'600',
            scrolling : false,
            onComplete:function(){
                initializeMap();
            }
        });
        event.preventDefault();
    });

});

