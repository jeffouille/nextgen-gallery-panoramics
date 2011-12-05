=== Plugin Name ===
Contributors: Jefouille
Tags: nextgen-gallery, nextgen, gallery, panoramic, pano, krpano, 360, virtual
Requires at least: 2.9.1
Tested up to: 3.2.1
Stable tag: 1.0

This plugin adds the ability to create panoramics viewer using krpano (www.krpano.com) from  NextGen Images

== Description ==

== Installation ==

1. Unzip the plugin to your `wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to 'Manage Gallery' in NextGEN, and select a gallery to see the new options

== Changelog ==

= 1.0 =
* Initial Release

== TODO ==
DONE extract gps for all images in a gallery
DONE extract gps for one image ajax
gps data picker for one image
region for gallery

DONE remove database record when remove a gallery

NOT USEABLE TO REMOVE add option to automaticly create pano when image is added

add admin page to edit skin file and config file

add krpanoplugin configuration in plugin options and in gallery option

check memory limit (gd vs imagemagik)

create preview of image 2000px width (with watermark ?) - Make 2000x1000 thumbnail to replace the original photo

add possibility to add manually a pano (memory limit)

publish a pano (with featured image)

DONE remove all pano file when remove an image

config file (skin.xml and krpanotool.config) edit panel (like css style edit)

DONE directory and option to place custom krpano tool and viewer

xml generator for plugin

verifiy security with use of check_admin_referer( 'myplugin-update' );
//--> check this url : http://planetozh.com/blog/2009/09/top-10-most-common-coding-mistakes-in-wordpress-plugins/

internationalize error messages ogf nggpanoPano.class.php

DONE Admin options add folder settings

DONE with live method.... make links added with ajax be correctly clickable

== When ngg is update ==

in /admin/manage.php
line 84 after
    if($delete_pic)
            nggGallery::show_message( __('Picture','nggallery').' \''.$this->pid.'\' '.__('deleted successfully','nggallery') );
add	
    //Jeff Modif
    //hook for other plugin to update the fields
    do_action('ngg_delete_picture', $this->pid);


line 178
replace
if($deleted)
        nggGallery::show_message(__('Gallery deleted successfully ', 'nggallery'));
with
if($deleted) {
        nggGallery::show_message(__('Gallery deleted successfully ', 'nggallery'));

        //Jeff Modif
        //hook for other plugin to update the fields
        do_action('ngg_delete_gallery', $this->pid);
}

memory limit error
    @ini_set('memory_limit', '128M');

in gd.thumbnail.inc.php 