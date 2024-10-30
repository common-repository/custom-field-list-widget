<?php
/*
Plugin Name: Custom Field List Widget
Plugin URI: http://undeuxoutrois.de/custom_field_list_widget.shtml
Description: This plugin creates sidebar widgets with lists of the values of a custom field (name). The listed values can be (hyper-)linked in different ways.
Author: Tim Berger
Version: 1.5.1
Author URI: http://undeuxoutrois.de/
Min WP Version: 2.8
Max WP Version: 5.8
License: GNU General Public License
Text Domain: custom-field-list-widget
Domain Path: /languages

Requirements:
	- min. WP 2.7 
	- a widgets supportting theme
	
Localization:
	Spanish - Juan Jose Bescos of IBIDEM GROUP (https://www.ibidemgroup.com)
	Bulgarian - Peter Toushkov
	Hindi - Kakesh Kumar (http://kakesh.com/)
	Danish (frontend only) - Peter Kirring (http://www.fotoblogger.dk/)
	German - Tim Berger
	English (default) - Tim Berger
	
	Russian (complete until v0.9.4.1) - Michael Comfi (http://www.comfi.com/)
	Uzbek (complete until v0.9.4.1) - Alisher Safarov (http://www.comfi.com/) 

For detailed information about the usage of this plugin, please read the readme.txt.	

Copyright 2021  Tim Berger  (email : be.tim.info@web.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Parts of this plugin are based on the multiple-widgets-pattern example from the file /wp-includes/widgets.php of WP 2.7.1

*/
function my_plugin_load_plugin_textdomain() {
    load_plugin_textdomain( 'my-plugin', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'my_plugin_load_plugin_textdomain' );

add_action('plugins_loaded', 'customfieldlist_init');
function customfieldlist_init() {
	global $wp_version;
	// #######################################################################################
	// max. number of hierarchy steps resp. number of 
	if ( ! defined( 'CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL' ) ) { define( 'CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL', 5 ); }
	// #######################################################################################

	// Pre-2.6 compatibility 
	if ( ! defined( 'WP_CONTENT_URL' ) ) { define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
	if ( ! defined( 'WP_CONTENT_DIR' ) ) { define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
	if ( ! defined( 'WP_PLUGIN_URL' ) ) { define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
	if ( ! defined( 'WP_PLUGIN_DIR' ) ) { define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }
	if ( ! defined( 'CUSTOM_FIELD_LIST_WIDGET_DIR' ) ) { define( 'CUSTOM_FIELD_LIST_WIDGET_DIR', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)) ); }
	if ( ! defined( 'CUSTOM_FIELD_LIST_WIDGET_URL' ) ) { define( 'CUSTOM_FIELD_LIST_WIDGET_URL', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)) ); }

	// load the translation file
	if ( function_exists('load_plugin_textdomain') ) {
		if ( version_compare( $wp_version, '2.7', '>=' ) ) {
			load_plugin_textdomain( 'custom-field-list-widget', FALSE, str_replace(WP_PLUGIN_DIR.'/', '', CUSTOM_FIELD_LIST_WIDGET_DIR.'/languages/') );
		} else {
			load_plugin_textdomain( 'custom-field-list-widget', FALSE, CUSTOM_FIELD_LIST_WIDGET_DIR.'/languages/' );
		}
	}
}

// on plugin deactivation
//~ register_deactivation_hook( (__FILE__), 'customfieldlist_on_deactivation' );
//~ function customfieldlist_on_deactivation() {
	//~ delete_option('widget_custom_field_list');
//~ }

// This function prints specialy the lists of one widget
function customfieldlist_print_widget_content($n, $number, $partlength, $hierarchymaxlevel, $list_format='ul_list', $list_style='standard', $show_number_of_subelements=FALSE, $signs, $charset='UTF-8', $group_by_firstchar='no', $strlimiter = Array('limittype' => 'end', 'maxlength' => 35, 'abbrev' => FALSE), $i=0, $j=0, $k=0) {
	if ('dropdownmenu' == $list_format AND ('each_element_with_sub_element' == $list_style)) {
		$internal_list_style = 'standard';
	} else {
		$internal_list_style = $list_style;
	}
	if ( $i < ($hierarchymaxlevel-1) ) {
		$i++;
		switch ($internal_list_style) {
			case 'individual_href' :
				switch ($list_format) {
					case 'dropdownmenu' :
						foreach ($n as $key => $value) {
							if ( TRUE === is_array($value) ) {
								echo "\t".'<optgroup class="customfieldoptgroup" label="'.esc_attr(customfieldlist_strlimiter($key, $strlimiter['limittype'], $strlimiter['maxlength'], $strlimiter['abbrev'])).'">'."\n";
								if ( 'yes' == $group_by_firstchar ) {//AND 0 < count($value) 
									customfieldlist_print_widget_content($value, $number, $partlength, $hierarchymaxlevel, $list_format, $list_style, $show_number_of_subelements, $signs, $charset, $group_by_firstchar, $strlimiter, $i, $j, $k);
								} else {
									if ('' != $value[0]['post_title']) {
										// there will only be a link in the dropdown list if the post_title or the description is not empty
										// post_guid is here not the GUID value. It is in every case an URL
										echo "\t".'<option class="customfieldoptionelements_'.$number.'_'.$k.' customfieldlist_opt_link" value="'.$value[0]['post_guid'].'">'.customfieldlist_strlimiter($value[0]['post_title'], $strlimiter['limittype'], $strlimiter['maxlength'], $strlimiter['abbrev'])."</option>\n";
									}
								}
								echo "\t</optgroup>\n";
							} else {
								echo "\t".'<option class="customfieldoptionelements_'.$number.'_'.$k.'">(3 select)'.__('Internal Plugin Error: value is no array', 'custom-field-list-widget')."</option>\n";
							}
						}
					break;
					case 'ul_list' :
					default :
						foreach ($n as $key => $value) {
							if ( TRUE === is_array($value) ) {
								if ( FALSE === isset($value[0]['post_guid']) OR 1 < count($value) ) {
									if ( TRUE === $show_number_of_subelements ) {
										$nr_of_subelement_str = ' ('.count($value).')';
									} else {
										$nr_of_subelement_str = '';
									}
									echo "\t<li class=".'"customfieldlistelements_'.$number.'_'.$k.'"'.">\n\t".'<span class="customfieldtitle">'.$key.'</span>'.$nr_of_subelement_str.' <span class="customfieldplus">'.$signs['minus'].'</span>'."<br />\n\t";
									echo '<ul class="customfieldsublist">'."\n";
									customfieldlist_print_widget_content($value, $number, $partlength, $hierarchymaxlevel, $list_format, $list_style, $show_number_of_subelements, $signs, $charset, $group_by_firstchar, $strlimiter, $i, $j, $k);
									echo "\t</ul>\n";
									echo "\t</li>\n";
								} else {
									if ( FALSE === empty($value[0]['post_guid']) ) {
										// post_guid is here not the GUID value. It is in every case an URL
										echo "\t".'<li class="customfieldlistelements_'.$number.'_'.$k.'"><a href="'.$value[0]['post_guid'].'" title="'.esc_attr($value[0]['post_title'].' - '.$value[0]['post_date']).'">'.$key."</a></li>\n";
									} else {
										echo "\t".'<li class="customfieldlistelements_'.$number.'_'.$k.'">'.$key."</li>\n";
									}
								}
								if ( $i == 1 ) { 
									$j++;
								}
								if ( $i == 1 AND  0 === ($j % $partlength)  ) {
									$k++;
								}
							} else {
								echo "\t".'<li class="customfieldlistelements_'.$number.'_'.$k.'">(3)'.__('Internal Plugin Error: value is no array', 'custom-field-list-widget')."</li>\n";
							}
						}
					break;
				}
			break;
			case 'each_element_with_sub_element' :
				foreach ($n as $key => $value) {
					if ( TRUE === is_array($value) ) { 
						if ( TRUE === $show_number_of_subelements AND 0 < count($value) ) {
							$nr_of_subelement_str = ' ('.count($value).')';
						} else {
							$nr_of_subelement_str = '';
						}
						echo "\t<li class=".'"customfieldlistelements_'.$number.'_'.$k.'"'.">\n\t".'<span class="customfieldtitle">'.$key.'</span>'.$nr_of_subelement_str.' <span class="customfieldplus">'.$signs['minus'].'</span>'."<br />\n\t";
						echo '<ul class="customfieldsublist">'."\n";
						customfieldlist_print_widget_content($value, $number, $partlength, $hierarchymaxlevel, $list_format, $list_style, $show_number_of_subelements, $signs, $charset, $group_by_firstchar, $strlimiter, $i, $j, $k);
						echo "\t</ul>\n";
						echo "\t</li>\n";
						if ( $i==1 ) { 
							$j++;
						}
						if ( $i==1 AND  0 === ($j % $partlength)  ) {
							$k++;
						}
					} else {
						echo "\t".'<li class="customfieldlistelements_'.$number.'_'.$k.'">(1)'.__('Internal Plugin Error: value is no array', 'custom-field-list-widget')."</li>\n";
					}
				}
			break;
			case 'standard' :
			default :
				switch ($list_format) {
					case 'dropdownmenu' :
						if ( 'each_element_with_sub_element' == $list_style ) {
							foreach ($n as $key => $value) {
								if ( TRUE === is_array($value) ) { 
									echo "\t".'<optgroup class="customfieldoptgroup" label="'.esc_attr(customfieldlist_strlimiter($key, $strlimiter['limittype'], $strlimiter['maxlength'], $strlimiter['abbrev'])).'">'."\n";
									customfieldlist_print_widget_content($value, $number, $partlength, $hierarchymaxlevel, $list_format, $list_style, $show_number_of_subelements, $signs, $charset, $group_by_firstchar, $strlimiter, $i, $j, $k);
									echo "\t</optgroup>\n";
								} else {
									echo "\t".'<option class="customfieldoptionelements_'.$number.'_'.$k.'">(2 select)'.__('Internal Plugin Error: value is no array', 'custom-field-list-widget')."</option>\n";
								}
							}
						} else {
							foreach ($n as $key => $value) {
								if ( TRUE === is_array($value) ) { 
									if ( FALSE === isset($value[0]['post_id']) OR 1 < count($value) ) {
										echo "\t".'<optgroup class="customfieldoptgroup" label="'.esc_attr(customfieldlist_strlimiter($key, $strlimiter['limittype'], $strlimiter['maxlength'], $strlimiter['abbrev'])).'">'."\n";
										customfieldlist_print_widget_content($value, $number, $partlength, $hierarchymaxlevel, $list_format, $list_style, $show_number_of_subelements, $signs, $charset, $group_by_firstchar, $strlimiter, $i, $j, $k);
										echo "\t</optgroup>\n";
									} else {
										echo "\t".'<option class="customfieldoptionelements_'.$number.'_'.$k.' customfieldlist_opt_link" value="'.get_permalink($value[0]['post_id']).'">'.customfieldlist_strlimiter($value[0]['post_title'], $strlimiter['limittype'], $strlimiter['maxlength'], $strlimiter['abbrev'])."</option>\n";
									}
								} else {
									echo "\t".'<option class="customfieldoptionelements_'.$number.'_'.$k.'">(2 select)'.__('Internal Plugin Error: value is no array', 'custom-field-list-widget')."</option>\n";
								}
							}
						}
					break;
					case 'ul_list' :
					default :
						foreach ($n as $key => $value) {
							if ( TRUE === is_array($value) ) {
								if ( FALSE === isset($value[0]['post_guid']) OR 1 < count($value) ) {
									if ( TRUE === $show_number_of_subelements ) {
										$nr_of_subelement_str = ' ('.count($value).')';
									} else {
										$nr_of_subelement_str = '';
									}
									echo "\t<li class=".'"customfieldlistelements_'.$number.'_'.$k.'"'.">\n\t".'<span class="customfieldtitle">'.$key.'</span>'.$nr_of_subelement_str.' <span class="customfieldplus">'.$signs['minus'].'</span>'."<br />\n\t";
									echo '<ul class="customfieldsublist">'."\n";
									customfieldlist_print_widget_content($value, $number, $partlength, $hierarchymaxlevel, $list_format, $list_style, $show_number_of_subelements, $signs, $charset, $group_by_firstchar, $strlimiter, $i, $j, $k);
									echo "\t</ul>\n";
									echo "\t</li>\n";
								} else {
									echo "\t".'<li class="customfieldlistelements_'.$number.'_'.$k.'"><a href="'.get_permalink($value[0]['post_id']).'" title="'.esc_attr($value[0]['post_title'].' - '.$value[0]['post_date']).'">'.$key."</a></li>\n";
								}
								if ( $i == 1 ) { 
									$j++;
								}
								if ( $i == 1 AND  0 === ($j % $partlength)  ) {
									$k++;
								}
							} else {
								echo "\t".'<li class="customfieldlistelements_'.$number.'_'.$k.'">(2)'.__('Internal Plugin Error: value is no array', 'custom-field-list-widget')."</li>\n";
							}
						}
					break;
				}
			break;
		}
	} else {
		switch ($list_format) { 
			case 'dropdownmenu' :
				if ( 'yes' == $group_by_firstchar AND 'individual_href' == $internal_list_style) {
					foreach ($n as $key => $value) {
						if ('' != $n[$key]['post_title']) {
							echo "\t".'<option class="customfieldoptionelements_'.$number.'_'.$k.' customfieldlist_opt_link" value="'.get_permalink($n[$key]['post_id']).'">'.customfieldlist_strlimiter($n[$key]['post_title'], $strlimiter['limittype'], $strlimiter['maxlength'], $strlimiter['abbrev'])."</option>\n";
						}
					}
				} else {
					foreach ($n as $key => $value) {
						echo "\t".'<option class="customfieldoptionelements_'.$number.'_'.$k.' customfieldlist_opt_link" value="'.get_permalink($n[$key]['post_id']).'">'.customfieldlist_strlimiter($n[$key]['post_title'], $strlimiter['limittype'], $strlimiter['maxlength'], $strlimiter['abbrev'])."</option>\n";
					}
				}
			break;
			case 'ul_list' :
			default:
				foreach ($n as $key => $value) {
					echo "\t".'<li class="customfieldlistelements_'.$number.'_'.$k.'"><a href="'.get_permalink($n[$key]['post_id']).'" title="'.esc_attr($n[$key]['post_title'].' - '.$n[$key]['post_date']).'">'.$n[$key]['post_title']."</a></li>\n";
				}
			break;
		}
	}
}


/**
* customfieldlist_strlimiter - if the input phrase is longer then maxlength then cut out character from the middle of the phrase
*
* @package customfieldlist
* @since 1.1.2
*
* @param str $phrase input string
* @param str $limittype [optional] - where should the function cut a piece out of the str: 'end' (default) or 'middle'
* @param int $maxlength [optional] - max. length of the output string. if it is zero then don't shorten the string
* @param bool $abbrev [optional] - use the abbr-tag with the original string as the title element
* @param str $paddingchar [optional] - character(s) which should symbolize the shortend string / placed in the middle of the shortend string
* @param str $classname [optional] - name(s) of the CSS class(es) of the abbr-tag
*
* @return str phrase with max. length
*/
function customfieldlist_strlimiter($phrase, $limittype = 'end', $maxlength = 35, $abbrev = FALSE, $paddingchar = ' ... ', $classname = 'customfieldlist_abbr') {
	$len = strlen($phrase);
	$maxlen = ($maxlength-strlen($paddingchar));
	if ( $len > $maxlen AND $maxlen > 0 ) {
		switch ($limittype) {
			default :
			case 'end' :
				if ($abbrev == TRUE) {
					if ( Trim($classname) != '' ) {
						return '<span class="'.$classname.'" title="'.esc_attr($phrase).'">' . substr($phrase, 0, $maxlen) . $paddingchar . '</span>';
					} else {
						return '<span title="'.esc_attr($phrase).'">' . substr($phrase, 0, $maxlen) . $paddingchar . '</span>';
					}
				} else {
					return substr($phrase, 0, $maxlength) . $paddingchar;
				}
			break;
			case 'middle' :
				$part1_len = floor($maxlen/2);
				$part1 = substr($phrase, 0,  $part1_len);
				$part2_len = ceil($maxlen/2);
				$part2 = substr($phrase, -$part2_len, $len);
				if ($abbrev == TRUE) {
					if ( Trim($classname) != '' ) {
						return '<span class="'.$classname.'" title="'.esc_attr($phrase).'">' . $part1 . $paddingchar . $part2 . '</span>';
					} else {
						return '<span title="'.esc_attr($phrase).'">' . $part1 . $paddingchar . $part2 . '</span>';
					}
				} else {
					return $part1 . $paddingchar. $part2;
				}
			break;
		}
	} else {
		return $phrase;
	}
}

function customfieldlist_build_output_array($n, $j=0, $o=array()) {
	if ( TRUE === is_array($n) AND $j < count($n) ) { 
		$k = array_keys($n);
		$o = Array(strval($n[$k[$j]]) => $o);
		$j++;
		$o = customfieldlist_build_output_array($n, $j, $o);
	}
	return $o;
}

