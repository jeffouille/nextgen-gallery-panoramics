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
DONE Put getSkinXML() and getPanoConfig() out of PanoClass but in lib/function.php and rename it
DONE Change BASEDIR when build scene
DONE save post_id in ngpano_picture
DONE shortcode for gallery


Redone singlemap to have several marker in map

map default options in admin (mapw, maph, map zoom, map_type, picto marker


shortcode generator in wysiwyg

add license file config in admin


add krpanoplugin configuration in plugin options and in gallery option

check memory limit (gd vs imagemagik)

Combox to choose article in krpano form

(?<=_xxx_).*











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

in /admin/album.php
line 515 and line 539 remove esc_url() function
<?php //Jeff Remove esc_url() ?> 
$preview_image = ( !is_null($image->thumbURL) )  ? '<div class="inlinepicture"><img src="' . esc_url( $image->thumbURL ). '" /></div>' : '';
$preview_image = isset($image->thumbURL) ? '<div class="inlinepicture"><img src="' . esc_url( $image->thumbURL ) . '" /></div>' : '';


in /admin/tinymce/window.php
line 16 :
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.core.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.widget.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui.position.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.ui.autocomplete.min.js"></script>
<!--	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui/jquery.ui.core.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui/jquery.ui.widget.min.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui/jquery.ui.position.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/ui/jquery.ui.autocomplete.min.js"></script>-->



memory limit error
    @ini_set('memory_limit', '128M');

in gd.thumbnail.inc.php 