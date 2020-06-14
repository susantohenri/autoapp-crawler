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
    echo $superCarRos->test ();
}

?>