// This function is heavily inspired by an example in the comments to the explanation of array_merge_recursive at php.net
function customfieldlist_array_merge($arr, $ins, $hierarchymaxlevel, $i = 0) {
	if ( is_array($arr) ) {
		if ( is_array($ins) ) {
			foreach ( $ins as $k => $v ) {
				$i++;
				if ( isset($arr[$k]) && is_array($v) && is_array($arr[$k]) && $i < $hierarchymaxlevel ) {
					$arr[$k] = customfieldlist_array_merge($arr[$k], $v, $hierarchymaxlevel, $i);
				} else {
					//  add all following data as array element with a new key 
					while ( isset($arr[$k]) ) {
						// add up the key until a key is found which is not already a key in the actual array
						$k++;
					}
					$arr[$k] = $v;
				}
			}
		}
	} elseif ( !is_array($arr) && (strlen($arr)==0 || $arr==0) ) {
		$arr=$ins;
	}
	return($arr);
}

function customfieldlist_remove_empty_array_elements($in) {
	foreach($in as $key => $value) {
		if (FALSE === empty($value)) {
			$out[$key] = $value;
		} 
	}
	return $out;
}

function customfieldlist_are_the_array_elements_empty($ar) {
	foreach ($ar as $ar_val) {
		$strval = trim(strval($ar_val));
		if ( !empty($strval) ) {
			return FALSE;
		}
	}
	return TRUE;
}

function customfieldlist_clean_array_values($in) {
	return (strip_tags(stripslashes(trim($in))));
}

function customfieldlist_get_clean_unique_values($in) {
	$out = array_map('customfieldlist_clean_array_values', $in);
	return customfieldlist_remove_empty_array_elements($out);
}
/*
// helper function - only for development purposes
function customfieldlist_var_dump($var) {
	// write the out put to the log file
	$filename = CUSTOM_FIELD_LIST_WIDGET_DIR.'/widget_custom_field_list_var_dump.dat';
	if (is_file($filename)) {
		chmod ($filename, 0777);
		if ((filesize($filename)/1024) > 100) { unlink($filename); } // delete the Logfile if it is bigger than 100 kByte
	}
	$handle = fopen($filename, "a");
	fputs($handle, var_export($var, TRUE)."\n");
	$status = fclose($handle);
	if (is_file($filename)) {chmod ($filename, 0644);}
}
*/

function customfieldlist_get_parts_of_strings($output_array=array(), $list_part_nr_type='1Lfront') {
	$substrings=array();
	switch ($list_part_nr_type) {
		default:
		case '1Lfront':
		case '2Lfront':
		case '3Lfront':
			switch ($list_part_nr_type) {
				default:
				case '1Lfront':
					$len = 1;
				break;
				case '2Lfront':
					$len = 2;
				break;
				case '3Lfront':
					$len = 3;
				break;
			}
			if ( FALSE === function_exists('mb_substr') ) {
				foreach ($output_array as $key => $value) {
					$substrings[] = substr($key, 0, $len);
				}
			} else {
				$blog_charset = get_bloginfo('charset');
				foreach ($output_array as $key => $value) {
					$substrings[] = mb_substr($key, 0, $len, $blog_charset);
				}
			}
		break;
		case 'firstword':
			foreach ($output_array as $key => $value) {
				$name_parts = explode(' ', trim($key));
				$substrings[] = $name_parts[0];
			}
		break;
		case 'lastword' :
			foreach ($output_array as $key => $value) {
				$name_parts = explode(' ', trim($key));
				$substrings[] = end($name_parts);
			}
		break;
	}
	return $substrings;
}

function customfieldlist_group_main_list_items($output_array, $group_criteria='1Lfront') {
	switch ($group_criteria) {
		default:
		case '1Lfront' :
		case '2Lfront' :
		case '3Lfront' :
		case 'firstword' :
		case 'lastword' :
			$startat = 0;
			$len = 1;
		break;
	}
	$blog_charset = get_bloginfo('charset');
	foreach ($output_array as $key => $value) {
		if ( FALSE === function_exists('mb_substr') ) {
			$substring = substr($key, $startat, $len);
		} else {
			$substring = mb_substr($key, $startat, $len, $blog_charset);
		}
		$new_output_array[$substring][$key] = $value;
	}
	return $new_output_array;
}

// produces the basic structure of the sidebar widget. the lists will be printed out by the function customfieldlist_print_widget_content()
function customfieldlist($args=array(), $widget_args=1) {
	global $wpdb;
	extract( $args, EXTR_SKIP );
	
	if ( is_numeric($widget_args) ) {
		$widget_args = array( 'number' => $widget_args );
	}
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );
	
	$options = get_option('widget_custom_field_list');
 
	if ( !isset($options[$number]) ) {
		return;
	} else {
		$opt = $options[$number];
	}
	
	$partlength = intval($opt['partlength']);
	
	if ( FALSE !== $opt ) { //AND !empty($opt['header'])
		$header = $opt['header'];
	} else {
		$header =  __('Custom Field List','custom-field-list-widget');
	}
	
	echo $before_widget."\n";
		if (FALSE === empty($header)) {
			echo $before_title . $header . $after_title . "\n";
		}
		echo '<input type="hidden" name="customfieldlist_widget_id" value="'.$number.'"'." />\n";
		if ('yes' === $opt['partlist'] AND $partlength >= 3) {
			echo '<input type="hidden" id="customfieldlistpartlist_'.$number.'" value="yes"'." />\n";
		} else {
			echo '<input type="hidden" id="customfieldlistpartlist_'.$number.'" value="no"'." />\n";
		}
		//~ if (TRUE === is_user_logged_in()) {
			//~ $only_public = '';
		//~ } else {
			$only_public = ' AND p.post_status = "publish"';
		//~ }
		$j=0;
		$k=0;
		if (FALSE !== $opt) {
			// decide whether it should be a drop down menu or ul-list ( list appearance )
			switch ($opt['list_format']) {
				case 'dropdownmenu' :
					if (TRUE == isset($opt['use_fullscreen_selectbox']) AND TRUE === $opt['use_fullscreen_selectbox']) {
						echo '<select id="customfieldlist_main_menu_'.$number.'" class="customfieldlist_selectbox" onchange="customfieldlistwidget_go_to_target(this.id, this.selectedIndex);" onfocus="customfieldlistwidget_show_list_in_thickbox('.$number.', this.id);" title="'.esc_attr($header).'">'."\n";
					} else {
						echo '<select id="customfieldlist_main_menu_'.$number.'" class="customfieldlist_selectbox" onchange="customfieldlistwidget_go_to_target(this.id, this.selectedIndex);">'."\n";
					}
					if (FALSE == isset($opt['select_list_default']) OR '' == $opt['select_list_default']) {
						echo "\t".'<option value="nothing">'.__('Select:','custom-field-list-widget').'</option>'."\n";
					} else {
						echo "\t".'<option value="nothing">'.$opt['select_list_default'].'</option>'."\n";
					}
					$listelementtags['begin'] = '<option value="nothing">';
					$listelementtags['end'] = '</option>';
				break;
				case 'ul_list' :
				default:
					echo '<ul id="customfieldlist_mainlist_'.$number.'">'."\n";
					$listelementtags['begin'] = '<li>';
					$listelementtags['end'] = '</li>';
				break;
			}

			// get the data from the data base depending on the list type
			if ( is_array($opt['custom_field_names']) AND 1 <= count($opt['custom_field_names']) AND FALSE === customfieldlist_are_the_array_elements_empty($opt['custom_field_names']) ) {
				$charset=get_bloginfo('charset'); 
				if ( FALSE == isset($opt['use_chr_limit_location']) ) {
					$opt['use_chr_limit_location'] = 'end';
				}
				if ( FALSE == isset($opt['use_chr_limit']) ) {
					$opt['use_chr_limit'] = 0;
				}
				switch ($opt['list_type']) {
					case 'individual_href' :
						$only_public1='';

						// are both custom field names (which are only possible for that option) in use? 
						$customfieldname_0 = trim($opt['custom_field_names'][0]);
						$customfieldname_1 = trim($opt['custom_field_names'][1]);
						if ( !empty($customfieldname_0) AND !empty($customfieldname_1) ) {
							// if there are two custom field names then use the new method to produce the querystring:
							$more_than_one_custom_field_name = TRUE;
							$meta_keys = $opt['custom_field_names'];
							$customfieldname_show = $meta_keys[$opt['sort_by_custom_field_name']];
							$nr_meta_keys = 2;

							// build querystring
							if (TRUE === is_array($meta_keys) AND 0 < $nr_meta_keys) {
								for ( $i = 0; $i < $nr_meta_keys; $i++ ) {
									// select the values of the wp_postmeta table by different a name for each meta_key
									$select_meta_value_str .= 'pm'.$i.'.meta_value AS meta_value'.$i.', ';
										
									// add a LEFT JOIN for each meta_key resp. custom field name // this useful to produce a data base request result which contains a column with the meta_values of each meta_key (originally the meta_values of all meta_keys are in one column in wp_postmeta)
									if ( 0 < $i ) {
										$from_left_join_str = 'LEFT JOIN '.$wpdb->postmeta.' AS pm'.$i.' ON (pm0.post_id = pm'.$i.'.post_id AND pm'.$i.'.meta_key="'.$meta_keys[$i].'")';
									}
								}
								// build "Order By" string:
								if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($opt['db_collate']) AND !empty($opt['db_collate'])) ) {
									if ( '' == DB_COLLATE ) {
										$collation_string = $opt['db_collate'];
									} else {
										$collation_string = DB_COLLATE;
									}
									$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value COLLATE '.$collation_string.', LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
								} else {
									$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value, LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
								}
								$querystring = 'SELECT pm0.meta_id, pm0.post_id, '.$select_meta_value_str.'p.guid, p.post_title, p.post_status FROM '.$wpdb->postmeta.' AS pm0 '.$from_left_join_str.' LEFT JOIN '.$wpdb->posts.' AS p ON (pm0.post_id = p.ID) WHERE pm0.meta_key = "'.$customfieldname_show.'"'.$only_public1.' ORDER BY '.$order_by_str;
							}
						} else {
							// if there is only one custom field name then use the old method to produce the querystring:
							$more_than_one_custom_field_name = FALSE;
							if ( !empty($customfieldname_0) AND empty($customfieldname_1) ) {
								$customfieldname_show = $opt['custom_field_names'][0];
							} elseif ( empty($customfieldname_0) AND !empty($customfieldname_1) ) {
								$customfieldname_show = $opt['custom_field_names'][1];
							}

							if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($opt['db_collate']) AND !empty($opt['db_collate'])) ) {
								if ( '' == DB_COLLATE ) {
									$collation_string = $opt['db_collate'];
								} else {
									$collation_string = DB_COLLATE;
								}
								$order_by_str = 'pm.meta_value COLLATE '.$collation_string.', LENGTH(pm.meta_value)';
							} else {
								$order_by_str = 'pm.meta_value, LENGTH(pm.meta_value)';
							}													
							$querystring = 'SELECT pm.meta_id, pm.post_id, pm.meta_value, p.post_status FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$customfieldname_show.'"'.$only_public1.' ORDER BY '.$order_by_str;
						}

						if ( $customfieldname_show == $opt['individual_href']['thecustomfieldname'] ) {
							$meta_values = $wpdb->get_results($querystring);
							$nr_meta_values = count($meta_values);
							
							if ($nr_meta_values > 0) {
								if ( TRUE === $more_than_one_custom_field_name ) {
									if ( 1 == $opt['sort_by_custom_field_name'] ) {
										$meta_valuenameindex = 'meta_value0';
									} else {
										$meta_valuenameindex = 'meta_value1';
									}
								} else {
									$meta_valuenameindex = 'meta_value';
								}
								
								$meta_values_array = array();
								foreach ($meta_values as $meta_value) {
									$meta_values_array[$meta_value->meta_id]=$meta_value->$meta_valuenameindex;
									$meta_value_post_status[$meta_value->meta_id]=$meta_value->post_status;
								}
								if ( 'alphabetically' === $opt['sortby'] ) {
									if ( 'lastword' === $opt['orderelement'] ) {
										$old_locale = setlocale(LC_COLLATE, "0");
										$nr_meta_values = count($meta_values_array);
										if (FALSE !== strpos(strtolower(php_uname('s')), 'win') AND function_exists('mb_convert_encoding')) {
											foreach ( $meta_values_array as $key => $value ) {
												$meta_values_array_zw[$key] = mb_convert_encoding(str_replace("_", " ", end(preg_split("/\s+/", $value, -1, PREG_SPLIT_NO_EMPTY))), $opt['encoding_for_win']);
											}
											// build the charset name and setlocale on Windows machines 
											$loc = setlocale(LC_COLLATE, $opt['win_country_codepage']);
										} else {
											foreach ( $meta_values_array as $key => $value ) {
												$meta_values_array_zw[$key] = str_replace("_", " ", end(preg_split("/\s+/", $value, -1, PREG_SPLIT_NO_EMPTY)));
											}
											// build the charset name and setlocale on Linux (or other) machines 
											$wplang=get_locale();
											$loc = setlocale(LC_COLLATE, $wplang.'.'.DB_CHARSET);
										}
								
										// sort the meta_values
										if ( 'desc' === $opt['sortseq'] ) {
											arsort($meta_values_array_zw, SORT_LOCALE_STRING);
										} else {
											asort($meta_values_array_zw, SORT_LOCALE_STRING);
										}
										
										$individual_href_keys=array_keys($opt['individual_href']['id']);
										
										foreach ( $meta_values_array_zw as $key => $value ) {
											foreach ( $individual_href_keys as $individual_href_key ) {
												if ( $individual_href_key === $key ) {
													$individual_href['id'][$key] = $opt['individual_href']['id'][$key];
													$individual_href['link'][$key] = $opt['individual_href']['link'][$key];
													$individual_href['descr'][$key] = $opt['individual_href']['descr'][$key];
												}
											}
										}
										
										// turn the locale back
										$loc=setlocale(LC_COLLATE, $old_locale);
									} else {
										// reverse the sort sequence if the option says so
										if ( 'desc' === $opt['sortseq'] ) {
											$opt_rev = array_reverse($opt['individual_href']['id'], TRUE);
											$individual_href = $opt['individual_href'];
										} else {
											$individual_href = $opt['individual_href'];
										}
									}
								} else {
									$individual_href = $opt['individual_href'];
								}
								
								
								// get the data of the posts of this blog if post of this has been linked with the dropdown menu
								// built WHERE string
								foreach ($individual_href['id'] as $key => $post_id) {
									if ( 'none' !== $individual_href['id'][$key] ) {
										$where_ar[] = 'ID = '.$post_id;
									}
								}
								
								// if where_ar is no array then no links to own blog posts has been set ( all $link_target_post_id values are 'none')
								if (isset($where_ar) AND is_array($where_ar)) {
									$where = implode(' OR ', $where_ar);
									switch ($opt['sortby']) {
										default :
											$opt['sortby'] = 'alphabetically';
										case 'alphabetically' :
											$querystring = 'SELECT ID, post_title, post_status, post_date FROM '.$wpdb->posts." WHERE ".$where." ORDER BY ID DESC";
										break;
										case 'post_date' :
											if ( 'desc' == $opt['sortseq'] ) {
												$querystring = 'SELECT ID, post_title, post_status, post_date FROM '.$wpdb->posts." WHERE ".$where." ORDER BY post_date DESC";
											} else {
												$querystring = 'SELECT ID, post_title, post_status, post_date FROM '.$wpdb->posts." WHERE ".$where." ORDER BY post_date ASC";
											}
										break;
									}
									$post_status_results =  $wpdb->get_results($querystring);
									foreach ($post_status_results as $post_status_result) {
										$post_data[$post_status_result->ID]['post_title']=$post_status_result->post_title;
										$post_data[$post_status_result->ID]['post_status']=$post_status_result->post_status;
										$post_data[$post_status_result->ID]['post_date']=$post_status_result->post_date;
									}
								}
								
								switch ($opt['sortby']) {
									default :
										$opt['sortby'] = 'alphabetically';
									case 'alphabetically' :
										foreach ($individual_href['id'] as $key => $post_id) {
											$meta_value = $meta_values_array[$key];
											$descr = htmlspecialchars($individual_href['descr'][$key], ENT_COMPAT, $charset);
											if ('none' == $post_id) { // if there is no post or page id ...
												$output_array[$meta_value][0]['post_id'] = '';
												
												// ... then look for an URL which was free entered into the text box
												$url = trim($individual_href['link'][$key]);
												if ( FALSE == empty($url) ) {
													$output_array[$meta_value][0]['post_guid'] = $url;
													$output_array[$meta_value][0]['post_title'] = $descr;
												} else {
													$output_array[$meta_value][0]['post_guid'] = '';
													$output_array[$meta_value][0]['post_title'] = '';
												}
												$output_array[$meta_value][0]['post_date'] = '';
											} else {
												if ( '' != $only_public AND 'publish' != $post_data[$post_id]['post_status'] ) { // if there is a post_id check if the post is published and if the user is logged in
													$output_array[$meta_value][0]['post_id'] = '';
													$output_array[$meta_value][0]['post_guid'] = '';
													$output_array[$meta_value][0]['post_title'] = '';
													$output_array[$meta_value][0]['post_date'] = '';
												} else {
													$output_array[$meta_value][0]['post_id'] = $post_id;
													$output_array[$meta_value][0]['post_guid'] = get_permalink(intval($post_id));
													$output_array[$meta_value][0]['post_title'] = $descr;
													$output_array[$meta_value][0]['post_date'] = $post_data[$post_id]['post_date'];
												}
											}
										}
									break;
									case 'post_date' :
										// build output for the list elements which have a link to a local post
										foreach ($post_data as $post_dat) {
											$meta_id = array_search($post_dat['post_id'], $individual_href['id']);
											if (FALSE !== $meta_id) {
												if ( '' != $only_public AND 'publish' != $post_dat['post_status'] ) { // if there is a post_id check if the post is published and if the user is logged in
													$output_array[$meta_value][0]['post_id'] = '';
													$output_array[$meta_value][0]['post_guid'] = '';
													$output_array[$meta_value][0]['post_title'] = '';
													$output_array[$meta_value][0]['post_date'] = '';
												} else {
													$descr = htmlspecialchars($individual_href['descr'][$meta_id], ENT_COMPAT, $charset);
													$meta_value = $meta_values_array[$meta_id];
													$output_array[$meta_value][0]['post_id'] = $post_dat['post_id'];
													$output_array[$meta_value][0]['post_guid'] = get_permalink(intval($individual_href['id'][$meta_id]));
													$output_array[$meta_value][0]['post_title'] = $descr;
													$output_array[$meta_value][0]['post_date'] = $post_dat['post_date'];
												}
											}
										}
										// build output for the list elements which have a link to remote web site no link
										foreach ($individual_href['id'] as $key => $post_id) {
											$meta_value = $meta_values_array[$key];
											$descr = htmlspecialchars($individual_href['descr'][$key], ENT_COMPAT, $charset);
											if ('none' == $post_id) { // if there is no post or page id ...
												$output_array[$meta_value][0]['post_id'] = '';
												// ... then look for an URL which was free entered into the text box
												$url = trim($individual_href['link'][$key]);
												if ( FALSE == empty($url) ) {
													$output_array[$meta_value][0]['post_guid'] = $url;
													$output_array[$meta_value][0]['post_title'] = $descr;
												} else {
													$output_array[$meta_value][0]['post_guid'] = '';
													$output_array[$meta_value][0]['post_title'] = '';
												}
												$output_array[$meta_value][0]['post_date'] = '';
											}
										}
									break;
								}
								
								$hierarchymaxlevel=2;
								if ( 'yes' == $opt['group_by_firstchar'] ) {
									$output_array = customfieldlist_group_main_list_items($output_array, $group_criteria);
									$hierarchymaxlevel++;
								}								
								
								$nr_of_mainlistelements = count($output_array);
								$k = $nr_of_mainlistelements;
								$j = floor($nr_of_mainlistelements / $partlength);
								if ( 0 < ($nr_of_mainlistelements % $partlength) ) {
									$j++;
								}
								
								$liststyleopt = 'individual_href';
								
								$signslibrary = array(
									'dblarrows' => array('minus' => '&laquo;', 'plus' => '&raquo;'),
									'gtlt' => array('minus' => '&lt;', 'plus' => '&gt;'),
									'plusminus_short' => array('minus' => '-', 'plus' => '+'),
									'showhide' => array('minus' => '['.__('Hide','custom-field-list-widget').']', 'plus' => '['.__('Show','custom-field-list-widget').']'),
									'default' => array('minus' => '[ - ]', 'plus' => '[ + ]')
								);
								if ( FALSE == isset($opt['plusminusalt']) or FALSE == array_key_exists($opt['plusminusalt'], $signslibrary) ) {
									$signsgroup = 'default';
								} else {
									$signsgroup = $opt['plusminusalt'];
								}
								
								customfieldlist_print_widget_content($output_array, $number, $partlength, $hierarchymaxlevel, $opt['list_format'], $liststyleopt, $opt['show_number_of_subelements'], $signslibrary[$signsgroup], $charset, $opt['group_by_firstchar'], Array('limittype' => $opt['use_chr_limit_location'], 'maxlength' => $opt['use_chr_limit'], 'abbrev' => FALSE));
							} else {
								echo $listelementtags['begin'].sprintf(__('There are no values in connection to the custom field name "%1$s" in the data base.','custom-field-list-widget'), $customfieldname_show).$listelementtags['end']."\n";
							}
						} else {
							if ( empty($opt['individual_href']['thecustomfieldname']) ) {
								$customfieldname_from_db = '('.__('no value', 'custom-field-list-widget').')';
							} else {
								$customfieldname_from_db = $opt['individual_href']['thecustomfieldname'];
							}
							echo $listelementtags['begin'].sprintf(__('The actual custom field name "%1$s" and the custom field name "%2$s" for which the link references are saved are different. Please save the links for the values of the actual custom field name.','custom-field-list-widget'), $customfieldname_show, $customfieldname_from_db).$listelementtags['end']."\n";
						}
					break;
					case 'standard' :
					default :
						$meta_keys = $opt['custom_field_names'];
						$none_empty = customfieldlist_remove_empty_array_elements($meta_keys);
						$nr_meta_keys = count($none_empty);
						$nr_meta_values=0;
						if (TRUE === is_array($meta_keys) AND 0 < $nr_meta_keys) {
							$signslibrary = array(
								'dblarrows' => array('minus' => '&laquo;', 'plus' => '&raquo;'),
								'gtlt' => array('minus' => '&lt;', 'plus' => '&gt;'),
								'plusminus_short' => array('minus' => '-', 'plus' => '+'),
								'showhide' => array('minus' => '['.__('Hide','custom-field-list-widget').']', 'plus' => '['.__('Show','custom-field-list-widget').']'),
								'default' => array('minus' => '[ - ]', 'plus' => '[ + ]')
							);
							if ( FALSE == isset($opt['plusminusalt']) or FALSE == array_key_exists($opt['plusminusalt'], $signslibrary) ) {
								$signsgroup = 'default';
							} else {
								$signsgroup = $opt['plusminusalt'];
							}
							$select_meta_value_str = '';
							$from_left_join_str = '';
							
							// build querystring
							for ( $i = 0; $i < $nr_meta_keys; $i++ ) {
								// select the values of the wp_postmeta table by different a name for each meta_key
								$select_meta_value_str .= 'pm'.$i.'.meta_value AS meta_value'.$i.', ';
									
								// add a LEFT JOIN for each meta_key a.k.a. custom field name // this useful to produce a data base request result which contains a column with the meta_values of each meta_key (originally the meta_values of all meta_keys are in one column in wp_postmeta)
								if ( 0 < $i ) {
									$from_left_join_str .= 'LEFT JOIN '.$wpdb->postmeta.' AS pm'.$i.' ON (pm0.post_id = pm'.$i.'.post_id AND pm'.$i.'.meta_key="'.$meta_keys[$i].'")';
								}
							}
							
							switch ($opt['sortby']) {
								default :
									$opt['sortby'] = 'alphabetically';
								case 'alphabetically' :
									// build "Order By" string:
									if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($opt['db_collate']) AND !empty($opt['db_collate'])) ) {
										if ( '' == DB_COLLATE ) {
											$collation_string = $opt['db_collate'];
										} else {
											$collation_string = DB_COLLATE;
										}
										if (isset($opt['sort_titles_alphab']) AND 'yes' === $opt['sort_titles_alphab']) {
											if ( 'desc' == $opt['sortseq'] ) {
												$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value COLLATE '.$collation_string.', p.post_title COLLATE '.$collation_string.' DESC';
											} else {
												$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value COLLATE '.$collation_string.',  p.post_title COLLATE '.$collation_string.' ASC';
											}
										} else {
											if ( 'desc' == $opt['sortseq'] ) {
												$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value COLLATE '.$collation_string.' DESC, LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
											} else {
												$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value COLLATE '.$collation_string.' ASC, LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
											}
										}
									} else {
										if (isset($opt['sort_titles_alphab']) AND 'yes' === $opt['sort_titles_alphab']) {
											if ( 'desc' == $opt['sortseq'] ) {
												$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value, p.post_title DESC';
											} else {
												$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value, p.post_title ASC';
											}
										} else {
											if ( 'desc' == $opt['sortseq'] ) {
												$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value DESC, LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
											} else {
												$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value ASC, LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
											}
										}
									}
								break;
								case 'post_date' :
									if ( 'desc' === $opt['sortseq'] ) {
										$order_by_str = 'p.post_date DESC';
									} else {
										$order_by_str = 'p.post_date ASC';
									}
								break;
							}
