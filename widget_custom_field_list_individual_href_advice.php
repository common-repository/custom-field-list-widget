<?php 
if ( TRUE == isset($_GET['abspath']) AND FALSE === stristr($_GET['abspath'], '://') AND FALSE === stristr($_GET['abspath'], '%3A%2F%2F') AND TRUE == is_file($_GET['abspath'] . 'wp-config.php')  ) {
	require_once( $_GET['abspath'] . 'wp-config.php');
	if ( FALSE == function_exists('wp_verify_nonce') or FALSE == wp_verify_nonce($_GET['_wpnonce'], 'customfieldlist_individual_href_security') ) {
		die ('<p class="error" style="vertical-align:middle; padding:1em; font-weight:normal;">'.__('Security Check failed!','custom-field-list-widget').'</p>'); 
	}
	if ( TRUE == function_exists('is_user_logged_in') and TRUE == is_user_logged_in() ) {
		customfieldlist_print_advice(intval($_GET['advicemsg']));
	} else {
		die('<p class="error" style="vertical-align:middle; padding:1em; font-weight:normal;">'.__('You have to be logged in for this action.','custom-field-list-widget').'</p>');
	}
} else {
	die( ' Please do not load this page directly.' );
}

function customfieldlist_print_advice($advicemsg) {
	echo '<div class="error" style="vertical-align:middle; padding:1em; font-weight:normal;">';
	switch ($advicemsg) {
		case 2 :
			echo sprintf(__('Please, choose the list type "%1$s" to able to set the link reference for the custom field name values.' ,'custom-field-list-widget'), __('a list of all values with manually set links','custom-field-list-widget'));
		break;
		case 3 :
		default:
			_e('Please, save the custom field names first.','custom-field-list-widget');
		break;
	}
	echo '</div>';
}
?>