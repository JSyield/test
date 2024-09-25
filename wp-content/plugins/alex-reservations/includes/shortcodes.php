<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//----------------------------------
// Shortcode Booking Form
//----------------------------------
add_shortcode('rr_form', 'alexr_restaurant_reservation_form');

// has_shortcode does not work well with page builders
//add_action('wp_enqueue_scripts', 'alexr_restaurant_reservation_form_scripts');

function alexr_restaurant_reservation_form( $atts, $content = null )
{
	// Prevent issues with elementor, beaver builder
	if (is_admin() || isset($_GET['fl_builder']) || wp_doing_ajax())
	{
		return '<div style="padding: 20px; background: #fadcb6; text-align: center;">Alex Reservations widget ID=' . $atts['id'] . ' will be displayed here.</div>';
	}

	$atts = shortcode_atts( array(
		'id' => 0, // Widget id
		'booking_uuid' => false, // Booking uuid to change
		'button' => __eva('Book now'),
		'mode' => 'service',
		'embed' => 'no',
		'hide_button' => 'no',
		// mode = time => user selects party,date,time
		// mode = service => user selects party,date,service,
	), $atts);

	extract($atts);

	$w_form = \Alexr\Settings\WidgetForm::where('id', $id)->first();
	if ($w_form){
		$restaurant_id = $w_form->restaurant_id;
	} else {
		$restaurant_id = 0;
	}

	// Add some random number to generate unique identifier for the button
	// so can put the same buttons 2 times in the same page
	$form_id = $id.'-'.\Evavel\Support\Str::upper(\Evavel\Support\Str::random(8));

	$html = '<div class="app-restaurant-form" 
		id="app-restaurant-form-' . $form_id .'"  
		data-embed="'.$embed.'" 
		data-nonce="'.evavel_tenant_create_nonce($restaurant_id).'" 
		data-widget="' . $id . '" 
		data-restaurant="' . $restaurant_id . '" 
		data-hide_button="' . $hide_button . '"
		data-button="' . $button . '" 
		data-mode="' . $mode .'"
		data-booking_uuid="'. $booking_uuid .'"
		></div>';

	$widget_dir  = ALEXR_PLUGIN_URL . 'assets/widget-mix/';
	$widget_version = ALEXR_VERSION;

	if (defined('ALEXR_PRO_PLUGIN_URL')) {
		$widget_dir = ALEXR_PRO_PLUGIN_URL . 'assets/widget-mix/';
	}
	if (defined('ALEXR_PRO_VERSION')) {
		$widget_version = ALEXR_PRO_VERSION;
	}

	$assets_url = ALEXR_PLUGIN_URL.'assets/';

	wp_enqueue_style( 'restaurant-form', $widget_dir.'index.css' , array(), $widget_version );
	alexr_enqueue_widget_custom_css($w_form, $widget_version);

	wp_enqueue_script('srr-windowsfet-js', $assets_url.'js/windowSfet.js', array(), ALEXR_VERSION, true);
	wp_enqueue_script('srr-crypto-js', $assets_url.'js/crypto-js.min.js', array(), ALEXR_VERSION, true);

	wp_enqueue_script( 'restaurant-form-main', $widget_dir . 'main.js', array('srr-windowsfet-js','srr-crypto-js'), $widget_version, true );

	wp_localize_script('restaurant-form-main', 'rr_config', alexr_get_config());
	wp_localize_script('restaurant-form-main', 'rr_translations', alexr_get_translations());

	do_action('alexr-shortcode-after-scripts', $w_form);

	return $html;
}


function alexr_enqueue_widget_custom_css($w_form, $version)
{
	if (!isset($w_form->form_config['custom_css'])) {
		return;
	}
	$custom_css = $w_form->form_config['custom_css'];

	if (is_string($custom_css) && strlen($custom_css) > 10) {
		wp_enqueue_style('restaurant-form-custom-css',$custom_css, array(), $version );
	}
}


/*
function alexr_restaurant_reservation_form_scripts()
{
	global $wp_version, $post;

	if ( !isset($post->post_content) ) return;

	$widget_dir  = ALEXR_PLUGIN_URL . 'assets/widget-mix/';
	$widget_version = ALEXR_VERSION;

	if (defined('ALEXR_PRO_PLUGIN_URL')) {
		$widget_dir = ALEXR_PRO_PLUGIN_URL . 'assets/widget-mix/';
	}
	if (defined('ALEXR_PRO_VERSION')) {
		$widget_version = ALEXR_PRO_VERSION;
	}

	if (has_shortcode($post->post_content, 'rr_form')) {

		wp_enqueue_style( 'restaurant-form', $widget_dir.'index.css' , array(), $widget_version );
		wp_enqueue_script( 'restaurant-form-main', $widget_dir . 'main.js', array( ), $widget_version, true );
		wp_localize_script('restaurant-form-main', 'rr_config', alexr_get_config());
		wp_localize_script('restaurant-form-main', 'rr_translations', alexr_get_translations());
	}
}
*/
