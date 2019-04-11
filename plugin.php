<?php
/**
 * Plugin Name: Transport Model [Post Type]
 * Plugin URI: https://www.bonseo.es/
 * Description: Modelo de Transport
 * Author: jjlmoya
 * Author URI: https://www.bonseo.es/
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * @package BS
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once plugin_dir_path(__FILE__) . '/Transport.php';
function bs_transport_get_post_type()
{
	return Transport::getInstance('Transporte', 'Transportes', "transporte-y-conexiones",
		array(
			"distance" => array(
				"name" => "Distancia al Aeropuerto",
				"value" => "distance",
				"input" => "number"
			),
			"price" => array(
				"name" => "Precio desde Aeropuerto",
				"value" => "price",
				"input" => "number"
			),
			"time" => array(
				"name" => "Tiempo al/a Aeropuerto",
				"value" => "time",
				"input" => "number"
			)
		)
	);
}

function bs_transport_register_post_type()
{
	$model = bs_transport_get_post_type();
	$labels = array(
		"name" => __($model->plural, "custom-post-type-ui"),
		"singular_name" => __($model->singular, "custom-post-type-ui"),
	);

	$args = array(
		"label" => __($model->plural, "custom-post-type-ui"),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"delete_with_user" => false,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		'menu_icon' => $model->icon,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array("slug" => $model->path, "with_front" => true),
		"query_var" => true,
		"supports" =>
			array("title",
				"editor",
				"thumbnail",
				"custom-fields",
				"excerpt"),
	);

	register_post_type($model->db, $args);
}

function bs_transport_create_custom_params()
{
	$model = bs_transport_get_post_type();
	foreach ($model->customFields as $customField) {
		add_action('add_meta_boxes', $model->nameSpace . '_' . $customField["value"] . '_register');
	}
}

function bs_transport_register($customType)
{
	$model = bs_transport_get_post_type();
	$customField = $model->customFields;
	$customField = $customField[$customType];
	add_meta_box(
		$model->db . '_' . $customField['value'],
		$customField['name'],
		$model->nameSpace . '_' . $customField['value'] . '_callback',
		$model->db,
		'side',
		'high'
	);

}

function bs_transport_callback($fieldType)
{
	$model = bs_transport_get_post_type();
	$customField = $model->customFields;
	$customField = $customField[$fieldType];
	$dbEntry = $model->db . '_' . $customField['value'];
	global $post;
	wp_nonce_field(basename(__FILE__), $dbEntry);
	$value = get_post_meta($post->ID, $dbEntry, true);
	echo '<input type="' . $customField['input'] . '" name="' . $dbEntry . '" value="' . esc_textarea($value) . '" class="widefat">';
}

function bs_transport_on_save($post_id)
{

	$model = bs_transport_get_post_type();

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (isset($_POST['post_type']) && $_POST['post_type'] == $model->db) {
		if (!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	}
	foreach ($model->customFields as $customField) {
		$customFieldEntry = $model->db . '_' . $customField['value'];
		if (!isset($_POST[$customFieldEntry])) {
			return;
		}
		$myValue = sanitize_text_field($_POST[$customFieldEntry]);
		update_post_meta($post_id, $customFieldEntry, $myValue);
	}
}

add_action('init', 'bs_transport_register_post_type');
add_action('save_post', 'bs_transport_on_save');
bs_transport_create_custom_params();


function bs_transport_price_register()
{
	bs_transport_register('price');
}

function bs_transport_price_callback()
{
	bs_transport_callback('price');
}


function bs_transport_time_register()
{
	bs_transport_register('time');
}

function bs_transport_time_callback()
{
	bs_transport_callback('time');
}

function bs_transport_distance_register()
{
	bs_transport_register('distance');
}

function bs_transport_distance_callback()
{
	bs_transport_callback('distance');
}
