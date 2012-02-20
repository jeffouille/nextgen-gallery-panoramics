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
DONE gps data picker for one image
DONE add gps marker on image
DONE add shortcode simplepic with map
DONE remove database record when remove a gallery
DONE create preview of image 2000px width (with watermark ?) - Make 2000x1000 thumbnail to replace the original photo
DONE remove all pano file when remove an image
DONE directory and option to place custom krpano tool and viewer
DONE Admin options add folder settings
DONE with live method.... make links added with ajax be correctly clickable
DONE add possibility to add manually a pano (memory limit)
DONE publish a pano from admin page
DONE add admin page to edit skin file and config file config file (skin.xml and krpanotool.config) edit panel (like css style edit)
DONE region for gallery
DONE publish a pano with featured image
DONE checkbox exif date in article
DONE checkbox featured image in article published
DONE category in publish pano

shortcode for photo gallery

map default options in admin (mapw, maph, map zoom, map_type, picto marker


shortcode generator in wysiwyg

add license file config in admin


add krpanoplugin configuration in plugin options and in gallery option

check memory limit (gd vs imagemagik)

save post_id in ngpano_picture

delete post_id when publish article







xml generator for plugin

verifiy security with use of check_admin_referer( 'myplugin-update' );
//--> check this url : http://planetozh.com/blog/2009/09/top-10-most-common-coding-mistakes-in-wordpress-plugins/

internationalize error messages ogf nggpanoPano.class.php

== When ngg is update ==

in /admin/manage-image.php
line 465 remove esc_url() function
<?php //Jeff Remove esc_url() ?> 
<a href="<?php echo  ( add_query_arg('i', mt_rand(), $picture->imageURL) ); ?>" class="shutter" title="<?php echo $picture->filename ?>">
            <img class="thumb" src="<?php echo  ( add_query_arg('i', mt_rand(), $picture->thumbURL) ); ?>" id="thumb<?php echo $pid ?>" />
    </a>

memory limit error
    @ini_set('memory_limit', '128M');

in gd.thumbnail.inc.php 