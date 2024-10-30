<?php
if ( TRUE == isset($_GET['abspath']) AND FALSE === stristr($_GET['abspath'], '://') AND FALSE === stristr($_GET['abspath'], '%3A%2F%2F') AND TRUE == is_file($_GET['abspath'] . 'wp-config.php') ) {
	require_once( $_GET['abspath'] . 'wp-config.php');
	if ( FALSE == function_exists('wp_verify_nonce') OR FALSE == wp_verify_nonce($_GET['_wpnonce'], 'customfieldlist_long_selectbox_security') ) {
		die ('<p class="error" style="vertical-align:middle; padding:1em; font-weight:normal;">'.__('Security Check failed!','custom-field-list-widget').'</p>'); 
	}
	customfieldlist_print_long_selectbox($_GET['selectboxid']);
} else {
	die ('Please do not load this page directly.');
}

function customfieldlist_print_long_selectbox($selectboxid) {
	global $wp_version;
	echo '<div style="vertical-align:middle; padding:1em; font-weight:normal;" id="customfieldlist_long_selectbox_container">';
	?>
	<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function(){
		jQuery('#<?php echo $selectboxid; ?>').children().clone().appendTo(jQuery('#customfieldlist_long_selectbox'));
	});
	jQuery('#<?php echo $selectboxid; ?>').attr('disabled', 'disabled');
	jQuery('#<?php echo $selectboxid; ?>').hide();
	var customfieldlist_orig_z_index_window = jQuery('#TB_window').css('z-index');
	var customfieldlist_orig_z_index_overlay = jQuery('#TB_overlay').css('z-index');
	jQuery('#TB_window').css('z-index', '10005');
	jQuery('#TB_overlay').css('z-index', '10000');
	<?php
	if (version_compare($wp_version, '3.3', '<')) { 
		echo "jQuery('#TB_window').bind( 'unload', function() {\n";
	} else {
		echo "jQuery('#TB_window').bind( 'tb_unload', function() {\n";
	}
	?>
		jQuery('#TB_window').css('z-index', customfieldlist_orig_z_index_window);
		jQuery('#TB_overlay').css('z-index', customfieldlist_orig_z_index_overlay);
		jQuery('#<?php echo $selectboxid; ?>').removeAttr('disabled');
		jQuery('#<?php echo $selectboxid; ?>').show();
	});
	//]]>
	</script>
	<?php
	echo '<select id="customfieldlist_long_selectbox" class="customfieldlist_selectbox" onchange="customfieldlistwidget_go_to_target(this.id, this.selectedIndex);">';
	echo '</select>';
	echo '</div>';
	if (version_compare($wp_version, '2.9', '<')) {
		echo '[ <a href="javascript:void(null);" onclick="tb_remove();">'.__('Close').'</a> ]';
	}
	?>
	<script type="text/javascript">
	//<![CDATA[
	jQuery('#customfieldlist_long_selectbox').focus();
	//]]>
	</script>
	<?php
}
?>