<?php
/*
Custom Field List Widget for K2

If you are using the "K2 Disable Widgets" plugin then 
copy this file to the folder /wp-content/themes/k2/app/modules/

file version: 0.1
*/

/*  Copyright 2009  Tim Berger  (email : timberge@cs.tu-berlin.de)

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
*/

if (function_exists('customfieldlist') AND function_exists('customfieldlist_widget_control')) {
	register_sidebar_module(__('Custom Field List','customfieldlist'), 'customfieldlist');
	register_sidebar_module_control(__('Custom Field List','customfieldlist'), 'customfieldlist_widget_control');
}
?>