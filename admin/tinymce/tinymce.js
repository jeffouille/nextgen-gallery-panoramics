
function nggpanoinit() {
	tinyMCEPopup.resizeToInnerSize();        
}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function insertNGGPANOLink() {
	
	var tagtext;
	
	var galleryPanel = jQuery('#gallery_panel');
	var albumPanel = jQuery('#album_panel');
	var panoramicPanel = jQuery('#panoramic_panel');
        
        var panoCallback = jQuery('#panoCallback');
        var albumCallback = jQuery('#albumCallback'); 
        var galleryCallback = jQuery('#galleryCallback');
	
	// who is active ?
        //Gallery Panel
	if (galleryPanel.hasClass('current')) {
                //get values
                var galleryid = jQuery('#galleries').val();
                var galleryShortcodetype = jQuery("input[name='galleryShortcodetype']:checked").val();
		var galleryWidth = jQuery('#galleryWidth').val();
		var galleryHeight = jQuery('#galleryHeight').val();
                var galleryAlign = jQuery("input[name='galleryAlign']:checked").val();
                var galleryMapW = jQuery('#galleryMapW').val();
		var galleryMapH = jQuery('#galleryMapH').val();
                var galleryMapZ = jQuery('#galleryMapZ').val();
                var galleryMaptype = jQuery("input[name='galleryMaptype']:checked").val();
                var galleryLinks = jQuery("input[name='galleryLinks']:checked").val();
                var galleryLinksChoice = jQuery('.galleryLinksChoice:checkbox:checked').map(function() {
                    return this.value;
                }).get();
                var galleryMainLink = jQuery("input[name='galleryMainlink']:checked").val();
                //formated value
                var galleryWidth_attr = '';
                var galleryHeight_attr = '';
                var galleryMapZ_attr = '';
                if(galleryShortcodetype == 'gallerymap') {
                    galleryWidth_attr = (galleryMapW == '') ? '' : ' w=' + galleryMapW;
                    galleryHeight_attr = (galleryMapH == '') ? '' : ' h=' + galleryMapH;
                    galleryMapZ_attr = (galleryMapZ == '' ) ? '' : ' zoom=' + galleryMapZ;
                } else {
                    galleryWidth_attr = (galleryWidth == '') ? '' : ' w=' + galleryWidth;
                    galleryHeight_attr = (galleryHeight == '') ? '' : ' h=' + galleryHeight;
                    galleryMapZ_attr = (galleryMapZ == '' ) ? '' : ' mapz=' + galleryMapZ;
                }
                var galleryAlign_attr = (galleryAlign == 'none' || galleryAlign == '') ? '' : ' float=' + galleryAlign;
                var galleryMapW_attr = (galleryMapW == '' ) ? '' : ' mapw=' + galleryMapW;
                var galleryMapH_attr = (galleryMapH == '' ) ? '' : ' maph=' + galleryMapH;
                var galleryMaptype_attr = (galleryMaptype == '' ) ? '' : ' maptype=' + galleryMaptype;
                var galleryLinks_attr = '';
                switch(galleryLinks) {
                    case 'none':
                        galleryLinks_attr = '';
                    break;
                    case 'all':
                        galleryLinks_attr = ' links=all';
                    break;
                    case 'select':
                        if(galleryLinksChoice.length >0) {
                            galleryLinks_attr = ' links='+galleryLinksChoice.join("-");
                        } else {
                            galleryLinks_attr = '';
                        }
                    break;
                }
                var galleryMainLink_attr = (galleryMainLink == '' ) ? '' : ' mainlink=' + galleryMainLink;
                galleryMainLink_attr = (galleryLinks_attr == '') ? '' : galleryMainLink_attr;

		if (galleryid != null ) {
                    
                    tagtext = "[" + galleryShortcodetype + " id=" + galleryid + galleryWidth_attr + galleryHeight_attr + galleryAlign_attr;
                    
                    switch(galleryShortcodetype) {
                        case 'panoramicgallery':
                            tagtext += '';
                        break;
                        case 'panoramicgallerywithmap':
                            tagtext +=  galleryMapW_attr + galleryMapH_attr + galleryMapZ_attr + galleryMaptype_attr;
                        break;
                        case 'gallerymap':
                            tagtext +=  galleryMapW_attr + galleryMapH_attr + galleryMapZ_attr + galleryMaptype_attr + galleryLinks_attr + galleryMainLink_attr;
                        break;
                    }
                    tagtext += "]";
                    galleryCallback.addClass('hidden').removeClass('error').removeClass('message');
                    
//                    galleryCallback.removeClass('hidden').addClass('error').addClass('message');
//                    galleryCallback.text(tagtext);
//                    return;
                    
                } else {
                    //panoCallback.text(NGGPANOTinyoptions.pleaseSelectText);
                    galleryCallback.removeClass('hidden').addClass('error').addClass('message');
                    galleryCallback.text(tinyMCEPopup.getLang('NGGPano.pleaseSelectGalleryText'));
                    return;
                    //tinyMCEPopup.close();
		}
	}
	// Album Panel
	if (albumPanel.hasClass('current')) {
                //get values
                var albumid = jQuery('#albums').val();
                var albumShortcodetype = jQuery("input[name='albumShortcodetype']:checked").val();
		var albumWidth = jQuery('#albumWidth').val();
		var albumHeight = jQuery('#albumHeight').val();
                var albumAlign = jQuery("input[name='albumAlign']:checked").val();
                var albumMapW = jQuery('#albumMapW').val();
		var albumMapH = jQuery('#albumMapH').val();
                var albumMapZ = jQuery('#albumMapZ').val();
                var albumMaptype = jQuery("input[name='albumMaptype']:checked").val();
                var albumLinks = jQuery("input[name='albumLinks']:checked").val();
                var albumLinksChoice = jQuery('.albumLinksChoice:checkbox:checked').map(function() {
                    return this.value;
                }).get();
                var albumMainLink = jQuery("input[name='albumMainlink']:checked").val();
                //formated value
                var albumWidth_attr = '';
                var albumHeight_attr = '';
                var albumMapZ_attr = '';
                if(albumShortcodetype == 'albummap') {
                    albumWidth_attr = (albumMapW == '') ? '' : ' w=' + albumMapW;
                    albumHeight_attr = (albumMapH == '') ? '' : ' h=' + albumMapH;
                    albumMapZ_attr = (albumMapZ == '' ) ? '' : ' zoom=' + albumMapZ;
                } else {
                    albumWidth_attr = (albumWidth == '') ? '' : ' w=' + albumWidth;
                    albumHeight_attr = (albumHeight == '') ? '' : ' h=' + albumHeight;
                    albumMapZ_attr = (albumMapZ == '' ) ? '' : ' mapz=' + albumMapZ;
                }
                var albumAlign_attr = (albumAlign == 'none' || albumAlign == '') ? '' : ' float=' + albumAlign;
                var albumMapW_attr = (albumMapW == '' ) ? '' : ' mapw=' + albumMapW;
                var albumMapH_attr = (albumMapH == '' ) ? '' : ' maph=' + albumMapH;
                var albumMaptype_attr = (albumMaptype == '' ) ? '' : ' maptype=' + albumMaptype;
                var albumLinks_attr = '';
                switch(albumLinks) {
                    case 'none':
                        albumLinks_attr = '';
                    break;
                    case 'all':
                        albumLinks_attr = ' links=all';
                    break;
                    case 'select':
                        if(albumLinksChoice.length >0) {
                            albumLinks_attr = ' links='+albumLinksChoice.join("-");
                        } else {
                            albumLinks_attr = '';
                        }
                    break;
                }
                var albumMainLink_attr = (albumMainLink == '' ) ? '' : ' mainlink=' + albumMainLink;
                albumMainLink_attr = (albumLinks_attr == '') ? '' : albumMainLink_attr;
                
		if (albumid != null ) {
                    
                    tagtext = "[" + albumShortcodetype + " id=" + albumid + albumWidth_attr + albumHeight_attr + albumAlign_attr;
                    
                    switch(albumShortcodetype) {
                        case 'panoramicalbum':
                            tagtext += '';
                        break;
                        case 'panoramicalbumwithmap':
                            tagtext +=  albumMapW_attr + albumMapH_attr + albumMapZ_attr + albumMaptype_attr;
                        break;
                        case 'albummap':
                            tagtext +=  albumMapW_attr + albumMapH_attr + albumMapZ_attr + albumMaptype_attr + albumLinks_attr + albumMainLink_attr;
                        break;
                    }
                    tagtext += "]";
                    albumCallback.addClass('hidden').removeClass('error').removeClass('message');
                    
//                    albumCallback.removeClass('hidden').addClass('error').addClass('message');
//                    albumCallback.text(tagtext);
//                    return;
                    
                } else {
                    //panoCallback.text(NGGPANOTinyoptions.pleaseSelectText);
                    albumCallback.removeClass('hidden').addClass('error').addClass('message');
                    albumCallback.text(tinyMCEPopup.getLang('NGGPano.pleaseSelectAlbumText'));
                    return;
                    //tinyMCEPopup.close();
		}
	}
        //Panoramic Panel
	if (panoramicPanel.hasClass('current')) {
                //get values
                var panolist = jQuery('#panoramics').val() || [];
                var panoShortcodetype = jQuery("input[name='panoShortcodetype']:checked").val();
		var panoWidth = jQuery('#panoWidth').val();
		var panoHeight = jQuery('#panoHeight').val();
		var panoEffect = jQuery('#panoEffect').val();
                var panoAlign = jQuery("input[name='panoAlign']:checked").val();
                var panoCaptiontype = jQuery("input[name='panoCaptiontype']:checked").val();
                var panoMapW = jQuery('#panoMapW').val();
		var panoMapH = jQuery('#panoMapH').val();
                var panoMapZ = jQuery('#panoMapZ').val();
                var panoMaptype = jQuery("input[name='panoMaptype']:checked").val();
                var panoLinks = jQuery("input[name='panoLinks']:checked").val();
                var panoLinksChoice = jQuery('.panoLinksChoice:checkbox:checked').map(function() {
                    return this.value;
                }).get();
                var panoMainLink = jQuery("input[name='panoMainlink']:checked").val();
                //formated value
                var panolistformat = panolist.join(",");
                var panoWidth_attr = '';
                var panoHeight_attr = '';
                var panoMapZ_attr = '';
                if(panoShortcodetype == 'singlemap') {
                    panoWidth_attr = (panoMapW == '') ? '' : ' w=' + panoMapW;
                    panoHeight_attr = (panoMapH == '') ? '' : ' h=' + panoMapH;
                    panoMapZ_attr = (panoMapZ == '' ) ? '' : ' zoom=' + panoMapZ;
                } else {
                    panoWidth_attr = (panoWidth == '') ? '' : ' w=' + panoWidth;
                    panoHeight_attr = (panoHeight == '') ? '' : ' h=' + panoHeight;
                    panoMapZ_attr = (panoMapZ == '' ) ? '' : ' mapz=' + panoMapZ;
                }
                var panoEffect_attr = (panoEffect == 'none' || panoEffect == '') ? '' : ' mode=' + panoEffect;
                var panoAlign_attr = (panoAlign == 'none' || panoAlign == '') ? '' : ' float=' + panoAlign;
                var panoCaptiontype_attr = (panoCaptiontype == 'none' || panoCaptiontype == '') ? '' : ' caption=' + panoCaptiontype;
                var panoMapW_attr = (panoMapW == '' ) ? '' : ' mapw=' + panoMapW;
                var panoMapH_attr = (panoMapH == '' ) ? '' : ' maph=' + panoMapH;
                var panoMaptype_attr = (panoMaptype == '' ) ? '' : ' maptype=' + panoMaptype;
                var panoLinks_attr = '';
                switch(panoLinks) {
                    case 'none':
                        panoLinks_attr = '';
                    break;
                    case 'all':
                        panoLinks_attr = ' links=all';
                    break;
                    case 'select':
                        if(panoLinksChoice.length >0) {
                            panoLinks_attr = ' links='+panoLinksChoice.join("-");
                        } else {
                            panoLinks_attr = '';
                        }
                    break;
                }
                var panoMainLink_attr = (panoMainLink == '' ) ? '' : ' mainlink=' + panoMainLink;
                panoMainLink_attr = (panoLinks_attr == '') ? '' : panoMainLink_attr;
                
		if (panolist.length != 0 ) {
                    
                    tagtext = "[" + panoShortcodetype + " id=" + panolistformat + panoWidth_attr + panoHeight_attr + panoAlign_attr + panoCaptiontype_attr;
                    
                    switch(panoShortcodetype) {
                        case 'panoramic':
                            tagtext += '';
                        break;
                        case 'singlepicwithlinks':
                           tagtext += panoEffect_attr + panoLinks_attr + panoMainLink_attr;
                        break;
                        case 'singlemap':
                            tagtext += panoLinks_attr + panoMainLink_attr + panoMapW_attr + panoMapH_attr + panoMapZ_attr + panoMaptype_attr;
                        break;
                        case 'singlepicwithmap':
                            tagtext +=  panoEffect_attr + panoMapW_attr + panoMapH_attr + panoMapZ_attr + panoMaptype_attr;
                        break;
                        case 'panoramicwithmap':
                            tagtext +=  panoMapW_attr + panoMapH_attr + panoMapZ_attr + panoMaptype_attr;
                        break;
                        
                    }
                    tagtext += "]";
                    panoCallback.addClass('hidden').removeClass('error').removeClass('message');
                   /*
                    panoCallback.removeClass('hidden').addClass('error').addClass('message');
                    panoCallback.text(tagtext);
                    return;
                    */
                } else {
                    //panoCallback.text(NGGPANOTinyoptions.pleaseSelectText);
                    panoCallback.removeClass('hidden').addClass('error').addClass('message');
                    panoCallback.text(tinyMCEPopup.getLang('NGGPano.pleaseSelectText'));
                    return;
                    //tinyMCEPopup.close();
		}
	}
	
	if(window.tinyMCE && tagtext != '') {
        window.tinyMCE.execInstanceCommand(window.tinyMCE.activeEditor.id, 'mceInsertContent', false, tagtext);
		//Peforms a clean up of the current editor HTML. 
		//tinyMCEPopup.editor.execCommand('mceCleanup');
		//Repaints the editor. Sometimes the browser has graphic glitches. 
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}