//~ 							if ( 0 < $nr_meta_keys) {
//~ 								$group_by = ' GROUP BY pm0.post_id ';
//~ 							} else {
								$group_by = '';
//~ 							}
							
							if ( TRUE === is_array($opt['filter_by_category']) AND 0 < count($opt['filter_by_category'])) {
								$join_category_filter = 'LEFT JOIN '.$wpdb->term_relationships.' rel ON rel.object_id = pm0.post_id LEFT JOIN '.$wpdb->term_taxonomy.' tax ON tax.term_taxonomy_id = rel.term_taxonomy_id LEFT JOIN '.$wpdb->terms.' t ON t.term_id = tax.term_id ';
								$where_category = ' AND (';
								for ($i=0; $i < count($opt['filter_by_category']); $i++) {
									$where_category .= 't.slug = "'.$opt['filter_by_category'][$i].'"';
									if ( $i >= 0  AND $i < (count($opt['filter_by_category'])-1) ) {
										$where_category .= ' OR ';
									}
								}
								$where_category .= ')';
							} else {
								$join_category_filter = '';
								$where_category = '';
							}
							$querystring = 'SELECT pm0.post_id, '.$select_meta_value_str.'p.guid, p.post_title, p.post_date FROM '.$wpdb->postmeta.' AS pm0 '.$from_left_join_str.' LEFT JOIN '.$wpdb->posts.' AS p ON (pm0.post_id = p.ID) '.$join_category_filter.' WHERE pm0.meta_key = "'.$meta_keys[0].'"'.$only_public.$where_category.$group_by.' ORDER BY '.$order_by_str;
//~ 							echo '<!-- '.$querystring.' -->';
							$meta_values = $wpdb->get_results($querystring);
							$nr_meta_values = count($meta_values);
						}
						
						if ( 0 < $nr_meta_values ) {
							if ( 'alphabetically' === $opt['sortby'] AND 'lastword' === $opt['orderelement'] ) {
								$mvals=array();
								$old_locale = setlocale(LC_COLLATE, "0");
								$meta_value_name = meta_value.$opt['sort_by_custom_field_name'];
								if (FALSE !== strpos(strtolower(php_uname('s')), 'win') AND function_exists('mb_convert_encoding')) {
									for ( $i=0; $i < $nr_meta_values; $i++ ) {
										$mvals[] = mb_convert_encoding(str_replace("_", " ", end(preg_split("/\s+/", $meta_values[$i]->$meta_value_name, -1, PREG_SPLIT_NO_EMPTY))), $opt['encoding_for_win']);
									}
									// build the charset name and setlocale on Windows machines 
									$loc = setlocale(LC_COLLATE, $opt['win_country_codepage']);
								} else {
									for ( $i=0; $i < $nr_meta_values; $i++ ) {
										$mvals[] = str_replace("_", " ", end(preg_split("/\s+/", $meta_values[$i]->$meta_value_name, -1, PREG_SPLIT_NO_EMPTY)));
									}
									// build the charset name and setlocale on Linux (or other) machines 
									$wplang = get_locale();
									$loc = setlocale(LC_COLLATE, $wplang.'.'.DB_CHARSET);
								}

								// sort the meta_values
								if ( 'desc' === $opt['sortseq'] ) {
									arsort($mvals, SORT_LOCALE_STRING);
								} else {
									asort($mvals, SORT_LOCALE_STRING);
								}

								// turn the locale back
								$loc = setlocale(LC_COLLATE, $old_locale);
								
								// get the keys with the new order
								$mval_keys = array_keys($mvals);
								
								foreach ( $mval_keys as $mval_key ) {
									$meta_values_tmp[] = $meta_values[$mval_key];
								}
								$meta_values = $meta_values_tmp;
								unset($meta_values_tmp);
							}
							
							$hierarchy = $opt['hierarchy'];

							$clean_unique_values = customfieldlist_get_clean_unique_values($meta_keys);

							$nr_none_empty_meta_keys = count($clean_unique_values);
							$used_fields = $nr_none_empty_meta_keys;
							
							$dontshowthis_id = FALSE;
							foreach ($opt['donnotshowthis_customfieldname'] as $key => $value) {
								if ( 'sel' === $value ) { // there are custom field names which should not be included in the hierarchy
									$dontshowthis_id = $key;
								}
							}
							$new_used_fields = $used_fields;
							for ($i=0; $i < $used_fields; $i++) {
								if ( TRUE === is_numeric($dontshowthis_id) AND $hierarchy[$i] == $dontshowthis_id ) {
									$new_used_fields = $used_fields-1;
								} else {
									$meta_value_key_names[] ='meta_value'.$hierarchy[$i];
								}
							}
							$used_fields = $new_used_fields;
							
							krsort($meta_value_key_names);
							
							$result = Array();
							$output_array = Array();
							$hierarchymaxlevel=($used_fields+1);

							foreach ( $meta_values as $meta_value ) {
								$output_key_names = array();
								foreach ( $meta_value_key_names as $meta_value_key_name ) {
									$output_key_names[] = $meta_value->$meta_value_key_name;
								}
								$result_zw = customfieldlist_build_output_array($output_key_names, 0, Array(Array('post_id' => $meta_value->post_id, 'post_guid' => $meta_value->guid, 'post_title' => $meta_value->post_title, 'post_date' => $meta_value->post_date)));
								$output_array = customfieldlist_array_merge($output_array, $result_zw, $hierarchymaxlevel);
							}

							if ( 'yes' == $opt['list_style_opt1'] ) {
								$liststyleopt = 'each_element_with_sub_element';
							} else {
								$liststyleopt = 'standard';
							}
							
							if ( 'yes' == $opt['group_by_firstchar'] ) {
								$output_array = customfieldlist_group_main_list_items($output_array, $group_criteria);
								$liststyleopt = 'each_element_with_sub_element';
								$hierarchymaxlevel++;
							}
							
							if ( isset($opt['show_this_custom_field_name_as_heading']) ) {
								$meta_value_id = strval($opt['show_this_custom_field_name_as_heading']);
							} else {
								$meta_value_id =  '';
							}
							$nr_of_mainlistelements = count($output_array);
							$k = $nr_of_mainlistelements;
							$j = floor($nr_of_mainlistelements / $partlength);
							if ( 0 < ($nr_of_mainlistelements % $partlength) ) {
								$j++;
							}
							
							if ( 'yes' == $opt['list_style_opt1'] ) {
								$liststyleopt = 'each_element_with_sub_element';
							} else {
								$liststyleopt = 'standard';
							}
							
							customfieldlist_print_widget_content($output_array, $number, $partlength, $hierarchymaxlevel, $opt['list_format'], $liststyleopt, $opt['show_number_of_subelements'], $signslibrary[$signsgroup], $charset, $opt['group_by_firstchar'], Array('limittype' => $opt['use_chr_limit_location'], 'maxlength' => $opt['use_chr_limit'], 'abbrev' => FALSE));
						} else {
							echo $listelementtags['begin'].sprintf(__('There are no values which are related to the custom field names which are set on the widgets page.','custom-field-list-widget'), $opt['customfieldname']).$listelementtags['end']."\n";
						}
					break;
				}
			} else {
				echo $listelementtags['begin'].__('Please, define a custom field name!','custom-field-list-widget').$listelementtags['end']."\n";
			}
			
			switch ($opt['list_format']) {
				case 'dropdownmenu' :
					echo "</select><!-- select end --> \n";
				break;
				case 'ul_list' :
				default:
					echo "</ul><!-- ul end --> \n";
					echo '<input type="hidden" id="customfieldlistelements_'.$number.'" value="'.$j.'"'." />\n";
					if ($j > 0 AND $k > $partlength) {
						echo '<p class="customfieldlistpages" id="customfieldlistpages_'.$number.'"'.">\n";
						echo __('part','custom-field-list-widget').": ";
						
						// check out which part name tape should be used
						if ( !isset($opt['list_part_nr_type']) OR empty($opt['list_part_nr_type']) ) {
							$partnumbertype='numbers';
						} elseif ( 'numbers' != $opt['list_part_nr_type'] ) {
							$partnumbertype='letters';
						}
						// get the parts 
						if ( 'letters' == $partnumbertype ) {
							if ( TRUE == is_array($output_array) ) {
								$letters = customfieldlist_get_parts_of_strings($output_array, $opt['list_part_nr_type']);
							} else {
								$partnumbertype == 'numbers';
							}
						}
						for ($i=0; $i<$j; $i++) {
							if ( 0 === $i ) {
								$css_class=' class="customfieldlist_selectedpart"';
							} else {
								$css_class='';
							}
							switch ($partnumbertype) {
								case 'letters' :
									$nr = $i*$partlength;
									if ( isset($letters[($nr+$partlength-1)]) ) {
										$nr_last = $nr+$partlength-1;
									} else {
										$nr_last = count($letters)-1;
									}
									if ( $letters[$nr] != $letters[$nr_last] ) {
										echo '[<a id="customfieldlistpart_'.$number.'_'.$i.'"'.$css_class.' href="javascript:show_this_customfieldlistelements('.$i.', '.$j.', '.$number.');"> '.$letters[$nr].' - '.$letters[$nr_last].' </a>] ';
									} else {
										echo '[<a id="customfieldlistpart_'.$number.'_'.$i.'"'.$css_class.' href="javascript:show_this_customfieldlistelements('.$i.', '.$j.', '.$number.');"> '.$letters[$nr].' </a>] ';
									}
								break;
								case 'numbers' :
								default:
									echo '[<a id="customfieldlistpart_'.$number.'_'.$i.'"'.$css_class.' href="javascript:show_this_customfieldlistelements('.$i.', '.$j.', '.$number.');"> '.($i+1).' </a>] ';
								break;
							}
						}
						echo "\n</p>\n";
					}
				break;
			}
		} else {
			echo "<p>".__('Unable to retrieve the data of the customfield list widget from the db.','custom-field-list-widget')."</p>\n";
		}
	echo $after_widget."<!-- after_widget -->\n";
}

