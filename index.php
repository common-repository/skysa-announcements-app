<?php
/*
Plugin Name: Skysa Announcements App
Plugin URI: http://wordpress.org/extend/plugins/skysa-announcements-app
Description: An app which can display rich Announcements with automatic expiration.
Version: 1.10
Author: Skysa
Author URI: http://www.skysa.com
*/

/*
*************************************************************
*                 This app was made using the:              *
*                       Skysa App SDK                       *
*    http://wordpress.org/extend/plugins/skysa-app-sdk/     *
*************************************************************
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
MA  02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) exit;

// Skysa App plugins require the skysa-req subdirectory,
// and the index file in that directory to be included.
// Here is where we make sure it is included in the project.
include_once dirname( __FILE__ ) . '/skysa-required/index.php';


// ANNOUNCEMENTS APP
$GLOBALS['SkysaApps']->RegisterApp(array( //Describer array for the app
    'id' => '501ae5e0a5954',
    'label' => 'Announcements',
	'options' => array( 
		'bar_label' => array( 
            'label' => 'Button Label',
			'info' => 'What would you like the bar link label name to be?',
			'type' => 'text',
			'value' => 'Announcements',
			'size' => '30|1'
		),
        'icon' => array(
            'label' => 'Button Icon URL',
            'info' => 'Enter a URL for the an Icon Image. (You can leave this blank for none)',
			'type' => 'image',
			'value' => plugins_url( '/icons/announcements-icon-wp.png', __FILE__ ), // pull in the default icon URL for a local icon file included with the plugin.
			'size' => '50|1'
        ),
        'title' => array(
            'label' => 'App Title',
            'info' => 'What would you like to set as the title for the Announcements window?',
			'type' => 'text',
			'value' => 'Site Announcements',
			'size' => '30|1'
        ),
        'option1' => array(
            'label' => 'Auto Popup for New Announcements',
            'info' => 'Would you like this App to Popup for your visitor when you have a new announcement which they have not yet seen?',
			'type' => 'selectbox',
			'value' => 'Yes|No',
			'size' => '10|1'
        )
	),
    'window' => array(
        'width' => '350',
        'height' => '250',
        'position' => 'Page Center'
    ),
    'manage' => array(
        'label' => 'Announcements', 
        'add_label' => 'Add Announcement', 
        'records' => array( 
            'subject' => array(
			    'label' => 'Subject',
			    'type' => 'text',
			    'value' => '',
			    'size' => '30|1'
		    ),
            'body' => array(
			    'label' => 'Your Message',
			    'type' => 'editor',
			    'value' => '',
			    'size' => '50|6'
		    ),
            'expires' => array(
                'label' => 'Expiration Date',
			    'info' => 'Date to display the announcement until.',
			    'type' => 'date',
			    'value' => date("m/d/Y",mktime(0,0,0,date("m"),date("d")+7,date("Y"))), // Set the default value to 1 week from today
			    'size' => '8|1'
		    )
        )
    ),
    'fvars' => array(
        'created' => skysa_app_announcements_fvar_created
    ),
    'views' => array(
        'main' => skysa_app_announcements_view_main
    ), 
    'html' => '<div id="$button_id" class="bar-button" time="#fvar_created" apptitle="$app_title" w="$app_width" h="$app_height" bar="$app_position">$app_icon<span class="label">$app_bar_label</span></div>',
    'js' => "
        S.on('click',function(){S.open('window','main')}); 
        S.load('cssStr','.SKYUI-announcement { margin-bottom: 10px;} .SKYUI-announcement h3 { font-size: 20px; margin-bottom: 5px;} .SKYUI-announcement h3 .SKYUI-time { font-size: 12px; display: block;}');
     "
));

// Main Announcements App View function
function skysa_app_announcements_view_main($rec){
    $str = ''; // View functions must return a string. Start the string here.
    if($rec['content'] && count($rec['content']) > 0){ // Check if any manage records (announcements) have been added.
        foreach( $rec['content'] as $created => $item ){ // Loop through all announcements. The created time is the item key.
            $exp = strtotime($item->expires); // Get the expiration date as a time string for comparison.
            if($exp > time()){ // If the announcement has not expired, add the HTML for it to the string.
                $str .= '<div class="SKYUI-announcement">
                    <h3>' . $item->subject . ' <span class="SKYUI-time">' . date("F j, Y g:i A",$created+ (get_option( 'gmt_offset' )*3600)) . '</span></h3>
                    <div>' . $item->body .'</div>
                </div>';
            }
        }
    }
    if($str == ''){ // If there are not any active announcments display a message to that effect.
        $str = 'There are no active announcements.';
    }
    return $str; // Return the string for display.
}

// Announcements Created Function Variable
function skysa_app_announcements_fvar_created($rec){
    if($rec['content'] && count($rec['content']) > 0 && $rec['option1'] == 'Yes'){ // Check for any manage records (announcements) and check if the popup option is set to Yes.
        foreach( $rec['content'] as $created => $item ){ 
            $exp = strtotime($item->expires);
            if($exp > time()){
                return $created+ (get_option( 'gmt_offset' )*3600); // return th time string for the most recently added active announcement.
                break;
            }
        }
    }
    return 0; // If not, return 0, the announcements app will not pop up.
}
?>