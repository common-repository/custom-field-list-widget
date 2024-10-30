<?php 
header('Content-type: application/x-javascript');
?>
// if jQuery is available and working then deflate the sublists and change the switch sign after the page is loaded 
jQuery(window).load(function(){
	var signs = customfieldlist_the_collapse_sign();
	jQuery(".customfieldplus").text(signs['plus']);
	jQuery(".customfieldsublist").hide();
	var widget_numbers = jQuery("[name='customfieldlist_widget_id']");
	for (var j=0; j < widget_numbers.length; j++) {
		if ('yes' == jQuery("#customfieldlistpartlist_" + String(widget_numbers[j].value)).val()) {
			var li_els=jQuery("#customfieldlistelements_" + String(widget_numbers[j].value)).val();
			for ( i=1; i <= li_els; i++ ) {
				jQuery(".customfieldlistelements_" + String(widget_numbers[j].value) + "_" + String(i)).hide();
			}
			jQuery("#customfieldlistpages_" + String(widget_numbers[j].value)).show();
		}
	}
});

// inflate or deflate the sublists
jQuery(document).ready(function(){
	var signs = customfieldlist_the_collapse_sign();
	var speed = customfieldlist_effect_speed();

	jQuery(".customfieldplus").click(function() {
		var field = jQuery(this).parent().children(".customfieldplus");
		if (signs['plus'] == field.text()) {
			field.text(signs['minus']);
			jQuery(this).parent().children(".customfieldsublist").show(speed);
		} else {
			field.text(signs['plus']);
			jQuery(this).parent().children(".customfieldsublist").hide(speed);
		}
	});
	
	jQuery(".customfieldtitle").click(function() {
		var field = jQuery(this).parent().children(".customfieldplus");
		if (signs['plus'] == field.text()) {
			field.text(signs['minus']);
			jQuery(this).parent().children(".customfieldsublist").show(speed);
		} else {
			field.text(signs['plus']);
			jQuery(this).parent().children(".customfieldsublist").hide(speed);
		}
	});
});

// switch between the list parts
function show_this_customfieldlistelements( list, lists, number ) {
	var speed = customfieldlist_effect_speed();
	for ( i=0; i <= lists; i++ ) {
		if ( i == Number(list) ) {
			jQuery( ".customfieldlistelements_" + String(number) + "_" + String(i)).show(speed);
			// mark the page number with a different appearence
			if ( false == jQuery( "[id='customfieldlistpart_" + String(number) + "_" + String(i) + "']" ).hasClass("customfieldlist_selectedpart") ) {
				jQuery( "[id='customfieldlistpart_" + String(number) + "_" + String(i) + "']" ).addClass("customfieldlist_selectedpart");
			}
		} else {
			jQuery( ".customfieldlistelements_" + String(number) + "_" + String(i) ).hide(speed);
			// remove the special style class for the selected list part
			if ( true == jQuery( "[id='customfieldlistpart_" + String(number) + "_" + String(i) + "']" ).hasClass("customfieldlist_selectedpart") ) {
				jQuery( "[id='customfieldlistpart_" + String(number) + "_" + String(i) + "']" ).removeClass("customfieldlist_selectedpart");
			}
		}
	}
}

// open the selected post - for the drop down menu option
function customfieldlistwidget_go_to_target( menu_id, selectedindex ) {
	var target_url = document.getElementById(String(menu_id)).options[Number(selectedindex)].value;
	var trimed_target_url = target_url.replace(/\s/g, '');
	if (0 < trimed_target_url.length && 'nothing' != target_url) {
		top.location.href = target_url;
	}
}