/*
 * the control- or preferences panel at the widgets page
 *
 * @param array|int $widget_args Widget number. Which of the several widgets of this type do we mean.
 */
 function customfieldlist_widget_control( $widget_args = 1 ) {
	global $wp_registered_widgets, $wpdb;
	static $updated = false; // Whether or not we have already updated the data after a POST submit
	
	if ( is_numeric($widget_args) ) {
		$widget_args = array( 'number' => $widget_args );
	}
	
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$opt = get_option("widget_custom_field_list");
	if ( !is_array($opt) ) {
		$opt = array();
	}
	
	// We need to update the data
	if ( !$updated && !empty($_POST['sidebar']) ) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) ) {
			$this_sidebar =& $sidebars_widgets[$sidebar];
		} else {
			$this_sidebar = array();
		}
		
		foreach ( $this_sidebar as $_widget_id ) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ( 'customfieldlist' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "customfieldlist-$widget_number", $_POST['widget-id'] ) ) { // the widget has been removed. "many-$widget_number" is "{id_base}-{widget_number}
					unset($opt[$widget_number]);
				}
			}
		}
		
		foreach ( (array) $_POST['customfieldlist-submit'] as $widget_number => $customfieldlist_option ) {
			// compile data from $widget_many_instance
			if ( !isset($_POST['customfieldlist_opt'][$widget_number]) OR !is_array($_POST['customfieldlist_opt'][$widget_number]) ) {// user clicked cancel
				continue;
			}
			$opt[$widget_number]['header'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['header'])));
			
			$hierarchy_error = FALSE;
			if ( TRUE === is_array($_POST['customfieldlist_opt'][$widget_number]['custom_field_names']) ) {
				$opt[$widget_number]['custom_field_names'] = array_map('customfieldlist_clean_array_values', $_POST['customfieldlist_opt'][$widget_number]['custom_field_names']); 
				$i=0;
				if ('individual_href' !== $_POST['customfieldlist_opt'][$widget_number]['list_type']) {
					foreach ( $opt[$widget_number]['custom_field_names'] as $custom_field_name ) {
						if ( 0 < $i AND ('' == $opt[$widget_number]['custom_field_names'][($i-1)] AND '' != $custom_field_name) ) {
							$hierarchy_error = TRUE;
						}
						if (TRUE === $hierarchy_error) {
							$opt[$widget_number]['custom_field_names'][$i] = '';
						}
						$i++;
					}
				}
			} else {
				$opt[$widget_number]['custom_field_names'] = array_fill(0, CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL, '');
			}
			
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['group_by_firstchar']) AND 'yes' == $_POST['customfieldlist_opt'][$widget_number]['group_by_firstchar'] ) {
				$opt[$widget_number]['group_by_firstchar'] = 'yes';
			} else {
				$opt[$widget_number]['group_by_firstchar'] = 'no';
			}

			if (TRUE === $hierarchy_error) {
				$opt[$widget_number]['sort_by_custom_field_name'] = 0;
			} else {
				$opt[$widget_number]['sort_by_custom_field_name'] = intval(strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['sort_by_custom_field_name']))));
			}
			
			if ( TRUE === is_array($_POST['customfieldlist_opt'][$widget_number]['hierarchy']) ) {
				$opt[$widget_number]['hierarchy'] = $_POST['customfieldlist_opt'][$widget_number]['hierarchy'];	
			} else {
				$opt[$widget_number]['hierarchy'] = range(0, (CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL-1));
			}
			
			if ( TRUE === is_array($_POST['customfieldlist_opt'][$widget_number]['donnotshowthis_customfieldname']) AND 1 === count($_POST['customfieldlist_opt'][$widget_number]['donnotshowthis_customfieldname']) AND FALSE === $hierarchy_error) {
				$opt[$widget_number]['donnotshowthis_customfieldname'] = array_fill(0, CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL, 'notsel');
				foreach ($_POST['customfieldlist_opt'][$widget_number]['donnotshowthis_customfieldname'] as $key => $value) {
					$opt[$widget_number]['donnotshowthis_customfieldname'][$value] = 'sel';
				}
			} else {
				$opt[$widget_number]['donnotshowthis_customfieldname'] = array_fill(0, CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL, 'notsel');
			}
			
			if ( TRUE === is_array($_POST['customfieldlist_opt'][$widget_number]['filterbycategory']) AND 0 < count($_POST['customfieldlist_opt'][$widget_number]['filterbycategory'])) {
				$opt[$widget_number]['filter_by_category'] = array_map('customfieldlist_clean_array_values', $_POST['customfieldlist_opt'][$widget_number]['filterbycategory']); 
			}
			// maybe for later usage
			if ( TRUE === is_array($_POST['customfieldlist_opt'][$widget_number]['filterbyterm_id']) AND 0 < count($_POST['customfieldlist_opt'][$widget_number]['filterbyterm_id'])) {
				$opt[$widget_number]['filter_by_term_id'] = array_map('customfieldlist_clean_array_values', $_POST['customfieldlist_opt'][$widget_number]['filterbyterm_id']); 
			}

			if ( 'asc' === $_POST['customfieldlist_opt'][$widget_number]['customfieldsortseq'] OR 'desc' === $_POST['customfieldlist_opt'][$widget_number]['customfieldsortseq'] ) {
				$opt[$widget_number]['sortseq'] = $_POST['customfieldlist_opt'][$widget_number]['customfieldsortseq'];
			} else {
				$opt[$widget_number]['sortseq'] = 'asc';
			}
			if ( 'alphabetically' === $_POST['customfieldlist_opt'][$widget_number]['customfieldsortby'] OR 'post_date' === $_POST['customfieldlist_opt'][$widget_number]['customfieldsortby'] ) {
				$opt[$widget_number]['sortby'] = $_POST['customfieldlist_opt'][$widget_number]['customfieldsortby'];
			} else {
				$opt[$widget_number]['sortby'] = 'alphabetically';
			}
			
			$opt[$widget_number]['db_collate'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['db_collate'])));
			$opt[$widget_number]['win_country_codepage'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['win_country_codepage'])));
			$opt[$widget_number]['encoding_for_win'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['encoding_for_win'])));
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['sort_titles_alphab']) AND 'yes' == $_POST['customfieldlist_opt'][$widget_number]['sort_titles_alphab']) {
				$opt[$widget_number]['sort_titles_alphab'] = 'yes';
			} else {
				$opt[$widget_number]['sort_titles_alphab'] = 'no';
			}
			
			if ( 'standard' !== $_POST['customfieldlist_opt'][$widget_number]['list_type'] AND 'individual_href' !== $_POST['customfieldlist_opt'][$widget_number]['list_type'] ) {
				$opt[$widget_number]['list_type'] = 'standard';
			} else {
				$opt[$widget_number]['list_type'] = $_POST['customfieldlist_opt'][$widget_number]['list_type'];
			}
			
			if ( 'ul_list' !== $_POST['customfieldlist_opt'][$widget_number]['list_format'] AND 'dropdownmenu' !== $_POST['customfieldlist_opt'][$widget_number]['list_format'] ) {
				$opt[$widget_number]['list_format'] = 'ul_list';
			} else {
				$opt[$widget_number]['list_format'] = $_POST['customfieldlist_opt'][$widget_number]['list_format'];
			}
			
			$opt[$widget_number]['select_list_default'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['select_list_default'])));
			
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['list_style_opt1']) ) {
				$opt[$widget_number]['list_style_opt1'] = 'yes';
			} else {
				$opt[$widget_number]['list_style_opt1'] = 'no';
			}
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['list_style_opt1_hidden']) AND 'yes' == $_POST['customfieldlist_opt'][$widget_number]['list_style_opt1_hidden'] ) {
				$opt[$widget_number]['list_style_opt1'] = 'yes';
				$opt[$widget_number]['list_style_opt1_hidden'] = 'yes';
			} else {
				$opt[$widget_number]['list_style_opt1_hidden'] = 'no';
			}

			if ( isset($_POST['customfieldlist_opt'][$widget_number]['show_number_of_subelements']) ) {
				$opt[$widget_number]['show_number_of_subelements'] = TRUE;
			} else {
				$opt[$widget_number]['show_number_of_subelements'] = FALSE;
			}
			
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['partlist']) ) {
				$opt[$widget_number]['partlist'] = 'yes';
			} else {
				$opt[$widget_number]['partlist'] = 'no';
			}
			
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['orderelement']) ) {
				$opt[$widget_number]['orderelement'] = 'lastword';
			} else {
				$opt[$widget_number]['orderelement'] = 'firstword';
			}
			
			$opt[$widget_number]['partlength'] = intval(strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['partlength']))));
			if ( is_nan($opt[$widget_number]['partlength']) OR $opt[$widget_number]['partlength'] < 3 ) {
				$opt[$widget_number]['partlength'] = 3;
			} 
			
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['list_part_nr_type']) ) {
				$opt[$widget_number]['list_part_nr_type'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['list_part_nr_type'])));
			} else {
				$opt[$widget_number]['list_part_nr_type'] = 'numbers';
			}
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['use_fullscreen_selectbox']) AND 'yes' == $_POST['customfieldlist_opt'][$widget_number]['use_fullscreen_selectbox']) {
				$opt[$widget_number]['use_fullscreen_selectbox'] = TRUE;
				$opt_general = get_option('widget_custom_field_list_general_options');
				$opt_general['use_fullscreen_selectbox'] = $widget_number;
				update_option('widget_custom_field_list_general_options', $opt_general);
			} else {
				$opt[$widget_number]['use_fullscreen_selectbox'] = FALSE;
			}
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['use_chr_limit']) ) {
				$opt[$widget_number]['use_chr_limit'] = intval(strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['use_chr_limit']))));
			} else {
				$opt[$widget_number]['use_chr_limit'] = 0;
			}
			switch ($_POST['customfieldlist_opt'][$widget_number]['use_chr_limit_location']) {
				default :
				case 'end' :
					$opt[$widget_number]['use_chr_limit_location'] = 'end';
				break;
				case 'middle' :
					$opt[$widget_number]['use_chr_limit_location'] = 'middle';
				break;
			}
		}
		update_option('widget_custom_field_list', $opt);
		$updated = true; // So that we don't go through this more than once
	}
	
	// Here we echo out the form
	if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$partlength = 3;
		$header =  __('Custom Field List','custom-field-list-widget');
		$number = '%i%';
	} else {
		$header =esc_attr($opt[$number]['header']);
		$partlength = $opt[$number]['partlength'];
	}
	
	echo '<p style="text-align:center;">'.__('Header (optional)','custom-field-list-widget').': <input type="text" name="customfieldlist_opt['.$number.'][header]" value="'.$header.'" maxlength="200" /><br /><span style="font-size:0.8em;">('.__('Leave the field empty for no widget title','custom-field-list-widget').')</span></p>'."\n";
	
	// section: custom field names
	echo '<div class="customfieldlist_section">'."\n";
		echo '<h5>'.__('Custom Field Names','custom-field-list-widget').'</h5>'."\n";
		
		if ( FALSE === is_array($opt[$number]['custom_field_names']) OR CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL > count($opt[$number]['custom_field_names']) ) {
			$opt[$number]['custom_field_names'] = array_fill(0, CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL, '');
		}
		
		switch ($opt[$number]['list_type']) {
			case 'individual_href' :
				$listlayoutopt1chk = '';
				$listlayoutopt3chk = ' checked="checked"';
				$liststyleopt1disabled = ' disabled="disabled"';
				$liststyleopt3disabled = ' disabled="disabled"';
				$sort_titles_alphab_disabled = ' disabled="disabled"';
			break;
			case 'standard' :
			default :
				$listlayoutopt1chk = ' checked="checked"';
				$listlayoutopt3chk = '';
				$liststyleopt1disabled = '';
				$liststyleopt3disabled = '';
				$sort_titles_alphab_disabled = '';
			break;
		}
		
		// nr of used text boxes :
		$nr_of_custom_field_names = 0;
		foreach ( $opt[$number]['custom_field_names'] as $custom_field_name ) {
			$cfn_trim = trim($custom_field_name);
			if ( !empty($cfn_trim) ) {
				$nr_of_custom_field_names++;
			}
		}
		
		$i=0;
		$thecustomfieldname='';
		echo '<div class="customfieldlist_customfieldnames_box">'."\n";
			echo '<div class="customfieldlist_row customfieldlist_head_row">'."\n";
				echo '<div class="customfieldlist_column_index">&nbsp;</div>';
				echo '<div class="customfieldlist_column_textbox customfieldlist_column_textbox_head">';
					_e('custom field names','custom-field-list-widget');
				echo '</div>';
				echo '<div class="customfieldlist_column_radiobutton">';
					_e('sort by','custom-field-list-widget');
				echo '</div>';
				echo '<div class="customfieldlist_column_checkbox">';
					_e('hide this','custom-field-list-widget');
				echo '</div>'."\n";
			echo '</div>'."\n";
		foreach ( $opt[$number]['custom_field_names'] as $custom_field_name ) {
			if (($i & 1) == 1) {$style = ' alternate';} else {$style = '';}
			echo '<div class="customfieldlist_row'.$style.'">'."\n";
			echo '<div class="customfieldlist_column_index">';
			echo $i . '.';
			echo '</div>';
			
			if ( 'individual_href' === $opt[$number]['list_type'] AND 1 < $i ) {
				$readonly_text_areas = ' readonly="readonly"';
				$disable_radio_buttons = ' disabled="disabled"';
			} else {
				$readonly_text_areas = '';
				$disable_radio_buttons = '';
			}
			
			########## TEXTAREA column #########################
			echo '<div class="customfieldlist_column_textbox">';
			echo '&nbsp;<input type="text" id="customfieldnames_'.$number.'_'.$i.'" name="customfieldlist_opt['.$number.'][custom_field_names][]" value="'.esc_attr($custom_field_name).'" maxlength="200" onchange="customfieldlist_customfieldname_changed(this.name, \''.$number.'\');"'.$readonly_text_areas.' />';
			echo '</div>';
			
			########## RADIO BUTTON column #####################
			if ( $i == $opt[$number]['sort_by_custom_field_name'] ) {
				$checked=' checked="checked"';
				if ( 1 < $nr_of_custom_field_names ) {
					$disable_check_boxes = '';
				} else {
					$disable_check_boxes = ' disabled="disabled"';
				}
				$disable_radio_buttons = '';
			} else {
				$checked='';
				if ( 1 < $nr_of_custom_field_names ) {
					$cfn_trim = trim($opt[$number]['custom_field_names'][$i]);
					if ( !empty($cfn_trim) ) {
						$disable_radio_buttons = '';
					} else {
						$disable_radio_buttons = ' disabled="disabled"';
					}
				} else {
					$disable_radio_buttons = ' disabled="disabled"';
				}

				$disable_check_boxes = ' disabled="disabled"';
			}
			echo '<div class="customfieldlist_column_radiobutton">';
			echo '&nbsp;<input type="radio" name="customfieldlist_opt['.$number.'][sort_by_custom_field_name]" value="'.$i.'"'.$checked.' onclick="customfieldlist_radio_button_changed(this.name, \''.$number.'\', '.$i.');"'.$disable_radio_buttons.' />';
			echo '</div>';
			
			########## CHECKBOX column ########################
			$checked='';
			$disabled='';
			if ( 'sel' === $opt[$number]['donnotshowthis_customfieldname'][$i] ) {
				$checked=' checked="checked"';
			} else {
				$checked='';
			}
			echo '<div class="customfieldlist_column_checkbox">';
			echo '&nbsp;<input type="checkbox" id="donnotshowthis_customfieldname_'.$number.'_'.$i.'" name="customfieldlist_opt['.$number.'][donnotshowthis_customfieldname][]" value="'.$i.'"'.$checked.$disabled.' onclick="customfieldlist_checkbox_changed(this.id, this.name, \''.$number.'\', '.$i.');"'.$disable_check_boxes.' />';

			########## hidden HIERARCHY column ####################
			echo '<input type="hidden" name="customfieldlist_opt['.$number.'][hierarchy][]" value="'.$i.'">';
			//~ echo '<select id="customfieldhierarchy_'.$number.'" name="customfieldlist_opt['.$number.'][hierarchy][]" style="width:10%;">';
			//~ for ($j=0; $j < (CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL+1); $j++) {
				//~ if ( $j == intval($opt[$number]['hierarchy'][$i]) ) {
					//~ $selected=' selected="selected"';
				//~ } else {
					//~ $selected='';
				//~ }
				
				//~ if ( CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL == $j ) {
					//~ echo '<option value="'.$j.'"'.$selected.'>'. __('do not show this','custom-field-list-widget') .'</option>';
				//~ } else {
					//~ echo '<option value="'.$j.'"'.$selected.'>'. $j .'</option>';
				//~ }
			//~ }
			//~ echo '</select>';
			echo '</div>';
			echo '</div>';
			$i++;
		}
		echo '</div>'."\n";
		
		echo '<div id="customfieldlist_hierarchy_vacancy_error_'.$number.'" class="customfieldlist_error" style="display:none;">'. __('If you want to create a list with several hierarchy levels then fill the custom field name fields one by one.','custom-field-list-widget').'</div>'."\n";
		
		########## BEGIN: check if the custom field names are used for same posts ##########
		$notequal = FALSE;
		$where='';
		if (1 < $nr_of_custom_field_names) {
			for ( $i=0; $i < $nr_of_custom_field_names; $i++ ) {
				$zw_post_ids = Array();
				$query_postmeta_values = "SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key='".$opt[$number]['custom_field_names'][$i]."' ORDER BY post_id DESC";
				$postmeta_values = $wpdb->get_results($query_postmeta_values);
				foreach ($postmeta_values as $postmeta_value) {
					$zw_post_ids[]=$postmeta_value->post_id;
				}
				$postmetas[$i]['post_ids'] = $zw_post_ids;
				$postmetas[$i]['nr_of_values'] = sizeof($postmeta_values);
				$postmetas[$i]['meta_key'] = $opt[$number]['custom_field_names'][$i];
				// compare the post_ids
				if ( 0 < $i ) {
					if ( $postmetas[($i-1)]['nr_of_values'] == $postmetas[$i]['nr_of_values'] ) {
						for  ($j=0; $j < $nr_of_custom_field_names; $j++ ) {
							if ( $postmetas[($i-1)]['post_ids'][$j] != $postmetas[$i]['post_ids'][$j]) {
								$notequal = TRUE;
							}
						}
					} else {
						$notequal = TRUE;
					}
				}
			}
		}
		if ( TRUE === $notequal ) {
			echo '<div class="customfieldlist_advice" id="customfieldlist_advice_cfn_usage">'."\n";
				echo ''.__('You are using more than one custom field name. But these custom field names are not used in the same amount of posts or in the same amount per posts.<br />It is likely that the appearance of the list in the side bar is not as intended.<br />The table gives an overview which and how often a custom field is used:','custom-field-list-widget').''."\n";
				$result = Array();
				foreach ( $postmetas as $postmeta ) {
					$result = array_merge($result , $postmeta['post_ids']);
				}
				$result = array_unique($result);
				$nr_unique_post_ids = count($result);
				rsort($result);
				$where='';
				$i=0;
				foreach ( $result as $ID ) {
					if ( $i < ($nr_unique_post_ids-1) ) {
						$where .= "ID=".$ID." OR ";
					} else {
						$where .= "ID=".$ID."";
					}
					$i++;
				}
				$querystring = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE ".$where;
				$posttitles_class = $wpdb->get_results($querystring);
				if (FALSE == $posttitles_class) {
					$posttitles = array_fill_keys($result,'');
				} else {
					foreach ( $posttitles_class as $posttitle_class ) {
						$posttitles[$posttitle_class->ID] = $posttitle_class->post_title;
					}
				}
				echo '<table class="cc_interval_table widefat">'."\n";
				echo '<thead>'."\n";
					echo '<tr>'."\n";
					echo '<th rowspan="2">'.__('custom field names','custom-field-list-widget').'</th>';
					echo '<th colspan="'.$nr_unique_post_ids.'">'.__('post IDs','custom-field-list-widget').'</th>';
					echo '</tr>'."\n";
					echo '<tr>'."\n";
					for ( $i=0; $i < $nr_unique_post_ids; $i++ ) {
						echo '<th><acronym class="customfieldlist_acronym" title="'.$posttitles[$result[$i]].'">'.$result[$i].'</acronym></th>';
					}
					echo '</tr>'."\n";
				echo '</thead>'."\n";
				echo '<tbody>'."\n";
				foreach ( $postmetas as $postmeta ) {
					echo '<tr>'."\n";
						echo '<td>';
						echo $postmeta['meta_key'];
						echo '</td>';
						for ( $i=0; $i < $nr_unique_post_ids; $i++ ) {
							echo '<td>';
							$counter=0;
							foreach ( $postmeta['post_ids'] as $postmeta_id ) {
								if ( $result[$i] == $postmeta_id ) {
									$counter++;
								}
							}
							if (0 < $counter) {
								echo $counter.'x';
							}
							echo '</td>';
						}
					echo '</tr>'."\n";
					$i++;
				}
				echo '</tbody>'."\n";
				echo '</table>'."\n";
			echo '</div>'."\n";
		}
		########## END: check if the custom field names are used for same posts #####################
		
		if ( 'yes' == $opt[$number]['group_by_firstchar'] ) {
			$group_by_firstchar = ' checked="checked"';
		} else {
			$group_by_firstchar = '';
		}
		echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_group_by_firstchar_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_group_by_firstchar" class="customfieldlist_label">'.__('group the values by the first character','custom-field-list-widget').'</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][group_by_firstchar]" id="customfieldlist_opt_'.$number.'_group_by_firstchar" value="yes"'.$group_by_firstchar.' onclick="customfieldlist_group_by_firstchar_changed(this.id, \''.$number.'\');" />'."\n";
		echo '<p id="customfieldlist_opt_'.$number.'_group_by_firstchar_explanation" class="customfieldlist_explanation">'.sprintf(__('Groups the custom field value by their first character after retrieving from the database. This might be a useful option if you have many values and you do not want to use the option "%1$s" to keep the list in the sidebar short.)','custom-field-list-widget'), __('display only a part of the list elements at once','custom-field-list-widget')).'</p>'."\n";
		echo '</div>'."\n";
	echo '</div>'."\n"; // end of section: custom field names

	// set the custom field name to variable which will be given to the link window, too
	// are both custom field names (which are only possible for that option) in use? 
	$customfieldname_0 = trim($opt[$number]['custom_field_names'][0]);
	$customfieldname_1 = trim($opt[$number]['custom_field_names'][1]);
	if ( !empty($customfieldname_0) AND !empty($customfieldname_1) ) {
		$thecustomfieldname = $opt[$number]['custom_field_names'][intval($opt[$number]['sort_by_custom_field_name'])];
	} else {
		if ( empty($customfieldname_0) ) {
			$thecustomfieldname = $opt[$number]['custom_field_names'][1];
		} else {
			$thecustomfieldname = $opt[$number]['custom_field_names'][0];
		}
	}
	
	// section: filter options
	echo '<div class="customfieldlist_section">'."\n";
		echo '<h5>'.__('Filter Options','custom-field-list-widget').'</h5>';
		$categories = get_categories();
		echo '<fieldset class="customfieldlist_fieldset_h3"><legend>'.__('include only posts of these categories into the list','custom-field-list-widget').':</legend>';
		echo '<ul>';
		if ( 0 < count($categories) ) {
			for($i = 0; $i < count($categories); $i++) {
				if ( TRUE === isset($opt[$number]['filter_by_category']) AND TRUE === is_array($opt[$number]['filter_by_category']) AND in_array($categories[$i]->{'slug'}, $opt[$number]['filter_by_category'])) {
					$customfield_include_category_checked = 'checked="checked"';
				} else {
					$customfield_include_category_checked = '';
				}
				echo '<li><label for="customfield_filterbycategory_'.$number.'_'.$i.'" class="customfieldlist_label">'.$categories[$i]->{'name'}.' ('.$categories[$i]->{'count'}.') <input type="checkbox" id="customfield_filterbycategory_'.$number.'_'.$i.'" name="customfieldlist_opt['.$number.'][filterbycategory][]" value="'.$categories[$i]->{'slug'}.'" '.$customfield_include_category_checked.'><input type="hidden" name="customfieldlist_opt['.$number.'][filterbyterm_id][]" value="'.$categories[$i]->{'cat_ID'}.'" /></label></li>';
			}
		} else {
			echo '<li>'.__('No categories found.','custom-field-list-widget').'</li>';
		}
		echo '</ul>';
		echo '</fieldset>';
	echo '</div>'."\n";
	
	// section: Sorting Options
	echo '<div class="customfieldlist_section">'."\n";
		echo '<h5>'.__('Sorting Options','custom-field-list-widget').'</h5>';
		
		if ( TRUE !== isset($opt[$number]['sortby']) OR FALSE !== empty($opt[$number]['sortby']) OR 'alphabetically' === $opt[$number]['sortby'] ) {
			$customfieldsortby_alphabetically_checked=' checked="checked"';
			$customfieldsortby_post_date_checked='';
			$customfieldsortby_lastword_disabled = '';
		} else {
			$customfieldsortby_alphabetically_checked='';
			$customfieldsortby_post_date_checked=' checked="checked"';
			$customfieldsortby_last_word_checked='';
			$customfieldsortby_lastword_disabled=' disabled="disabled"';
		}
		echo '<fieldset class="customfieldlist_fieldset_h3"><legend>'.__('sort criterion','custom-field-list-widget').':</legend>';
			echo '<div><label for="customfieldsortby_'.$number.'_alphabetically" class="customfieldlist_label">'.__('custom field values (alphabetically)','custom-field-list-widget').' <input type="radio" id="customfieldsortby_'.$number.'_alphabetically" name="customfieldlist_opt['.$number.'][customfieldsortby]" value="alphabetically"'.$customfieldsortby_alphabetically_checked.' onclick="customfieldlist_sort_by_changed(this.id, \''.$number.'\');" /></label></div>';
			echo '<div>'.'<a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_sort_by_post_date_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldsortby_'.$number.'_post_date" class="customfieldlist_label">'.__('post date','custom-field-list-widget').' <input type="radio" id="customfieldsortby_'.$number.'_post_date" name="customfieldlist_opt['.$number.'][customfieldsortby]" value="post_date"'.$customfieldsortby_post_date_checked.' onclick="customfieldlist_sort_by_changed(this.id, \''.$number.'\');" /></label>';
			echo '<p id="customfieldlist_opt_'.$number.'_sort_by_post_date_explanation" class="customfieldlist_explanation">'.__('A core function of this plugin is to link custom field values to posts. By using this option the custom field values will be arranged by the date of the posts they are linked to.','custom-field-list-widget').'</p>'."\n";
			echo '</div>';
		echo '</fieldset>';
		
		if ( TRUE !== isset($opt[$number]['sortseq']) OR TRUE === empty($opt[$number]['sortseq']) OR 'asc' === $opt[$number]['sortseq'] ) {
			$customfieldsortseq_ASC_checked=' checked="checked"';
			$customfieldsortseq_DESC_checked='';
		} else {
			$customfieldsortseq_ASC_checked='';
			$customfieldsortseq_DESC_checked=' checked="checked"';
		}
		echo '<fieldset class="customfieldlist_fieldset_h3"><legend>'.__('sort sequence','custom-field-list-widget').':</legend>';
			echo '<div><label for="customfieldsortseq_'.$number.'_asc" class="customfieldlist_label">'.__('ascending (ASC)','custom-field-list-widget').'</label> <input type="radio" id="customfieldsortseq_'.$number.'_asc" name="customfieldlist_opt['.$number.'][customfieldsortseq]" value="asc"'.$customfieldsortseq_ASC_checked.' /></div>';
			echo '<div><label for="customfieldsortseq_'.$number.'_desc" class="customfieldlist_label">'.__('descending (DESC)','custom-field-list-widget').'</label> <input type="radio" id="customfieldsortseq_'.$number.'_desc" name="customfieldlist_opt['.$number.'][customfieldsortseq]" value="desc"'.$customfieldsortseq_DESC_checked.' /></div>';
		echo '</fieldset>';
		echo '<fieldset class="customfieldlist_fieldset_h3"><legend>'.__('further sorting options','custom-field-list-widget').':</legend>';
			// section: select DB_CHARSET
			if (FALSE == defined('DB_COLLATE')) {
				echo '<p><a href="http://dev.mysql.com/doc/refman/5.1/en/charset-charsets.html" target="_blank">'.__('database collation','custom-field-list-widget').'</a>: <input type="text" name="customfieldlist_opt['.$number.'][db_collate]" value="'.esc_attr($opt[$number]['db_collate']).'" maxlength="200" /></p>'."\n";
			}
			
			// section: "sort by the last word" preferences
			$wplang = get_locale();
			$old_locale = setlocale(LC_COLLATE, "0");
			$loc = setlocale(LC_COLLATE, $wplang.'.'.get_bloginfo('charset'), $wplang, 'english_usa');
			setlocale(LC_COLLATE, $old_locale);
			if (FALSE === $loc) {
				$message_setloc = '<div class="customfieldlist_error">'.__('This option will probably not work. Because it is not possible to set "setlocale(LC_COLLATE, ... " on this server.','custom-field-list-widget').'</div>';
				$message_os_asterisk = ' class="customfieldlist_error_chkb"';
			} else {
				if (FALSE !== strpos(strtolower(php_uname('s')), 'win')) {
					if (function_exists('mb_convert_encoding')) {
						// the encoding which PHP multibyte supports  http://www.php.net/manual/en/mbstring.supported-encodings.php (without these: 'UTF-32', 'UTF-32BE', 'UTF-32LE', 'UTF-16', 'UTF-16BE', 'UTF-16LE', 'UTF-7', 'UTF7-IMAP', 'UTF-8',
						$encodings = array('UCS-4' => 'UCS-4', 'UCS-4BE' => 'UCS-4BE', 'UCS-4LE' => 'UCS-4LE', 'UCS-2' => 'UCS-2', 'UCS-2BE' => 'UCS-2BE', 'UCS-2LE' => 'UCS-2LE', 'ASCII' => 'ASCII', 'EUC-JP' => 'EUC-JP', 'SJIS' => 'SJIS', 'eucJP-win' => 'eucJP-win', 'SJIS-win' => 'SJIS-win', 'ISO-2022-JP' => 'ISO-2022-JP', 'JIS' => 'JIS', 'ISO-8859-1' => 'ISO-8859-1', 'ISO-8859-2' => 'ISO-8859-2', 'ISO-8859-3' => 'ISO-8859-3', 'ISO-8859-4' => 'ISO-8859-4', 'ISO-8859-5' => 'ISO-8859-5', 'ISO-8859-6' => 'ISO-8859-6', 'ISO-8859-7' => 'ISO-8859-7', 'ISO-8859-8' => 'ISO-8859-8', 'ISO-8859-9' => 'ISO-8859-9', 'ISO-8859-10' => 'ISO-8859-10', 'ISO-8859-13' => 'ISO-8859-13', 'ISO-8859-14' => 'ISO-8859-14', 'ISO-8859-15' => 'ISO-8859-15', 'byte2be' => 'byte2be', 'byte2le' => 'byte2le', 'byte4be' => 'byte4be', 'byte4le' => 'byte4le', 'BASE64' => 'BASE64', 'HTML-ENTITIES' => 'HTML-ENTITIES', '7bit' => '7bit', '8bit' => '8bit', 'EUC-CN' => 'EUC-CN', 'CP936' => 'CP936', 'HZ' => 'HZ', 'EUC-TW' => 'EUC-TW', 'CP950' => 'CP950', 'BIG-5', 'EUC-KR' => 'EUC-KR', 'UHC' => 'CP949', 'ISO-2022-KR' => 'ISO-2022-KR', 'Windows-1251' => 'CP1251', 'Windows-1252' => 'CP1252', 'IBM866' => 'CP866', 'KOI8-R' => 'KOI8-R');
						$message_os = '<div class="customfieldlist_advice">'.__('The servers OS is Windows (which is not able to sort UTF-8) what makes it probably necessary for the correct functioning of this option to:','custom-field-list-widget').'<br />';
						$message_os .= __('1. enter your <a href="http://msdn.microsoft.com/en-gb/library/39cwe7zf.aspx" target="_blank">language</a> and <a href="http://msdn.microsoft.com/en-gb/library/cdax410z.aspx" target="_blank">country</a> name and eventually the <a href="http://en.wikipedia.org/wiki/Windows_code_pages" target="_blank">code page number</a> (like german_germany or german_germany.1252 for German)','custom-field-list-widget').': <input type="text" name="customfieldlist_opt['.$number.'][win_country_codepage]" value="'.esc_attr($opt[$number]['win_country_codepage']).'" maxlength="200" style="width:92%;" /><br />';
						$message_os .= __('2. select the (same) code page in the form PHP can handle (e.g. Windows-1252 for German)','custom-field-list-widget').': ';
						$message_os .= '<select name="customfieldlist_opt['.$number.'][encoding_for_win]">';
						$stored_encoding =esc_attr($opt[$number]['encoding_for_win']);
						foreach ($encodings as $keyname => $encoding) {
							if ($encoding == $stored_encoding) {
								$message_os .= '<option value="'.$encoding.'" selected="selected">'.$keyname.'</option>';
							} else {
								$message_os .= '<option value="'.$encoding.'">'.$keyname.'</option>';
							}
						}
						$message_os .= '</select>';
						$message_os .= '</div>';
						$message_os_asterisk = ' class="customfieldlist_advice_chkb"';
					} else {
						$message_os = '<div class="customfieldlist_error">'.__('This option will probably not work on this server because this plugin converts the encoding of the meta values to the encoding of the OS (Windows) with the function mb_convert_encoding but this function is not available.','custom-field-list-widget').'</div>';
						$message_os_asterisk = ' class="customfieldlist_error_chkb"';
					}
				} else {
					$message_os = '';
				}
				$message_setloc = '';
			}
			if ( 'yes' == $opt[$number]['sort_titles_alphab'] AND 'standard' == $opt[$number]['list_type'] ) {
				$sort_titles_alphab = ' checked="checked"';
			} else {
				$sort_titles_alphab = '';
			}
			if ( 'lastword' === $opt[$number]['orderelement'] ) {
				$sort_titles_alphab = '';
				$sort_titles_alphab_disabled = ' disabled="disabled"';
				echo '<div'.$message_os_asterisk.'><label for="customfieldlist_sortbylastword_'.$number.'" class="customfieldlist_label">'.__('sort the values by the last word','custom-field-list-widget').' <input type="checkbox" name="customfieldlist_opt['.$number.'][orderelement]" id="customfieldlist_sortbylastword_'.$number.'" value="lastword" checked="checked" onclick="customfieldlist_sortbylastword_changed(this.id, \''.$number.'\');"'.$customfieldsortby_lastword_disabled.' /></label></div>'.$message_os.$message_setloc.''."\n";
			} else {
				echo '<div'.$message_os_asterisk.'><label for="customfieldlist_sortbylastword_'.$number.'" class="customfieldlist_label">'.__('sort the values by the last word','custom-field-list-widget').' <input type="checkbox" name="customfieldlist_opt['.$number.'][orderelement]" id="customfieldlist_sortbylastword_'.$number.'" value="lastword" onclick="customfieldlist_sortbylastword_changed(this.id, \''.$number.'\');"'.$customfieldsortby_lastword_disabled.' /></label></div>'.$message_os.$message_setloc.''."\n";
			}
			if ( TRUE === isset($opt[$number]['sortby']) AND 'post_date' === $opt[$number]['sortby'] ) {
				$sort_titles_alphab = '';
				$sort_titles_alphab_disabled = ' disabled="disabled"';
			}
			echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_sort_titles_alphab_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_sort_titles_alphab" class="customfieldlist_label">'.__('sort sub list elements alphabetically','custom-field-list-widget').'</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][sort_titles_alphab]" id="customfieldlist_opt_'.$number.'_sort_titles_alphab" value="yes"'.$sort_titles_alphab.$sort_titles_alphab_disabled.' />'."\n";
			echo '<p id="customfieldlist_opt_'.$number.'_sort_titles_alphab_explanation" class="customfieldlist_explanation">'.__('Arrange the sub list elements (which are e.g. post titles) in alphabetical order (By default (box is unchecked) the post titles are arranged by date.)','custom-field-list-widget').'</p>'."\n";
			echo '</div>'."\n";
		echo '</fieldset>';
		
	echo '</div>'."\n";

	// section: select the list type
	echo '<div class="customfieldlist_section">'."\n";
		echo '<h5>'.__('List Types','custom-field-list-widget').'</h5>'."\n";
		echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_type_opt1_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_type_opt1" class="customfieldlist_label">'.__('standard layout','custom-field-list-widget').'</label> <input type="radio" name="customfieldlist_opt['.$number.'][list_type]" id="customfieldlist_opt_'.$number.'_list_type_opt1" value="standard" '.$listlayoutopt1chk.' onclick="customfieldlist_opt_changed(this.id, \''.$number.'\');" />'."\n";
		echo '<p id="customfieldlist_opt_'.$number.'_list_type_opt1_explanation" class="customfieldlist_explanation">'.__('Only list elements of custom field names with more than one custom field value have sub elements. These sub elements becoming visible by clicking on the custom field name list elements or the + sign. The other list elements with one value are the hyper links to the posts and the values are in the link title.','custom-field-list-widget').'</p>'."\n";
		echo '</div>'."\n";
		
		echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_type_opt2_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_type_opt2" class="customfieldlist_label">'.__('a list with manually linked values','custom-field-list-widget').'</label> <input type="radio" name="customfieldlist_opt['.$number.'][list_type]" id="customfieldlist_opt_'.$number.'_list_type_opt2" value="individual_href" '.$listlayoutopt3chk.' onclick="customfieldlist_opt_changed(this.id, \''.$number.'\');" />'."\n";	
		echo '<p id="customfieldlist_opt_'.$number.'_list_type_opt2_explanation" class="customfieldlist_explanation">'.__('A simple list of all unique custom field values of one custom field name. Each value can be linked individually.','custom-field-list-widget').'</p>'."\n";
		echo '</div>'."\n";
		echo '<input type="button" class="button" id="customfieldlist_opt_'.$number.'_set_links" title="'.sprintf(__('Set a Link for each custom field value of the custom field: %1$s','custom-field-list-widget'), $thecustomfieldname).'" value="'.__('Set the links','custom-field-list-widget').'" onclick="customfieldlist_set_links(\'\', \''.$number.'\', this.id);" />'."\n";
		echo '<input type="hidden" id="customfieldlist_opt_'.$number.'_set_links_helper" value="'.sprintf(__('Set a Link for each custom field value of the custom field: %1$s','custom-field-list-widget'), $thecustomfieldname).'" />'."\n";
	echo '</div>'."\n";
	
	// section: list appearance
	switch ($opt[$number]['list_format']) {
		case 'dropdownmenu' :
			$listformatopt1chk = '';
			$listformatopt2chk = ' checked="checked"';
		break;
		case 'ul_list' :
		default :
			$listformatopt1chk = ' checked="checked"';
			$listformatopt2chk = '';
		break;
	}
	echo '<div class="customfieldlist_section">'."\n";
		echo '<h5>'.__('List Appearance','custom-field-list-widget').'</h5>'."\n";
		// ### Opt ###
		if ( 'standard' == $opt[$number]['list_type'] AND ('yes' == $opt[$number]['list_style_opt1'] OR 'yes' == $opt[$number]['list_style_opt1_hidden']) ) {
			$liststyleopt1chk = ' checked="checked"';
		} else {
			$liststyleopt1chk = '';
		}
		echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_style_opt1_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_style_opt1" class="customfieldlist_label">'.__('each element with sub elements','custom-field-list-widget').'</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][list_style_opt1]" id="customfieldlist_opt_'.$number.'_list_style_opt1" value="yes"'.$liststyleopt1chk.''.$liststyleopt1disabled.' onclick="customfieldlist_list_style_opt1_changed(this.id, \''.$number.'\');" />'."\n";
		echo '<p id="customfieldlist_opt_'.$number.'_list_style_opt1_explanation" class="customfieldlist_explanation">'.sprintf(__('Display each custom field name as a list element with the custom field value as a sub element. All sub elements are always visible and they are the hyper links to the posts. (Only available in combination with list type "%1$s")','custom-field-list-widget'),__('standard layout','custom-field-list-widget')).'</p>'."\n";
		if (FALSE == empty($liststyleopt1chk)) {$liststyleopt1hidden = 'yes';} else {$liststyleopt1hidden = 'no';}
		echo '<input type="hidden" name="customfieldlist_opt['.$number.'][list_style_opt1_hidden]" id="customfieldlist_opt_'.$number.'_list_style_opt1_hidden" value="'.$liststyleopt1hidden.'" />'."\n";
		echo '</div>'."\n";

		// ### Opt ###
		echo '<div class="customfieldlist_option_with_top_space"><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_format_opt1_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_format_opt1" class="customfieldlist_label">'.__('simple list','custom-field-list-widget').'</label> <input type="radio" name="customfieldlist_opt['.$number.'][list_format]" id="customfieldlist_opt_'.$number.'_list_format_opt1" value="ul_list"'.$listformatopt1chk.' onclick="customfieldlist_list_appearancetype_changed(this.id, \''.$number.'\');" />'."\n";
		echo '<p id="customfieldlist_opt_'.$number.'_list_format_opt1_explanation" class="customfieldlist_explanation">'.__('display the list elements in a simple bulleted lists','custom-field-list-widget').'</p>'."\n";
		echo '</div>'."\n";
		
		echo '<fieldset class="customfieldlist_fieldset_h2"><legend>'.__('simple list','custom-field-list-widget').':</legend>';
			// ### Opt ###
			if ( TRUE === $opt[$number]['show_number_of_subelements'] ) {
				$liststyleopt3chk = ' checked="checked"';
			} else {
				$liststyleopt3chk = '';
			}
			echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_style_opt3_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_style_opt3" class="customfieldlist_label">'.__('show the number of sub elements','custom-field-list-widget').'</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][show_number_of_subelements]" id="customfieldlist_opt_'.$number.'_list_style_opt3" value="yes"'.$liststyleopt3chk.''.$liststyleopt3disabled.' />'."\n";
			echo '<p id="customfieldlist_opt_'.$number.'_list_style_opt3_explanation" class="customfieldlist_explanation">'.sprintf(__('Display the number of sub elements after each list element with at least one sub element. (Only available in combination with list type "%1$s")','custom-field-list-widget'),__('standard layout','custom-field-list-widget')).'</p>'."\n";
			echo '</div>'."\n";

			// ### Opt ###
//~ 			if ( TRUE === $opt[$number]['list_items_expanded'] ) {
//~ 				$list_items_expanded_chk = ' checked="checked"';
//~ 			} else {
//~ 				$list_items_expanded_chk = '';
//~ 			}
//~ 			echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_style_opt3_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> ';
//~ 			echo '<label for="customfieldlist_opt_'.$number.'_list_style_opt3" class="customfieldlist_label">'.__('display all grouped list items expanded','custom-field-list-widget').'</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][list_items_expanded]" id="customfieldlist_opt_'.$number.'_list_style_opt3" value="yes"'.$list_items_expanded_chk.''.$liststyleopt3disabled.' />'."\n";
//~ 			echo '<p id="customfieldlist_opt_'.$number.'_list_style_opt3_explanation" class="customfieldlist_explanation">'.sprintf(__('Display the number of sub elements after each list element with at least one sub element. (Only available in combination with list type "%1$s")','custom-field-list-widget'),__('standard layout','custom-field-list-widget')).'</p>'."\n";
//~ 			echo '</div>'."\n";

			echo '<fieldset class="customfieldlist_fieldset_h3"><legend>'.__('partitioned list','custom-field-list-widget').':</legend>';
				// ### Opt ###
				if ( 'yes' == $opt[$number]['partlist'] ) {
					$liststyleopt2chk = ' checked="checked"';
					$liststyleoptpartlengthdisabled = '';
					$liststyleopt4disabled = '';
				} else {
					$liststyleopt2chk = '';
					$liststyleoptpartlengthdisabled = ' disabled="disabled"';
					$liststyleopt4disabled = ' disabled="disabled"';
				}
				echo '<div><label for="customfieldlist_opt_'.$number.'_list_style_opt2" class="customfieldlist_label">'.__('display only a part of the list elements at once','custom-field-list-widget').'</label> <input type="checkbox" id="customfieldlist_opt_'.$number.'_list_style_opt2" name="customfieldlist_opt['.$number.'][partlist]" value="yes"'.$liststyleopt2chk.' onclick="customfieldlist_partitionedlist_optionsswitch(this.id, \''.$number.'\');" /></div>'."\n";
				
				// ### Opt ###
				echo '<div><label for="customfieldlist_opt_'.$number.'_partlength" class="customfieldlist_label">'.__('elements per part of the list','custom-field-list-widget').' (X>=3)</label> <input type="text" id="customfieldlist_opt_'.$number.'_partlength" name="customfieldlist_opt['.$number.'][partlength]" value="'.$partlength.'" maxlength="5" style="width:5em;"'.$liststyleoptpartlengthdisabled.' /></div>'."\n";
				
				// ### Opt ###
				echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_style_opt4_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_style_opt4" class="customfieldlist_label">'.__('pagination type','custom-field-list-widget').' - '.__('use the','custom-field-list-widget').'</label> ';
				echo '<select id="customfieldlist_opt_'.$number.'_list_style_opt4" name="customfieldlist_opt['.$number.'][list_part_nr_type]"'.$liststyleopt4disabled.'>';
				$list_part_nr_types = array(
					'numbers' => __('numbers','custom-field-list-widget'),
					'1Lfront' => __('first letter','custom-field-list-widget'),
					'2Lfront' => __('first two letters','custom-field-list-widget'),
					'3Lfront' => __('first three letters','custom-field-list-widget'),
					'firstword' => __('first word','custom-field-list-widget'),
					'lastword' => __('last word','custom-field-list-widget')
				);
				foreach ($list_part_nr_types as $keyname => $list_part_nr_type) {
					if ($keyname == $opt[$number]['list_part_nr_type']) {
						echo '<option value="'.$keyname.'" selected="selected">'.$list_part_nr_type.'</option>';
					} else {
						echo '<option value="'.$keyname.'">'.$list_part_nr_type.'</option>';
					}
				}
				echo '</select>';
				echo '<p id="customfieldlist_opt_'.$number.'_list_style_opt4_explanation" class="customfieldlist_explanation">'.sprintf(__('You can choose if the pagination of the list parts should be consecutive numbers or strings taken from the main list elements. If you choose a strings as pagination type then the list part names will consist of parts from the first and the last main list element of a list part (if they are different.) like e.g. [Am - Be] (with type "%1$s").','custom-field-list-widget'),__('first two letters','custom-field-list-widget')).'</p>'."\n";
				echo '</div>';
			echo '</fieldset>';
		echo '</fieldset>';
		
		echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_format_opt2_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_format_opt2" class="customfieldlist_label">'.__('drop down menu','custom-field-list-widget').'</label> <input type="radio" name="customfieldlist_opt['.$number.'][list_format]" id="customfieldlist_opt_'.$number.'_list_format_opt2" value="dropdownmenu"'.$listformatopt2chk.' onclick="customfieldlist_list_appearancetype_changed(this.id, \''.$number.'\');" />'."\n";// 	
		echo '<p id="customfieldlist_opt_'.$number.'_list_format_opt2_explanation" class="customfieldlist_explanation">'.__('Show the list elements as a drop down menu.','custom-field-list-widget').'</p>'."\n";
		echo '</div>'."\n";
		echo '<div id="customfieldlist_opt_'.$number.'_list_format_opt2_advice" class="customfieldlist_advice" style="display:none;">'.sprintf(__('It might be expedient to use the option "%1$s" or "%2$s" in combination with "%3$s".','custom-field-list-widget'),__('each element with sub elements','custom-field-list-widget'), __('group the values by the first character','custom-field-list-widget'), __('drop down menu','custom-field-list-widget')).'</div>'."\n";
		if (FALSE == isset($opt[$number]['select_list_default']) OR '' == $opt[$number]['select_list_default']) {
			$select_list_default_value = __('Select:','custom-field-list-widget');
		} else {
			$select_list_default_value =esc_attr($opt[$number]['select_list_default']);
		}
		if (TRUE == isset($opt[$number]['use_fullscreen_selectbox']) AND TRUE === $opt[$number]['use_fullscreen_selectbox']) {
			$chk_use_fullscreen_selectbox = ' checked="checked"';
		} else {
			$chk_use_fullscreen_selectbox = '';
		}
		if (TRUE == isset($opt[$number]['use_chr_limit']) AND FALSE === empty($opt[$number]['use_chr_limit'])) {
			$use_chr_limit_value = $opt[$number]['use_chr_limit'];
		} else {
			$use_chr_limit_value = '0';
		}
		switch ($opt[$number]['use_chr_limit_location']) {
			default :
			case 'end' :
				$use_chr_limit_location['end'] = ' selected = "selected"';
				$use_chr_limit_location['middle'] = '';
			break;
			case 'middle' :
				$use_chr_limit_location['end'] = '';
				$use_chr_limit_location['middle'] = ' selected = "selected"';
			break;
		}
		echo '<fieldset class="customfieldlist_fieldset_h2"><legend>'.__('drop down menu','custom-field-list-widget').':</legend>';
			echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_use_fullscreen_selectbox_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> <label for="customfieldlist_opt_'.$number.'_use_fullscreen_selectbox" class="customfieldlist_label">'.__('Show the drop down menu in a full screen box:','custom-field-list-widget').'</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][use_fullscreen_selectbox]" value="yes"'.$chk_use_fullscreen_selectbox.' id="customfieldlist_opt_'.$number.'_use_fullscreen_selectbox" />'."\n";
			echo '<p id="customfieldlist_opt_'.$number.'_use_fullscreen_selectbox_explanation" class="customfieldlist_explanation">'.__('If you let the list appear as a drop down menu and you have long custom field values or long post titles then it is possible that parts of the list elements are not visible on the screen e.g. the list juts out the screen if it is in the right sidebar. In such cases you might consider using this feature.<br />It displays the drop down menu in a wide box in the middle of the screen when the focus is on the drop down menu element.','custom-field-list-widget').'</p>'."\n";
			echo '</div>'."\n";
			echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_use_chr_limit_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> <label for="customfieldlist_opt_'.$number.'_use_chr_limit" class="customfieldlist_label">'.__('Limit the length of the list elements:','custom-field-list-widget').'</label> <input type="text" name="customfieldlist_opt['.$number.'][use_chr_limit]" value="'.$use_chr_limit_value.'" id="customfieldlist_opt_'.$number.'_use_chr_limit" maxlength="4" class="customfieldlist_opt_use_chr_limit" />'."\n";
			echo '<p id="customfieldlist_opt_'.$number.'_use_chr_limit_explanation" class="customfieldlist_explanation">'.__('It is the maximal number of characters per list item. Zero means: do not limit the number of characters.<br />If you let the list appear as a drop down menu and you have long custom field values or long post titles then it is possible that parts of the list elements are not visible on the screen e.g. the list juts out the screen if it is in the right sidebar. In such cases you might consider using this feature.','custom-field-list-widget').'</p>'."\n";
			echo '</div>'."\n";
			echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_use_chr_limit_location_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> <label for="customfieldlist_opt_'.$number.'_use_chr_limit_location" class="customfieldlist_label">'.__('Shorten the strings at the end or in the middle:','custom-field-list-widget').'</label> <select size="1" name="customfieldlist_opt['.$number.'][use_chr_limit_location]" id="customfieldlist_opt_'.$number.'_use_chr_limit_location"><option value="end"'.$use_chr_limit_location['end'].'>'.__('end','custom-field-list-widget').'</option><option value="middle"'.$use_chr_limit_location['middle'].'>'.__('middle','custom-field-list-widget').'</option></select>'."\n";
			echo '<p id="customfieldlist_opt_'.$number.'_use_chr_limit_location_explanation" class="customfieldlist_explanation">'.__('For instance: The long title is "Star Wars - The Return of the Jedi" and the max. number of chararcters is 20. "end" produces: "Star Wars - The ... " and "middle" produces: "Star Wars ... of the Jedi".','custom-field-list-widget').'</p>'."\n";
			echo '</div>'."\n";
			echo '<label for="customfieldlist_opt_'.$number.'_list_select_default_value" class="customfieldlist_label">'.__('What should be the default value of the drop down menu?:','custom-field-list-widget').'</label> <input type="text" name="customfieldlist_opt['.$number.'][select_list_default]" value="'.$select_list_default_value.'" id="customfieldlist_opt_'.$number.'_list_select_default_value" maxlength="200" style="width:92%;" />'."\n";
		echo '</fieldset>';
		
		if ( function_exists('get_admin_url') ) {
			$adminurl = get_admin_url(); // since WP 3.0
		} elseif ( function_exists('admin_url') ) {
			$adminurl = admin_url(); // since WP 2.6
		} else {
			$adminurl = site_url() . '/wp-admin';
		}
		$settingsurl = trailingslashit($adminurl).'options-general.php?page='.plugin_basename(__FILE__);

		echo '<p class="customfieldlist_more_settings_advice">'.sprintf(__('settings for all widgets can be changed at the <a href="%1$s">Custom Field List Widget settings page</a>','custom-field-list-widget'), $settingsurl).'</p>'."\n";
	echo '</div>'."\n";
	echo '<input type="hidden" id="customfieldlist-submit-'.$number.'" name="customfieldlist-submit['.$number.'][submit]" value="1" />'."\n";
}

add_action('widgets_init', 'customfieldlist_widget_init');
function customfieldlist_widget_init() {
	if ( !$options = get_option('widget_custom_field_list') ) {
		$options = array();
	}
	
	// Variables for our widget
	$widget_ops = array(
		'classname' => 'customfieldlist',
		'description' => __('Displays a list of custom field values of a set key', 'custom-field-list-widget')
	);
	
	// Variables for our widget options panel
	$control_ops = array(
		'width' => 500,
		'height' => 310,
		'id_base' => 'customfieldlist'
	);
	
	$registered = false;
	
	foreach ( array_keys($options) as $o ) {
		// Per Automattic: "Old widgets can have null values for some reason"
		if ( !isset($options[$o]['header']) ) {
			continue;
		}
		
		$id = $control_ops['id_base'].'-'.$o;
		
		// Register the widget and then the widget options menu
		wp_register_sidebar_widget($id, __('Custom Field List','custom-field-list-widget'), 'customfieldlist', $widget_ops, array('number' => $o));
		wp_register_widget_control($id, __('Custom Field List','custom-field-list-widget'), 'customfieldlist_widget_control', $control_ops, array('number' => $o));
	}

	if ( !$registered ) {
		wp_register_sidebar_widget($control_ops['id_base'].'-1', __('Custom Field List','custom-field-list-widget'), 'customfieldlist', $widget_ops, array('number' => -1));
		wp_register_widget_control($control_ops['id_base'].'-1', __('Custom Field List','custom-field-list-widget'), 'customfieldlist_widget_control', $control_ops, array('number' => -1));
	}
}

// add jquery scripts for the appearance of the widgets lists
add_action('wp_print_scripts', 'customfieldlist_widget_script');
function customfieldlist_widget_script() {
	if ( FALSE == is_admin() ) {		
		$signslibrary = array(
			'dblarrows' => array('minus' => '&laquo;', 'plus' => '&raquo;'),
			'gtlt' => array('minus' => '&lt;', 'plus' => '&gt;'),
			'plusminus_short' => array('minus' => '-', 'plus' => '+'),
			'showhide' => array('minus' => '['.__('Hide','custom-field-list-widget').']', 'plus' => '['.__('Show','custom-field-list-widget').']'),
			'default' => array('minus' => '[ - ]', 'plus' => '[ + ]')
		);
		
		$customfieldlist_widgets_general_options = get_option('widget_custom_field_list_general_options');
		
		if ( FALSE === $customfieldlist_widgets_general_options OR FALSE === isset($customfieldlist_widgets_general_options['plusminusalt']) OR FALSE == array_key_exists($customfieldlist_widgets_general_options['plusminusalt'], $signslibrary) ) {
			$customfieldlist_widgets_general_options['plusminusalt']='default';
		}
			
		if ( FALSE === $customfieldlist_widgets_general_options OR FALSE === isset($customfieldlist_widgets_general_options['effect_speed']) OR empty($customfieldlist_widgets_general_options['effect_speed']) ) {
			$customfieldlist_widgets_general_options['effect_speed']='normal';
		}
		
		$siteurl = get_option('siteurl');
		if (FALSE === $siteurl) {
			$siteurl = '..';
		}
		
		if ( TRUE == isset($customfieldlist_widgets_general_options['use_fullscreen_selectbox']) ) {
			wp_enqueue_script( 'thickbox' ); // that loads jQuery automatically
		} else {
			// load the jQuery library of WP and the scripts which are responsible for the effects
			wp_enqueue_script( 'jquery' );
		}
		
		$scriptfile = CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_js.php';
		wp_register_script( 'customfieldlist_widget_script',  $scriptfile , array('jquery') );
		wp_enqueue_script( 'customfieldlist_widget_script' );
	
		if ( FALSE == isset($customfieldlist_widgets_general_options['plusminusalt']) OR FALSE == array_key_exists($customfieldlist_widgets_general_options['plusminusalt'], $signslibrary) ) {
			$customfieldlist_widgets_general_options['plusminusalt'] = 'default';
		}
		// get the plus/minus sign or it's alternative for the jQuery functions which change the behaviour and the appearance of the sidebar widgets
		echo "\n".'<script type="text/javascript">'."\n";
		echo '//<![CDATA['."\n";
		$charset = get_bloginfo('charset');
		echo 'function customfieldlist_the_collapse_sign() {'."\n";
		echo '	var signs = new Object();'."\n";
		if ( TRUE == version_compare(PHP_VERSION, '5.0', '<') ) {
			if ( 'UTF-8' == $charset ) {
				echo "	signs['minus'] = '".utf8_encode(html_entity_decode($signslibrary[$customfieldlist_widgets_general_options['plusminusalt']]['minus'], ENT_QUOTES))."';\n";
				echo "	signs['plus'] = '".utf8_encode(html_entity_decode($signslibrary[$customfieldlist_widgets_general_options['plusminusalt']]['plus'], ENT_QUOTES))."';\n";
			} else {
				if (function_exists('mb_convert_encoding')) {
					echo "	signs['minus'] = '".mb_convert_encoding(html_entity_decode($signslibrary[$customfieldlist_widgets_general_options['plusminusalt']]['minus'], ENT_QUOTES), $charset)."';\n";
					echo "	signs['plus'] = '".mb_convert_encoding(html_entity_decode($signslibrary[$customfieldlist_widgets_general_options['plusminusalt']]['plus'], ENT_QUOTES), $charset)."';\n";
				} else {
					echo "	signs['minus'] = '".html_entity_decode($signslibrary[$customfieldlist_widgets_general_options['plusminusalt']]['minus'], ENT_QUOTES)."';\n";
					echo "	signs['plus'] = '".html_entity_decode($signslibrary[$customfieldlist_widgets_general_options['plusminusalt']]['plus'], ENT_QUOTES)."';\n";
				}
			}
		} else {
			echo "	signs['minus'] = '".html_entity_decode($signslibrary[$customfieldlist_widgets_general_options['plusminusalt']]['minus'], ENT_QUOTES, $charset)."';\n";
			echo "	signs['plus'] = '".html_entity_decode($signslibrary[$customfieldlist_widgets_general_options['plusminusalt']]['plus'], ENT_QUOTES, $charset)."';\n";
		}
		echo '	return signs;'."\n";
		echo '}'."\n";
		echo 'function customfieldlist_effect_speed() {'."\n";
		if ( FALSE == isset($customfieldlist_widgets_general_options['effect_speed']) OR TRUE === empty($customfieldlist_widgets_general_options['effect_speed']) ) {
			echo "	var speed = 'normal';\n";
		} else {
			echo "	var speed = '".$customfieldlist_widgets_general_options['effect_speed']."';\n";
		}
		echo '	return speed;'."\n";
		echo '}'."\n";
		
		if ( TRUE == isset($customfieldlist_widgets_general_options['use_fullscreen_selectbox']) AND FALSE !== $customfieldlist_widgets_general_options['use_fullscreen_selectbox'] ) {
			echo '// use absolute URLs instead of relative URLs for the Thickbox icons'."\n";
			echo "var tb_pathToImage = '".$siteurl."/wp-includes/js/thickbox/loadingAnimation.gif';"."\n";
			echo "var tb_closeImage = '".$siteurl."/wp-includes/js/thickbox/tb-close.png';"."\n";
			echo 'function customfieldlistwidget_show_list_in_thickbox(number, this_id) {'."\n";
			echo "	var tst = '".CUSTOM_FIELD_LIST_WIDGET_URL."/widget_custom_field_list_long_selectbox.php?height=130&width=' + Math.round(window.innerWidth-(window.innerWidth * 0.20)) + '&abspath=".(urlencode(ABSPATH))."&selectboxid=' + this_id + '&_wpnonce=".wp_create_nonce('customfieldlist_long_selectbox_security')."';\n";
			echo '	tb_show(document.getElementById( String(this_id) ).title, tst, false);'."\n";
			echo '}'."\n";
			echo '//]]>'."\n";
			echo '</script>'."\n";
		} else {
			echo 	'//]]>'."\n";
			echo '</script>'."\n";
		}
	}
}

// add styles for the appearance of the widgets lists 
add_action('wp_print_styles', 'customfieldlist_widget_style');
function customfieldlist_widget_style() {
	$stylefile = CUSTOM_FIELD_LIST_WIDGET_URL.'/style/widget_custom_field_list.css';
	wp_register_style( 'customfieldlist_widget_style', $stylefile );
	wp_enqueue_style( 'customfieldlist_widget_style' );
	$customfieldlist_widgets_general_options = get_option('widget_custom_field_list_general_options');
	if ( TRUE == isset($customfieldlist_widgets_general_options['use_fullscreen_selectbox']) AND FALSE !== $customfieldlist_widgets_general_options['use_fullscreen_selectbox'] ) {
		wp_enqueue_style( 'thickbox' );
	}
}

// add js on the widgets page
add_action('admin_print_scripts-widgets.php', 'customfieldlist_widget_admin_script');
function customfieldlist_widget_admin_script() {
	wp_enqueue_script( 'thickbox' );
	?>
	<script type="text/javascript">
	//<![CDATA[
	function customfieldlist_group_by_firstchar_changed(id, number) {
		if ( true == document.getElementById('customfieldlist_opt_' + String(number) + '_list_format_opt2').checked ) {
			customfieldlist_list_appearancetype_changed('customfieldlist_opt_' + String(number) + '_list_format_opt2', number);
		}
	}
	
	function customfieldlist_sort_by_changed(id, number) {
		if ( true == document.getElementById('customfieldsortby_' + String(number) + '_post_date').checked ) {
			document.getElementById('customfieldlist_opt_' + String(number) + '_sort_titles_alphab').checked = false;
			document.getElementById('customfieldlist_opt_' + String(number) + '_sort_titles_alphab').disabled = true;
			document.getElementById('customfieldlist_sortbylastword_' + String(number)).checked = false;
			document.getElementById('customfieldlist_sortbylastword_' + String(number)).disabled = true;
		} else {
			if ( false == document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt2').checked ) {
				document.getElementById('customfieldlist_opt_' + String(number) + '_sort_titles_alphab').disabled = false;
			}
			document.getElementById('customfieldlist_sortbylastword_' + String(number)).disabled = false;
		}
	}
	
	
	function customfieldlist_sortbylastword_changed(id, number) {
		if ( true == document.getElementById( id ).checked ) {
			document.getElementById('customfieldlist_opt_' + String(number) + '_sort_titles_alphab').checked = false;
			document.getElementById('customfieldlist_opt_' + String(number) + '_sort_titles_alphab').disabled = true;
		} else {
			if ( true == document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt1').checked ) {
				document.getElementById('customfieldlist_opt_' + String(number) + '_sort_titles_alphab').disabled = false;
			}
		}
	}
	
	function customfieldlist_list_appearancetype_changed(id, number) {
		// called when the user switches beween 'simple list' and 'drop down menu'
		if ( true == document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt1').checked && ('customfieldlist_opt_' + String(number) + '_list_format_opt2' == id && (document.getElementById('customfieldlist_opt_' + String(number) + '_group_by_firstchar').checked != true && document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt1').checked != true)) ) {
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_format_opt2_advice').style.display = 'block';
		} else {
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_format_opt2_advice').style.display = 'none';
		}
	}
	
	function customfieldlist_list_style_opt1_changed(id, number) {
		if ( document.getElementById( id ).checked == true ) {
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt1_hidden').value = 'yes';
		} else {
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt1_hidden').value = 'no';
		}
		if ( true == document.getElementById('customfieldlist_opt_' + String(number) + '_list_format_opt2').checked ) {
			customfieldlist_list_appearancetype_changed('customfieldlist_opt_' + String(number) + '_list_format_opt2', number);
		}
	}
	
	function customfieldlist_partitionedlist_optionsswitch(chkb_id, number) {
		if ( document.getElementById( chkb_id ).checked==true ) {
			document.getElementById('customfieldlist_opt_' + String(number) + '_partlength').disabled = false;
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt4').disabled = false;
		} else {
			document.getElementById('customfieldlist_opt_' + String(number) + '_partlength').disabled = true;
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt4').disabled = true;
		}
	}
	
	function customfieldlist_set_links(link, number, this_id) {
		if ( document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt2').checked == true ) {
			if ( 'unsaved_changes' != document.getElementById( this_id + '_helper' ).value ) {
				document.getElementById(this_id).title = String(document.getElementById( this_id + '_helper' ).value);
				var scrheight = Math.round(window.innerHeight-(window.innerHeight * 0.12));
				var tst = '<?php  echo CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_individual_href.php?height=\'+scrheight+\'&width=750&abspath='.(rawurlencode(ABSPATH)).'&number='; ?>' + String(number) + '<?php echo '&_wpnonce='.wp_create_nonce('customfieldlist_individual_href_security'); ?>';
			} else {
				document.getElementById(this_id).title = '<?php echo __('Unsaved changes','custom-field-list-widget'); ?>';
				var tst = '<?php  echo CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_individual_href_advice.php?height=100&width=750&abspath='.(rawurlencode(ABSPATH)).'&advicemsg=3&_wpnonce='.wp_create_nonce('customfieldlist_individual_href_security'); ?>';
			}
		} else {
			document.getElementById(this_id).title = '<?php echo __('Not available with these widget preferences.','custom-field-list-widget'); ?>';
			var tst = '<?php  echo CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_individual_href_advice.php?height=100&width=750&abspath='.(rawurlencode(ABSPATH)).'&advicemsg=2&_wpnonce='.wp_create_nonce('customfieldlist_individual_href_security'); ?>';
		}
		tb_show(document.getElementById( String(this_id) ).title, tst, false);
	}
	
	function customfieldlist_opt_changed (opt_id, number) {
		var txtb_elements_name = 'customfieldlist_opt[' + String(number) + '][custom_field_names][]';
		var alltxtb = document.getElementsByName(txtb_elements_name);
		var number_of_txtb = document.getElementsByName(txtb_elements_name).length;
			// show the advice for using the 'each element with sub elements' option
			if ( true == document.getElementById('customfieldlist_opt_' + String(number) + '_list_format_opt2').checked ) {
				customfieldlist_list_appearancetype_changed('customfieldlist_opt_' + String(number) + '_list_format_opt2', number);
			}
		// when opt2 was selected, make textareas, radio buttons and check boxes read only
		if ( 'customfieldlist_opt_'+ String(number) +'_list_type_opt2' == opt_id ) {
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt1').checked = false;
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt1').disabled = true;
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt3').checked = false;
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt3').disabled = true;
			document.getElementById('customfieldlist_opt_' + String(number) + '_sort_titles_alphab').checked = false;
			document.getElementById('customfieldlist_opt_' + String(number) + '_sort_titles_alphab').disabled = true;
			
			// which radio button is selected
			var rb_elements_name = 'customfieldlist_opt[' + String(number) + '][sort_by_custom_field_name]';
			var allrb = document.getElementsByName(rb_elements_name);
			var number_of_rbuttons = document.getElementsByName(rb_elements_name).length;
			var checked_rb_index = -1;
			var status;
			for (var i = 0; i < number_of_rbuttons; i++ ) {
				status = allrb[i].checked;	
				if ( true == status ) {
					checked_rb_index = i;
				} 
			}
			
			var chkb_elements_name = 'customfieldlist_opt[' + String(number) + '][donnotshowthis_customfieldname][]';
			var allchk = document.getElementsByName(chkb_elements_name);
		
			// disable all txt boxes (with index > 1) (and clear them), radio buttons and chk boxes
			for (var i = 0; i < number_of_txtb; i++ ) {
				if ( 1 < i ) {
					alltxtb[i].value = '';
					alltxtb[i].readOnly = true;
					allrb[i].disabled = true;
				} 
				allrb[i].disabled = true;
				allchk[i].checked = false;
				allchk[i].disabled = true;
			}
			
			// count the not empty fields and get the index_of_the_last_used_field
			var nr_of_used_fields = 0;
			var index_of_the_last_used_field = -1;
			var textb_status = Array();
			for (var i = 0; i < number_of_txtb; i++ ) {
				var trimed_val = alltxtb[i].value.replace(/\s/g, '' );
				if ( 0 < trimed_val.length ) {
					nr_of_used_fields++;
					index_of_the_last_used_field = i;
					textb_status[i] = true;
				} else {
					textb_status[i] = false;
				}
			}
			
			if ( 0 == nr_of_used_fields ) {
				allrb[0].disabled = false;
				allrb[0].checked = true;
			} else if ( 1 == nr_of_used_fields ) {
				allrb[index_of_the_last_used_field].disabled = false;
				allrb[index_of_the_last_used_field].checked = true;
			} else if ( 1 < nr_of_used_fields ) {
				// if the selected radio button has got an index > 1 then select the last used field and enable and check the checkbox right of it
				if ( 1 < checked_rb_index ) {
					checked_rb_index = index_of_the_last_used_field;
				} 
				allrb[checked_rb_index].disabled = false;
				allrb[checked_rb_index].checked = true;
				allchk[checked_rb_index].disabled = false;
				allchk[checked_rb_index].checked = true;
				// and enable the the other radio button
				if ( 0 == checked_rb_index ) {
					allrb[1].disabled = false;
				} else {
					allrb[0].disabled = false;
				}
			}
		} else {
			if ( false == document.getElementById('customfieldlist_sortbylastword_' + String(number)).checked && false == document.getElementById('customfieldsortby_' + String(number) + '_post_date').checked) {
				document.getElementById('customfieldlist_opt_' + String(number) + '_sort_titles_alphab').disabled = false;
			}
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt1').disabled = false;
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt3').disabled = false;
			if ( true == alltxtb[2].readOnly ) {
				for (var i = 2; i < number_of_txtb; i++ ) {
					alltxtb[i].readOnly = false;
				}
			}
		}
	}
	
	function customfieldlist_customfieldname_changed (name, number) {
		var list_type_opt2 = document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt2');
		
		var txtb_elements_name = 'customfieldlist_opt[' + String(number) + '][custom_field_names][]';
		var alltxtb = document.getElementsByName(txtb_elements_name);
		var number_of_txtb = alltxtb.length;
		
		// count the not empty fields and get the index_of_the_last_used_field
		var nr_of_used_fields = 0;
		var index_of_the_last_used_field = -1;
		var hierarchy_vacancy = false;
		var textb_status = Array();
		for (var i = 0; i < number_of_txtb; i++ ) {
			var trimed_val = alltxtb[i].value.replace(/\s/g, '' );
			if ( 0 < trimed_val.length ) {
				nr_of_used_fields++;
				index_of_the_last_used_field = i;
				textb_status[i] = true;
			} else {
				textb_status[i] = false;
			}
			if ( 0 < i && (false == textb_status[(i-1)] && true == textb_status[i]) && false == list_type_opt2.checked ) {
				hierarchy_vacancy = true;
			} 
		}
		var message = document.getElementById('customfieldlist_hierarchy_vacancy_error_'+String(number));
		if ( true == hierarchy_vacancy ) {
			message.style.display = 'block';
		} else {
			if ( 'block' == message.style.display ) {
				message.style.display = 'none';
			}
		}
		
		var rb_elements_name = 'customfieldlist_opt[' + String(number) + '][sort_by_custom_field_name]';
		var allrb = document.getElementsByName(rb_elements_name);
		var number_of_rbuttons = document.getElementsByName(rb_elements_name).length;

		// which radio button is selected
		var rb_checked_status = Array();
		var checked_rb_index = -1;
		for (var i = 0; i < number_of_rbuttons; i++ ) {
			var status = allrb[i].checked;	
			if ( true == status ) {
				rb_checked_status[i] = true;
				checked_rb_index = i;
			} else {
				rb_checked_status[i] = false;
			}
		}
		
		var chkb_elements_name = 'customfieldlist_opt[' + String(number) + '][donnotshowthis_customfieldname][]';
		var allchk = document.getElementsByName(chkb_elements_name);
		
		if ( 0 == nr_of_used_fields ) {
			for (var i = 0; i < number_of_rbuttons; i++ ) {
				allrb[i].disabled = true;
				allchk[i].checked = false;
				allchk[i].disabled = true;
			}
			allrb[0].disabled = false;
			allrb[0].checked = true;
		// if there is only one field in use then enable and select/check the radio button in this row and disable the other radio buttons
		} else if ( 1 == nr_of_used_fields ) {
			if ( index_of_the_last_used_field != checked_rb_index ) {
				checked_rb_index = index_of_the_last_used_field;
			} 
			for (var i = 0; i < number_of_rbuttons; i++ ) {
				if ( i == checked_rb_index ) {
					allrb[i].disabled = false;
					allrb[i].checked = true;
					allchk[i].disabled = false;
					allchk[i].checked = false;
				} else {
					allrb[i].disabled = true;
					allchk[i].checked = false;
					allchk[i].disabled = true;
				}
			}
		// else enable the radio buttons right of all used txt boxes (, select/check one of them) and enable checkbox right of the selected radio button
		} else if ( 1 < nr_of_used_fields ) {
			for (var i = 0; i < number_of_rbuttons; i++ ) {
				if ( true == textb_status[i] ) {
					allrb[i].disabled = false;
				} else {
					if ( i == checked_rb_index ) {
						checked_rb_index = index_of_the_last_used_field;
						allrb[checked_rb_index].checked = true;
					}
					allrb[i].disabled = true;
					allchk[i].checked = false;
					allchk[i].disabled = true;
				}
			}
			allchk[checked_rb_index].disabled = false;
		}
		
		// set the helper to "unsaved_changes" (this will be overwritten after saving the textbox values)
		document.getElementById( 'customfieldlist_opt_'+ String(number) + '_set_links_helper' ).value = 'unsaved_changes';
		
		switch (list_type_opt2.checked) {
			case true :
				//~ if ( index_of_the_last_used_field != checked_rb_index ) {
					//~ checked_rb_index = index_of_the_last_used_field;
				//~ } 
				
				if ( 1 < nr_of_used_fields ) {
					allchk[checked_rb_index].checked = true; 
				}
			break;
			case false :
			default:
			break;
		}
	}
	
	function customfieldlist_radio_button_changed (name, number, index) {
		// enable only the checkbox right of the radio button when the radio button selection was changed
		// which radio button is selected
		var checked_rb_index = Number(index);

		// select (only) the checkbox in the same row
		var chkb_elements_name = 'customfieldlist_opt[' + String(number) + '][donnotshowthis_customfieldname][]';
		var allchk = document.getElementsByName(chkb_elements_name);
		var number_of_checkboxes = document.getElementsByName(chkb_elements_name).length;
		
		// disable all check boxes
		for (var i = 0; i < number_of_checkboxes; i++ ) {
			allchk[i].checked = false;
			allchk[i].disabled = true;
		}
		
		var nr_of_used_fields = customfieldlist_get_nr_of_used_txtb( Number(number) );
		
		// check the checkbox right of radio button
		if ( 1 < nr_of_used_fields ) {
			allchk[checked_rb_index].disabled = false;
		}	
	
		// for individual href option: - check the checkbox right of the radio button
		var list_type_opt2 = document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt2');
		switch (list_type_opt2.checked) {
			case true :
				if ( 1 < nr_of_used_fields ) {
					allchk[checked_rb_index].checked = true;
				}
			break;
			case false :
			default:
			break;
		}
	}
	
	function customfieldlist_checkbox_changed (chbk_id, elements_name, number, index) {
		var list_type_opt2 = document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt2');
		switch (list_type_opt2.checked) {
			case true :
				var nr_of_used_fields = customfieldlist_get_nr_of_used_txtb(number);
				
				if ( true == list_type_opt2.checked && 2 == nr_of_used_fields) {
					var rb_elements_name = 'customfieldlist_opt[' + String(number) + '][sort_by_custom_field_name]';
					var allrb = document.getElementsByName(rb_elements_name);
					customfieldlist_select_only_this_chbk(chbk_id, elements_name, false);
					allrb[index].checked = true;
				} else {
					customfieldlist_select_only_this_chbk(chbk_id, elements_name, true);
				}
			break;
			case false :
			default:
			break;
		}
	}
	
	function customfieldlist_get_nr_of_used_txtb(number) {
		var txtb_elements_name = 'customfieldlist_opt[' + String(number) + '][custom_field_names][]';
		var alltxtb = document.getElementsByName(txtb_elements_name);
		var number_of_txtb = alltxtb.length;
		
		//count the not empty fields
		var nr_of_used_fields = 0;
		for (var i = 0; i < number_of_txtb; i++ ) {
			var trimed_val = alltxtb[i].value.replace(/\s/g, '' );
			if ( 0 < trimed_val.length ) {
				nr_of_used_fields++;
			}
		}
		return nr_of_used_fields;
	}
	
	function customfieldlist_select_only_this_chbk(chbk_id, elements_name, get_status) {
		var allchk = document.getElementsByName(elements_name);
		var number_of_checkboxes = document.getElementsByName(elements_name).length;
		switch (get_status) {
			case false :
				var status = true;
			break;
			default :
			case true :
				var status = document.getElementById(chbk_id).checked;
			break;
		}

		// uncheck all
		for (var i = 0; i < number_of_checkboxes; i++ ) {
			allchk[i].checked = false;
		}
	
		if ( false == status ) {
			// uncheck the one
			document.getElementById(chbk_id).checked = false;
		} else {
			// check the one
			document.getElementById(chbk_id).checked = true;
		}
	}
	
	function customfieldlist_show_this_explanation ( explanation_id ) {
		if ( document.getElementById(explanation_id).style.display == 'none' || document.getElementById(explanation_id).style.display == '' ) {
			document.getElementById(explanation_id).style.display = 'block';
		} else {
			document.getElementById(explanation_id).style.display = 'none';
		}
		return false;
	}
	//]]>
	</script>
	<?php
}

