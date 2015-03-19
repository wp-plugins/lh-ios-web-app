<?php
/*
Plugin Name: LH Ios Web App
Plugin URI: http://lhero.org/plugins/lh-ios-web-app/
Description: Makes your wp site ios web app capable
Version: 0.10
Author: Peter Shaw
Author URI: http://shawfactor.com/
*/

define ( 'LH_IOS_WEB_APP_PLUGIN_URL', plugin_dir_url(__FILE__)); // with forward slash (/).


class LH_ios_web_app_plugin {

// variables for the field and option names 
var $lh_iphone_app_apple_touch_icon_field_name = 'apple-touch-icon';
var $lh_iphone_app_web_app_capable_field_name = 'apple-mobile-web-app-capable';
var $lh_iphone_app_title_field_name = 'apple-mobile-web-app-title';
var $lh_iphone_app_apple_touch_startup_image = 'apple-touch-startup-image';
var $lh_iphone_app_maintain_state_field_name = 'js_helper-maintain_state';
var $lh_iphone_app_show_webapp_prompt_field_name = 'js_helper-show_addtohome_prompt';
var $lh_iphone_app_opt_name = 'lh_iphone_web_app-options';
var $lh_iphone_app_apple_touch_icon_sizes;
var $lh_iphone_app_apple_touch_startup_image_sizes;



function create_startup_image_sizes() {


$touch_icon_sizes[0] = array('height' => '460','width' => '320');
$touch_icon_sizes[1] = array('height' => '920','width' => '640');
$touch_icon_sizes[2] = array('height' => '960','width' => '640');
$touch_icon_sizes[3] = array('height' => '1096', 'width' => '640');

return $touch_icon_sizes;

}

function create_touch_icon_sizes() {

$startup_image_sizes[0] = array('height' => '57','width' => '57');
$startup_image_sizes[1] = array('height' => '72', 'width' => '72');
$startup_image_sizes[2] = array('height' => '114', 'width' => '114');
$startup_image_sizes[3] = array('height' => '144', 'width' => '144');

return $startup_image_sizes;

}


function get_image_sizes( $size = '' ) {

        global $_wp_additional_image_sizes;

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach( $get_intermediate_image_sizes as $_size ) {

                if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

                        $sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
                        $sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
                        $sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

                } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

                        $sizes[ $_size ] = array( 
                                'width' => $_wp_additional_image_sizes[ $_size ]['width'],
                                'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                                'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
                        );

                }

        }

        // Get only 1 size if found
        if ( $size ) {

                if( isset( $sizes[ $size ] ) ) {
                        return $sizes[ $size ];
                } else {
                        return false;
                }

        }

        return $sizes;
}

function check_image_size($id,$size){
remove_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ) );

$imagedata = wp_get_attachment_image_src( $id, $size );

add_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ), 10, 3 );

if ($imagedata){

$size = $this->get_image_sizes($size);

if (($imagedata[1] == $size['width']) and ($imagedata[2] == $size['height'])){

return $imagedata[0];


}

} else {


return false;


}


}


function add_new_image_sizes_to_wp() {

foreach( $this->lh_iphone_app_apple_touch_icon_sizes as $size ){

add_image_size( $this->lh_iphone_app_apple_touch_icon_field_name.'_'.$size['width'].'x'.$size['height'], $size['width'], $size['height'], true ); 


}

foreach( $this->lh_iphone_app_apple_touch_startup_image_sizes as $size ){

add_image_size( $this->lh_iphone_app_apple_touch_startup_image.'_'.$size['width'].'x'.$size['height'], $size['width'], $size['height'], true ); 


}


}

function add_meta_to_head() {

$options  = get_option($this->lh_iphone_app_opt_name);



echo "\n<!-- Start LH ios Web App -->\n";



echo "<meta name=\"viewport\" content=\"width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;\" />\n";

if   ($options[$this->lh_iphone_app_title_field_name]){

echo "<meta name=\"apple-mobile-web-app-title\" content=\"".$options[$this->lh_iphone_app_title_field_name]."\" />\n";

}

if   ($options[$this->lh_iphone_app_web_app_capable_field_name] == 1){

echo "<meta name=\"apple-mobile-web-app-capable\" content=\"yes\" />\n";

}

foreach( $this->lh_iphone_app_apple_touch_icon_sizes as $size ){

if ($href = $this->check_image_size($options[$this->lh_iphone_app_apple_touch_icon_field_name], $this->lh_iphone_app_apple_touch_icon_field_name.'_'.$size['width'].'x'.$size['height'] )){

echo "<link rel=\"".$this->lh_iphone_app_apple_touch_icon_field_name."\" sizes=\"".$size['width']."x".$size['height']."\" href=\"".$href."\" />\n";


}

}

foreach( $this->lh_iphone_app_apple_touch_startup_image_sizes as $size ){

if ($href = $this->check_image_size($options[$this->lh_iphone_app_apple_touch_startup_image], $this->lh_iphone_app_apple_touch_startup_image.'_'.$size['width'].'x'.$size['height'] )){

echo "<link rel=\"".$this->lh_iphone_app_apple_touch_startup_image."\" sizes=\"".$size['width']."x".$size['height']."\" href=\"".$href."\" />\n";

}

}

echo "<meta name=\"apple-mobile-web-app-status-bar-style\" content=\"black\" />\n";

echo "<!-- End LH ios Web App -->\n\n";




}


