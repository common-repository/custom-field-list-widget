<?php
if ( TRUE == isset($_POST['abspath']) AND FALSE === stristr($_POST['abspath'], '://') AND FALSE === stristr($_POST['abspath'], '%3A%2F%2F') AND TRUE == is_file($_POST['abspath'] . 'wp-config.php') ) {
	require_once($_POST['abspath'] . 'wp-config.php');
	if ( FALSE == function_exists('wp_verify_nonce') or FALSE == wp_verify_nonce($_POST['_ajax_nonce'], 'customfieldlist_dbaction_security') ) {
		die(__('Security Check failed!','custom-field-list-widget')); 
	}
	if ( TRUE == function_exists('is_user_logged_in') and TRUE == is_user_logged_in() ) {
		if ( isset($_POST['widget_number']) AND FALSE === empty($_POST['widget_number'])) {
			customfieldlist_save_data(intval($_POST['widget_number']));
		} else {
			die(__('The widget number was not transmitted.','custom-field-list-widget'));
		}
	} else {
		die(__('You have to be logged in for this action.','custom-field-list-widget'));
	}
} else {
	die('Please do not load this page directly.');
}

function customfieldlist_save_data($widget_number) {
	// Data should be stored as an array:  array( number => data for that instance of the widget, ... )
	$opt = get_option('widget_custom_field_list');
	if ( !is_array($opt) ) {
		$opt = array();
	}
	foreach ($_POST['id'] as $key => $id) {
		$opt[$widget_number]['individual_href']['id'][$key] = strip_tags(trim(strval($id)));
	}
	foreach ($_POST['link'] as $key => $link) {
		$opt[$widget_number]['individual_href']['link'][$key] = clean_url(rawurldecode($link), array('http', 'https', 'ftp'), 'db');
	}
	foreach ($_POST['descr'] as $key => $descr) {
		$opt[$widget_number]['individual_href']['descr'][$key] = strip_tags(trim($descr));
	}
	$opt[$widget_number]['individual_href']['thecustomfieldname'] = strip_tags(trim($_POST['thecustomfieldname']));
	$result = update_option('widget_custom_field_list', $opt);
}
?>