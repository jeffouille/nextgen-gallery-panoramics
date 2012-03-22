<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

function nggpano_admin_style()  {

global $ngg;
global $nggpano;

if ( $skin_xml_exists = file_exists (TEMPLATEPATH . "/nggpano/krpano/nggpano-skin.xml") ) {

	$real_file = TEMPLATEPATH . "/nggpano/krpano/nggpano-skin.xml";
	$file_show = 'nggpano-skin.xml ' . __('(From the theme folder)','nggpano') . ' (/nggpano/krpano/)';
	
} else {

	if (isset($_POST['defaultSkinFile'])) {
		check_admin_referer('nggpano_template');
		$act_skinfile = $_POST['defaultSkinFile']; 
		//defaultSkinFile
		//if ( isset( $_POST['activate'] ) ) {
			// save option now
			//$nggpano->options['activateCSS'] = $_POST['activateCSS']; 
			$nggpano->options['defaultSkinFile'] = $act_skinfile;
			update_option('nggpano_options', $nggpano->options);
			nggPanoramic::show_message(__('Update Successfully','nggpano'));
		//}
	} else {
		// get the options
		if (isset($_POST['file']))
			$act_skinfile = $_POST['file'];
		else
			$act_skinfile = $nggpano->options['defaultSkinFile'];
	}
	
	// set the path
        $real_file = ABSPATH . $nggpano->options['skinFolder'] . $act_skinfile;
}

if (isset($_POST['updateskinfile'])) {
	
	check_admin_referer('nggpano_template');

	if ( !current_user_can('edit_themes') )
	wp_die('<p>'.__('You do not have sufficient permissions to edit templates for this blog.').'</p>');

	$newcontent = stripslashes($_POST['newcontent']);

	if (is_writeable($real_file)) {
		$f = fopen($real_file, 'w+');
		fwrite($f, $newcontent);

		fclose($f);
                nggPanoramic::show_message(__('Skin file successfully updated','nggpano'));
	}
}

// get the content of the file
//TODO: BUG : Read failed after write a file, maybe a Cache problem
$error = ( !is_file($real_file) );

if (!$error && filesize($real_file) > 0) {
	$f = fopen($real_file, 'r');
	$content = fread($f, filesize($real_file));
	$content = htmlspecialchars($content); 
}

?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('#colorSelector').ColorPicker({
			color: '#0000ff',
			onShow: function (colpkr) {
				jQuery(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				jQuery(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				jQuery('#colorSelector div').css('backgroundColor', '#' + hex);
			}
		});
	});
</script>
<div class="wrap">

	<div class="bordertitle">
        <?php screen_icon( 'nextgen-gallery-panoramics' ); ?>
		<h2><?php _e('Skin Editor','nggpano') ?></h2>
		<?php if (!$skin_xml_exists) : ?>
		<form id="themeselector" name="cssfiles" method="post">
		<?php wp_nonce_field('nggpano_template') ?>
		<strong><?php _e('Activate and use skin template:','nggpano') ?></strong>
<!--		<input type="checkbox" name="activateCSS" value="1" <?php checked('1', $nggpano->options['activateCSS']); ?> /> -->
<!--			<select name="css" id="theme" style="margin: 0pt; padding: 0pt;" onchange="this.form.submit();">-->
                            <select name="defaultSkinFile" id="defaultSkinFile" style="margin: 0pt; padding: 0pt;" onchange="this.form.submit();">
			                                    <?php
                                    $act_templatefile = $nggpano->options['defaultSkinFile'];
                                    $templatelist = nggpano_get_viewerskinfiles();
                                    foreach ($templatelist as $key =>$a_templatefile) {
                                            $template_name = $a_templatefile['Name'];
                                            if ($key == $act_templatefile) {
                                                    $file_show = $key;
                                                    $selected = " selected='selected'";
                                                    $act_template_description = $a_templatefile['Description'];
                                                    $act_template_author = $a_templatefile['Author'];
                                                    $act_template_version = $a_templatefile['Version'];
                                            }
                                            else $selected = '';
                                            $template_name = esc_attr($template_name);
                                            echo "\n\t<option value=\"$key\" $selected>$template_name</option>";
                                    }
                                    ?>

			</select>
			<input class="button" type="submit" name="activate" value="<?php _e('Activate','nggallery') ?> &raquo;" class="button" />
		</form>
		<?php endif; ?>
	</div>
	<br style="clear: both;"/>
	
<?php if (!is_multisite() || wpmu_site_admin() ) { ?>
	<div class="tablenav"> 
	  <?php
		if ( is_writeable($real_file) ) {
			echo '<big>' . sprintf(__('Editing <strong>%s</strong>','nggallery'), $file_show) . '</big>';
		} else {
			echo '<big>' . sprintf(__('Browsing <strong>%s</strong>','nggallery'), $file_show) . '</big>';
		}
		?>
	</div>
        <br style="clear: both;"/>
        <div class="tablenav">
            <?php echo '<big>' . __('<strong>IMPORTANT</strong> : Panoramic Plugin use <scene> nodes of krpano','nggpano') . '</big><br/>'; ?>
            <?php echo '<big>' . __('When panoramic is loaded; the initialize action is launched, so your template must include it with scene name in argument','nggpano') . '</big><br/>'; ?>
            <?php echo '<big>' . __('When a scene is loaded; the changescene action is launched, so your template must include it with scene name in argument','nggpano') . '</big>'; ?>
	</div> 
	<br style="clear: both;"/>
	
	<div id="templateside">
	<?php if (!$skin_xml_exists) : ?>
		<ul>
			<li><strong><?php _e('Author','nggallery') ?> :</strong> <?php echo $act_template_author ?></li>
			<li><strong><?php _e('Version','nggallery') ?> :</strong> <?php echo $act_template_version ?></li>
			<li><strong><?php _e('Description','nggallery') ?> :<br /></strong> <?php echo $act_template_description ?></li>
		</ul>
		<p><?php _e('Tip : Copy your skin file (nggpano-skin.xml) to your theme folder (THEMEDIR/nggpano/krpano/), so it will be not lost during a upgrade','nggpano') ?></p>
	<?php else: ?>
		<p><?php _e('Your theme contain a NextGEN Gallery Panoramic Template (THEMEDIR/nggpano/krpano/nggpano-skin.xml), this file will be used','nggpano') ?></p>
	<?php endif; ?>
    	<p><?php _e('Tip No. 2: Use the color picker below to help you find the right color scheme for your skin!','nggpano') ?></p>
        <p><?php _e('Tip No. 3: Use these variables : <ul><li>%PLUGINDIR% = directory with krpano plugins</li><li>%SKINDIR% = directory with krpano skin</li></ul>','nggpano') ?></p>
        <p><?php echo sprintf(__('&#37;PLUGINDIR&#37; will be replaced with : <strong>%s</strong>','nggpano'), $nggpano->options['pluginFolder']) ?></p>
        <p><?php echo sprintf(__('&#37;SKINDIR&#37; will be replaced with : <strong>%s</strong>','nggpano'), $nggpano->options['skinFolder']) ?></p> 
        <div id="colorSelector">
        	<div></div>
        </div>
	</div>
		<?php
		if (!$error) {
		?>
		<form name="template" id="template" method="post">
			 <?php wp_nonce_field('nggpano_template') ?>
			 <div><textarea cols="70" rows="25" name="newcontent" id="newcontent" tabindex="1"  class="codepress css"><?php echo $content ?></textarea>
			 <input type="hidden" name="updateskinfile" value="updateskinfile" />
			 <input type="hidden" name="file" value="<?php echo $file_show ?>" />
			 </div>
	<?php if ( is_writeable($real_file) ) : ?>
		<p class="submit">
			<input class="button-primary action" type="submit" name="submit" value="<?php _e('Update File','nggallery') ?>" tabindex="2" />
		</p>
	<?php else : ?>
	<p><em><?php _e('If this file were writable you could edit it.','nggallery'); ?></em></p>
	<?php endif; ?>
		</form>
		<?php
		} else {
			echo '<div class="error"><p>' . __('Oops, no such file exists! Double check the name and try again, merci.','nggallery') . '</p></div>';
		}
		?>
	<div class="clear"> &nbsp; </div>
</div> <!-- wrap-->
	
<?php
	}
	
} // END nggpano_admin_style()

?>