function enqueue_scripts() {

$options = get_option($this->lh_iphone_app_opt_name);

if ($options[$this->lh_iphone_app_show_webapp_prompt_field_name] == 1){

wp_enqueue_script('lh_iphone_app-add_to_home_script', plugins_url( '/scripts/init_prompt.js' , __FILE__ ), array(), '0.01', true  );

}


if ($options[$this->lh_iphone_app_web_app_capable_field_name] == 1){

wp_enqueue_script('lh_iphone_app-web_app_capable', plugins_url( '/scripts/app_overrides.js' , __FILE__ ),array(), '0.01', true );


}


if ($options[$this->lh_iphone_app_maintain_state_field_name] == 1){

wp_enqueue_script('lh_iphone_app-maintain_state', plugins_url( '/scripts/app_state.js' , __FILE__ ),array(), '0.01', true );


}


}



// Now include admin GUI functions

// Prepare the media uploader
function add_admin_scripts(){

if (isset($_GET['page']) && $_GET['page'] == 'lh-ios_web-app-identifier') {
	// must be running 3.5+ to use color pickers and image upload
	wp_enqueue_media();
        wp_register_script('lh-ios-app-admin', LH_IOS_WEB_APP_PLUGIN_URL.'scripts/uploader.js', array('jquery','media-upload','thickbox'));
	wp_enqueue_script('lh-ios-app-admin');

}
}


function plugin_menu() {
add_options_page('LH Ios Web App Options', 'LH Ios Web App', 'manage_options', 'lh-ios_web-app-identifier', array($this,"plugin_options"));
}

