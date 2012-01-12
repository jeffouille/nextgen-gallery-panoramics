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
    jQuery('a.fancybox').fancybox({
		fitToView	: true,
                maxWidth	: 2000,
		maxHeight	: 1000,
		width		: '95%',
		height		: '95%',
		autoSize	: true,
		closeClick	: false,
                closeBtn        : false,
		openEffect	: 'fade',
		closeEffect	: 'fade',
		prevEffect	: 'fade',
		nextEffect	: 'fade',
		helpers	: {
			title	: {
				type: 'inside'
			},
			buttons	: {
                            tpl: '<div id="fancybox-buttons"><ul><li><a class="btnPrev" title="'+fancyboxi10nSettings.msgPrevious+'" href="javascript:;"></a></li><li><a class="btnPlay" title="'+fancyboxi10nSettings.msgStartSlideshow+'" href="javascript:;"></a></li><li><a class="btnNext" title="'+fancyboxi10nSettings.msgNext+'" href="javascript:;"></a></li><li><a class="btnToggle" title="'+fancyboxi10nSettings.msgToggleSize+'" href="javascript:;"></a></li><li><a class="btnClose" title="'+fancyboxi10nSettings.msgClose+'" href="javascript:jQuery.fancybox.close();"></a></li></ul></div>'
                        },
			overlay	: {
				opacity : 0.8,
				css : {
					'background-color' : '#000'
				}
			}//,
//			thumbs	: {
//				width	: 50,
//				height	: 50
//			}
		},
                tpl: {
                        error: '<p class="fancybox-error">'+fancyboxi10nSettings.msgError+'</p>',
                        closeBtn: '<div title="'+fancyboxi10nSettings.msgClose+'" class="fancybox-item fancybox-close"></div>',
                        next: '<a title="'+fancyboxi10nSettings.msgNext+'" class="fancybox-item fancybox-next"><span></span></a>',
                        prev: '<a title="'+fancyboxi10nSettings.msgPrevious+'" class="fancybox-item fancybox-prev"><span></span></a>'
                    }
	});
        
    jQuery('a.fancyboxpano').fancybox({
                type            : 'ajax',
		fitToView	: false,
		width		: '95%',
		height		: '95%',
		autoSize	: false,
		closeClick	: false,
                closeBtn        : false,
		openEffect	: 'fade',
		closeEffect	: 'fade',
		prevEffect	: 'none',
		nextEffect	: 'none',
                mouseWheel      : false,
                scrolling       : 'no',
                arrows          : false,
		helpers	: {
			title	: {
				type: 'inside'
			},
			buttons	: {
                            tpl: '<div id="fancybox-buttons"><ul><li><a class="btnPrev" title="'+fancyboxi10nSettings.msgPrevious+'" href="javascript:;"></a></li><li><a class="btnPlay" title="'+fancyboxi10nSettings.msgStartSlideshow+'" href="javascript:;"></a></li><li><a class="btnNext" title="'+fancyboxi10nSettings.msgNext+'" href="javascript:;"></a></li><li><a class="btnToggle" title="'+fancyboxi10nSettings.msgToggleSize+'" href="javascript:;"></a></li><li><a class="btnClose" title="'+fancyboxi10nSettings.msgClose+'" href="javascript:jQuery.fancybox.close();"></a></li></ul></div>'
                        },
			overlay	: {
				opacity : 0.8,
				css : {
					'background-color' : '#000'
				}
			}//,
//			thumbs	: {
//				width	: 50,
//				height	: 50
//			}
		},
                tpl: {
                        error: '<p class="fancybox-error">'+fancyboxi10nSettings.msgError+'</p>',
                        closeBtn: '<div title="'+fancyboxi10nSettings.msgClose+'" class="fancybox-item fancybox-close"></div>',
                        next: '<a title="'+fancyboxi10nSettings.msgNext+'" class="fancybox-item fancybox-next"><span></span></a>',
                        prev: '<a title="'+fancyboxi10nSettings.msgPrevious+'" class="fancybox-item fancybox-prev"><span></span></a>'
                    }
	});
    jQuery('a.fancyboxmap').fancybox({
                type            : 'ajax',
		fitToView	: false,
                afterShow       : function(){ initializeMap();},        
                maxWidth	: 800,
		maxHeight	: 600,
		width		: '70%',
		height		: '70%',
		autoSize	: false,
		closeClick	: false,
                closeBtn        : false,
		openEffect	: 'none',
		closeEffect	: 'none',
		prevEffect	: 'none',
		nextEffect	: 'none',
                mouseWheel      : false,
                scrolling       : 'no',
                arrows          : false,
		helpers	: {
			title	: {
				type: 'inside'
			},
			buttons	: {
                            tpl: '<div id="fancybox-buttons"><ul><li><a class="btnPrev" title="'+fancyboxi10nSettings.msgPrevious+'" href="javascript:;"></a></li><li><a class="btnPlay" title="'+fancyboxi10nSettings.msgStartSlideshow+'" href="javascript:;"></a></li><li><a class="btnNext" title="'+fancyboxi10nSettings.msgNext+'" href="javascript:;"></a></li><li><a class="btnToggle" title="'+fancyboxi10nSettings.msgToggleSize+'" href="javascript:;"></a></li><li><a class="btnClose" title="'+fancyboxi10nSettings.msgClose+'" href="javascript:jQuery.fancybox.close();"></a></li></ul></div>'
                        },
			overlay	: {
				opacity : 0.8,
				css : {
					'background-color' : '#000'
				}
			}//,
//			thumbs	: {
//				width	: 50,
//				height	: 50
//			}
		},
                tpl: {
                        error: '<p class="fancybox-error">'+fancyboxi10nSettings.msgError+'</p>',
                        closeBtn: '<div title="'+fancyboxi10nSettings.msgClose+'" class="fancybox-item fancybox-close"></div>',
                        next: '<a title="'+fancyboxi10nSettings.msgNext+'" class="fancybox-item fancybox-next"><span></span></a>',
                        prev: '<a title="'+fancyboxi10nSettings.msgPrevious+'" class="fancybox-item fancybox-prev"><span></span></a>'
                    }
	});
});

