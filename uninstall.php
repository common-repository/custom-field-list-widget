<?php
if ( defined( 'WP_UNINSTALL_PLUGIN' ) AND function_exists('is_user_logged_in') AND is_user_logged_in() ) {
	delete_option('widget_custom_field_list');
	delete_option('widget_custom_field_list_general_options');
} else {
	die ('Don not open this page directly!');
}
?>
