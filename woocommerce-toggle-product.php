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
require_once 'WooCommerceToggleWPHelper.php';

class WooCommerceToggle
{
    private $_wp_helper;

    public function __construct(WooCommerceToggleWPHelper $WPHelper)
    {
        load_plugin_textdomain(WC_TOGGLE_TEXTDOMAIN, false, basename(dirname(__FILE__)) . '/languages');
        $this->_wp_helper = $WPHelper;
    }

    public function isWooCommercePluginActive()
    {
        return $this->_wp_helper->is_active_plugin("woocommerce/woocommerce.php");
    }

    function checkPreConditions()
    {
        $pre_conditions_ok = true;
        if (!checkRequisites()) {
            deactivate_plugins(plugin_basename(__FILE__));
            $pre_conditions_ok = false;
        }

        return $pre_conditions_ok;
    }
}

// The backup sanity check, in case the plugin is activated in a weird way,
// or the versions change after activation.
function checkRequisites()
{
    $ok = true;
    if (!isWooCommercePluginActive()) {
        add_action('admin_notices', 'disabled_notice');
        if (is_plugin_active(plugin_basename(__FILE__))) {
            deactivate_plugins(plugin_basename(__FILE__));
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }
        $ok = false;
    }

    return $ok;
}

function disabled_notice()
{
    echo '<div class="error" style="padding: 15px; background-color: mistyrose;"><strong>' . esc_html__('This plugin requires WooCommerce plugin active', WC_TOGGLE_TEXTDOMAIN) . '</strong></div>';
}


function manageProductVisibility($post_id, $ajax = FALSE)
{
    if (!$ajax) {
        if ((get_post_type($post_id) == 'product') && (get_post_status($post_id) == 'publish') && !(isset($_GET['ev_product_visibility']))) {
            update_post_meta($post_id, '_visibility', 'visible');
        }
    } else {
        if (!current_user_can('edit_products'))
            wp_die(__('You do not have sufficient permissions to access this page.', 'woocommerce'));

        if (!check_admin_referer('ev-product-visibility'))
            wp_die(__('You have taken too long. Please go back and retry.', 'woocommerce'));

        if (!$post_id)
            die;

        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'product')
            die;

        $status = get_post_status($post->ID);
        /**
         * Posibles valores:
         *  hidden
         *  search
         *  visible
         */
        if ($status == 'pending') {
            update_post_meta($post->ID, '_visibility', 'visible');
            wp_update_post(array('ID' => $post->ID, 'post_status' => 'publish'));
        } else {
            update_post_meta($post->ID, '_visibility', 'hidden');
            wp_update_post(array('ID' => $post->ID, 'post_status' => 'pending'));
        }

        wp_safe_redirect(remove_query_arg(array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer()));
        die();
    }
}

/**
 * Gestión de columnas añadidas por Evolufarma en la lista de productos
 * @param String $columns Columna actual
 * @param Integer $post_id
 * @return string
 */
function addPublishToggleColumn($columns)
{
    global $post, $the_product;

    if (!is_admin()) {
        return;
    }

    if (empty($the_product) || $the_product->id != $post->ID) {
        $the_product = get_product($post, array('product_type' => 'product'));
    }

    if (is_array($columns)) {
        $columns[PUBLISH] = __('Publish', WC_TOGGLE_TEXTDOMAIN);
        return $columns;
    } else {
        switch ($columns) {
            case PUBLISH:
                $url = wp_nonce_url(admin_url('admin-ajax.php?action=ev_product_visibility&product_id=' . $post->ID), 'ev-product-visibility');
                echo '<a href="' . esc_url($url) . '" title="' . __('Publish/Hide product', WC_TOGGLE_TEXTDOMAIN) . '" alt="' . __('Publish/Hide product', WC_TOGGLE_TEXTDOMAIN) . '">';
                echo ($the_product->is_visible()) ? '<span style="border-radius:10px;background-color:green;color:white;"class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-dismiss" style="color: red;"></span>';
                echo '</a>';
                break;
        }
    }
    return $columns;
}

function controlAjaxProductVisibility()
{
    $post_id = isset($_GET['product_id']) && (int)$_GET['product_id'] ? (int)$_GET['product_id'] : '';
    manageProductVisibility($post_id, TRUE);
}

register_activation_hook(__FILE__, 'activation_check');
add_action('admin_init', 'checkRequisites');
add_filter('manage_edit-product_columns', 'addPublishToggleColumn', 100);
add_action('manage_product_posts_custom_column', 'addPublishToggleColumn', 100);
add_filter('manage_edit-product_sortable_columns', 'addPublishToggleColumn', 100);
add_action('wp_ajax_ev_product_visibility', 'controlAjaxProductVisibility', 100);