function plugin_options() {

if (!current_user_can('manage_options')){

wp_die( __('You do not have sufficient permissions to access this page.') );

}




$lh_iphone_app_hidden_field_name = 'lh_iphone_app_submit_hidden';

if( isset($_POST[ $lh_iphone_app_hidden_field_name ]) && $_POST[ $lh_iphone_app_hidden_field_name ] == 'Y' ) {

        // Read their posted value




if ($_POST[ $this->lh_iphone_app_apple_touch_icon_field_name."-url" ] != ""){
$lh_iphone_app_options[ $this->lh_iphone_app_apple_touch_icon_field_name ] = $_POST[ $this->lh_iphone_app_apple_touch_icon_field_name ];
}

$lh_iphone_app_options[ $this->lh_iphone_app_web_app_capable_field_name ] = $_POST[ $this->lh_iphone_app_web_app_capable_field_name ];
$lh_iphone_app_options[ $this->lh_iphone_app_title_field_name ] = $_POST[ $this->lh_iphone_app_title_field_name];

if ($_POST[ $this->lh_iphone_app_apple_touch_startup_image."-url" ] != ""){
$lh_iphone_app_options[ $this->lh_iphone_app_apple_touch_startup_image ] = $_POST[ $this->lh_iphone_app_apple_touch_startup_image ];
}

$lh_iphone_app_options[ $this->lh_iphone_app_maintain_state_field_name ] = $_POST[ $this->lh_iphone_app_maintain_state_field_name ];
$lh_iphone_app_options[ $this->lh_iphone_app_show_webapp_prompt_field_name ] = $_POST[ $this->lh_iphone_app_show_webapp_prompt_field_name ];


// Save the posted value in the database
update_option( $this->lh_iphone_app_opt_name , $lh_iphone_app_options );


// Put an settings updated message on the screen


?>
<div class="updated"><p><strong><?php _e('settings saved.', 'lh-ios-web-app' ); ?></strong></p></div>
<?php


} else {

$lh_iphone_app_options  = get_option($this->lh_iphone_app_opt_name);


}


echo "<h2>" . __( 'LH Ios Web App Settings', 'lh-ios-web-app' ) . "</h2>";

?>


<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $lh_iphone_app_hidden_field_name; ?>" value="Y">


<p><?php _e("Touch Icon url:", 'lh-ios-web-app'); ?> 
<input type="hidden" name="<?php echo $this->lh_iphone_app_apple_touch_icon_field_name; ?>"  id="<?php echo $this->lh_iphone_app_apple_touch_icon_field_name; ?>" value="<?php echo $lh_iphone_app_options[$this->lh_iphone_app_apple_touch_icon_field_name]; ?>" size="10" />
<input type="url" name="<?php echo $this->lh_iphone_app_apple_touch_icon_field_name; ?>-url" id="<?php echo $this->lh_iphone_app_apple_touch_icon_field_name; ?>-url" value="<?php echo wp_get_attachment_url($lh_iphone_app_options[$this->lh_iphone_app_apple_touch_icon_field_name]); ?>" size="50" />
<input type="button" class="button" name="<?php echo $this->lh_iphone_app_apple_touch_icon_field_name; ?>-upload_button" id="<?php echo $this->lh_iphone_app_apple_touch_icon_field_name; ?>-upload_button" value="Upload/Select Image" />
</p>


<p><?php _e("Web App Capable:", 'lh-ios-web-app'); ?>
<select name="<?php echo $this->lh_iphone_app_web_app_capable_field_name; ?>" id="<?php echo $this->lh_iphone_app_web_app_capable_field_name; ?>">
<option value="1" <?php  if ($lh_iphone_app_options[$this->lh_iphone_app_web_app_capable_field_name] == 1){ echo 'selected="selected"'; }  ?>>Yes</option>
<option value="0" <?php  if ($lh_iphone_app_options[$this->lh_iphone_app_web_app_capable_field_name] == 0){ echo 'selected="selected"';}  ?>>No</option>
</select>

<?php   if ($lh_iphone_app_options[$this->lh_iphone_app_web_app_capable_field_name] == 1){   ?>

<p><?php _e('Title of your Web App: ', 'lh-ios-web-app'); ?>
<input type="text" name="<?php echo $this->lh_iphone_app_title_field_name; ?>" id="<?php echo $this->lh_iphone_app_title_field_name; ?>" value="<?php echo $lh_iphone_app_options[$this->lh_iphone_app_title_field_name]; ?>"  />
(<a href="http://lhero.org/plugins/lh-ios-web-app/#<?php echo $this->lh_iphone_app_title_field_name; ?>">What does this mean?</a>)
</p>




<p><?php _e("Startup Image url: ", 'lh-ios-web-app'); ?> 
<input type="hidden" name="<?php echo $this->lh_iphone_app_apple_touch_startup_image; ?>"  id="<?php echo $this->lh_iphone_app_apple_touch_startup_image; ?>" value="<?php echo $lh_iphone_app_options[$this->lh_iphone_app_apple_touch_startup_image]; ?>" size="10" />
<input type="url" name="<?php echo $this->lh_iphone_app_apple_touch_startup_image; ?>-url" id="<?php echo $this->lh_iphone_app_apple_touch_startup_image; ?>-url" value="<?php echo wp_get_attachment_url($lh_iphone_app_options[$this->lh_iphone_app_apple_touch_startup_image]); ?>" size="50" />
<input type="button" class="button" name="<?php echo $this->lh_iphone_app_apple_touch_startup_image; ?>-upload_button" id="<?php echo $this->lh_iphone_app_apple_touch_startup_image; ?>-upload_button" value="Upload/Select Image" />
</p>


<p><?php _e("Maintain App State: ", 'lh-ios-web-app'); ?>
<select name="<?php echo $this->lh_iphone_app_maintain_state_field_name; ?>" id="<?php echo $this->lh_iphone_app_maintain_state_field_name; ?>">
<option value="1" <?php  if ($lh_iphone_app_options[$this->lh_iphone_app_maintain_state_field_name] == 1){ echo 'selected="selected"'; }  ?>>Yes</option>
<option value="0" <?php  if ($lh_iphone_app_options[$this->lh_iphone_app_maintain_state_field_name] == 0){ echo 'selected="selected"';}  ?>>No</option>
</select>
(<a href="http://lhero.org/plugins/lh-ios-web-app/#<?php echo $this->lh_iphone_app_maintain_state_field_name; ?>">What does this mean?</a>)
</p>


<p>
<?php _e("Show Web App Prompt:", 'lh-ios-web-app'); ?>
<select name="<?php echo $this->lh_iphone_app_show_webapp_prompt_field_name; ?>" id="<?php echo $this->lh_iphone_app_show_webapp_prompt_field_name; ?>">
<option value="1" <?php  if ($lh_iphone_app_options[$this->lh_iphone_app_show_webapp_prompt_field_name] == 1){ echo 'selected="selected"'; }  ?>>Yes</option>
<option value="0" <?php  if ($lh_iphone_app_options[$this->lh_iphone_app_show_webapp_prompt_field_name] == 0){ echo 'selected="selected"';}  ?>>No</option>
</select>
(<a href="http://lhero.org/plugins/lh-ios-web-app/#<?php echo $this->lh_iphone_app_show_webapp_prompt_field_name; ?>">What does this mean?</a>)
</p>

<?php  }  ?>

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>
</form>

<?php



}


function __construct() {

$this->lh_iphone_app_apple_touch_icon_sizes = $this->create_touch_icon_sizes();

$this->lh_iphone_app_apple_touch_startup_image_sizes = $this->create_startup_image_sizes();

add_action( 'init', array($this,"add_new_image_sizes_to_wp"));

add_action('wp_head', array($this,"add_meta_to_head"));

add_action( 'wp_enqueue_scripts', array($this,"enqueue_scripts"));

add_action('admin_enqueue_scripts', array($this,"add_admin_scripts"));

add_action('admin_menu', array($this,"plugin_menu"));


}


}

$lh_ios_web_app = new LH_ios_web_app_plugin();

?>