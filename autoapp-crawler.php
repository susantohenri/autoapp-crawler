<?php 
/*
Plugin Name: AutoApp Crawler
URI: autoapp.do
Description: Data Crawler for AutoApp
Author: henrisusanto 
Version: 1.0
Author URI: https://github.com/susantohenri/autoapp-crawler
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

include plugin_dir_path( __FILE__ ) . 'page.php';
include plugin_dir_path( __FILE__ ) . 'supercarros.php';

function autoapp_crawler_shortcode () {
	ob_start ();
	if ( isset( $_POST['autoapp_crawler_submit'] ) ) autoapp_crawler_form_submission_handler ();
	autoapp_crawler_form ();
	return ob_get_clean();
}

add_shortcode( 'autoapp_crawler_url_submit_form', 'autoapp_crawler_shortcode' );

function autoapp_crawler_form () {
    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="POST" enctype="multipart/form-data">';
    echo "
        <p>
            <label>URL to crawl</label>
            <input type=\"text\" name=\"autoapp_crawler_url\" size=\"40\" {$required} />
        </p>
    ";
    echo "<input type=\"submit\" value=\"Crawl URL\" name=\"autoapp_crawler_submit\" >";
    echo '</form>';
}

function autoapp_crawler_form_submission_handler () {
    $url = $_POST['autoapp_crawler_url'];
	$superCarRos = new SuperCarRos ($url);
	// echo $superCarRos->test();
    foreach ($superCarRos->getCars () as $car) autoapp_crawler_create_post ($car);
}

function autoapp_crawler_create_post ($car) {
    $my_post = array(
        'post_title'    => wp_strip_all_tags($car['car_name']),
        'post_content'  => '',
        'post_status'   => 'draft',
        'post_type' => 'listings'
    );
	$post_id = wp_insert_post( $my_post );
	// $media_id = autoapp_crawler_insert_attachment_from_url ($car['car_photos'][0], $post_id);
    // foreach ($car['car_photos'] as $src) {
    //     autoapp_crawler_insert_attachment_from_url ($src, $post_id);
	// }
	$attributes = array ('car_name', 'car_price', 'car_body', 'car_mileage', 'car_fueltype', 'car_engine', 'car_transmission', 'car_drive', 'car_exterior_color', 'car_interior_color');
	foreach ($attributes as $field) {
		add_post_meta( $post_id, str_replace ('car_', '', $field), $car[$field]);
	}
	add_post_meta( $post_id, 'stm_car_location', $car['car_address']);
	add_post_meta( $post_id, 'stm_lat_car_admin', $car['car_lat']);
	add_post_meta( $post_id, 'stm_lng_car_admin', $car['car_lng']);
}

function autoapp_crawler_insert_attachment_from_url($url, $parent_post_id = null) {

	if( !class_exists( 'WP_Http' ) )
		include_once( ABSPATH . WPINC . '/class-http.php' );

	$http = new WP_Http();
	$response = $http->request( $url );
	if( $response['response']['code'] != 200 ) {
		return false;
	}

	$upload = wp_upload_bits( basename($url), null, $response['body'] );
	if( !empty( $upload['error'] ) ) {
		return false;
	}

	$file_path = $upload['file'];
	$file_name = basename( $file_path );
	$file_type = wp_check_filetype( $file_name, null );
	$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
	$wp_upload_dir = wp_upload_dir();

	$post_info = array(
		'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
		'post_mime_type' => $file_type['type'],
		'post_title'     => $attachment_title,
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	// Create the attachment
	$attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );

	// Include image.php
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	// Define attachment metadata
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

	// Assign metadata to attachment
	wp_update_attachment_metadata( $attach_id,  $attach_data );

	return $attach_id;
}

?>