add_action('admin_print_styles-widgets.php', 'customfieldlist_widget_widgetsettings_styles');
function customfieldlist_widget_widgetsettings_styles() {
	wp_enqueue_style( 'thickbox' );
	$stylefile = CUSTOM_FIELD_LIST_WIDGET_URL.'/style/widget_custom_field_list_widgetsettings.css';
	wp_register_style( 'customfieldlist_widget_widgetsettings_style', $stylefile );
	wp_enqueue_style( 'customfieldlist_widget_widgetsettings_style' );
}

function customfieldlist_widget_general_options() {
	$signslibrary = array(
		'default' => array('minus' => '[ - ]', 'plus' => '[ + ]'),
		'dblarrows' => array('minus' => '&laquo;', 'plus' => '&raquo;'),
		'gtlt' => array('minus' => '&lt;', 'plus' => '&gt;'),
		'plusminus_short' => array('minus' => '-', 'plus' => '+'),
		'showhide' => array('minus' => '['.__('Hide','custom-field-list-widget').']', 'plus' => '['.__('Show','custom-field-list-widget').']')
	);
	
	$speeds = array(
		'slow' => __('slow','custom-field-list-widget'),
		'normal' => __('normal','custom-field-list-widget'),
		'fast' => __('fast','custom-field-list-widget')
	);

	if (isset($_POST['action']) AND 'update' == $_POST['action']) {
		check_admin_referer('customfieldlist_general_options_security');
		if ( isset($_POST['customfieldlist_plusminusalt']) AND TRUE == array_key_exists($_POST['customfieldlist_plusminusalt'], $signslibrary)) {
			$opt['plusminusalt'] = $_POST['customfieldlist_plusminusalt'];
		} else {
			$opt['plusminusalt'] = 'default';
		}
		if ( isset($_POST['customfieldlist_effect_speed']) AND TRUE == array_key_exists($_POST['customfieldlist_effect_speed'], $speeds)) {
			$opt['effect_speed'] = $_POST['customfieldlist_effect_speed'];
		} else {
			$opt['effect_speed'] = 'normal'; // default
		}
		$result = update_option('widget_custom_field_list_general_options', $opt);
		//~ if (FALSE === $result) {
			//~ echo '<div id="message" class="error fade"><p>' . __('No settings updated!','custom-field-list-widget') . '</p></div>';
		//~ } else {
			echo '<div id="message" class="updated fade"><p>' . __('Changes saved') . '</p></div>';
		//~ }
	} else {
		$opt = get_option('widget_custom_field_list_general_options');
	}

	echo '<div class="wrap">'."\n";
	echo '<h2>'.__('Custom Field List Widget - settings','custom-field-list-widget').'</h2>'."\n";
	echo '<form method="post" action="">'."\n";
	wp_nonce_field('customfieldlist_general_options_security');
	echo '<table class="form-table">'."\n";

	echo '<tr valign="top">'."\n";
	echo '<th scope="row">'.__('symbols to deflate/inflate the sub list elements','custom-field-list-widget').'</th>'."\n";
	echo '<td>';
		echo '<select id="customfieldlist_opt_plusminusalt" name="customfieldlist_plusminusalt">';
			foreach ($signslibrary as $signsgroup => $signs) {
				if ($signsgroup == $opt['plusminusalt']) {
					echo '<option value="'.$signsgroup.'" selected="selected">'.$signs['minus'].' | '.$signs['plus'].'</option>';
				} else {
					echo '<option value="'.$signsgroup.'">'.$signs['minus'].' | '.$signs['plus'].'</option>';
				}
			}
		echo '</select>';	
		echo ' <span class="description">'.__('If a list element has sub elements then there will be a symbol which lets the users expand or collapse the list of the sub elements.','custom-field-list-widget').'</span>'."\n";
	echo '</td>'."\n";
	echo '</tr>'."\n";
 
	echo '<tr valign="top">'."\n";
	echo '<th scope="row">'.__('effect speed','custom-field-list-widget').'</th>'."\n";
	echo '<td>';
		echo '<select id="customfieldlist_opt_effect_speed" name="customfieldlist_effect_speed">';
			foreach ($speeds as $speed_keyname => $speed_displayname) {
				if ($speed_keyname == $opt['effect_speed']) {
					echo '<option value="'.$speed_keyname.'" selected="selected">'.$speed_displayname.'</option>';
				} else {
					echo '<option value="'.$speed_keyname.'">'.$speed_displayname.'</option>';
				}
			}
		echo '</select>';	
		echo ' <span class="description">'.__('How fast should the list elements show up or hide?','custom-field-list-widget').'</span>'."\n";
	echo '</td>'."\n";
	echo '</tr>'."\n";

	echo '</table>'."\n";

	echo '<input type="hidden" name="action" value="update" />'."\n";

	echo '<p class="submit">'."\n";
	echo '<input type="submit" class="button-primary" value="'.__('Save Changes').'" />'."\n";
	echo '</p>'."\n";

	echo '</form>'."\n";
	echo '</div>'."\n";
}

add_action('admin_menu', 'customfieldlist_add_options_page');
function customfieldlist_add_options_page() {
	if (function_exists('add_options_page')) {
		add_options_page(__('Custom Field List Widgets','custom-field-list-widget'), __('Custom Field List','custom-field-list-widget'), 'manage_options', plugin_basename(__FILE__), 'customfieldlist_widget_general_options'); 
	}
}
?>
