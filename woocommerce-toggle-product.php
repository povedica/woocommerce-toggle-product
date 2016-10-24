<?php
/*
Plugin Name: WooCommerce Easy Toggle Product
Plugin URI: http://wordpress.org/plugins/woocommerce-product-toggle/
Description: Just simple. Toggle your product visibility. Publish or Hidden. One click
Author: Pablo Poveda
Version: 1.0.0
Author URI: -
*/

define('WC_TOGGLE_TEXTDOMAIN', 'wc_toggle');
define('PUBLISH', 'publish');
define('HIDDEN', 'hidden');
require_once 'WooCommerceToggle.php';

new WooCommerceToggle(new WooCommerceToggleWPHelper());




