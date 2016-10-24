<?php

require_once 'WooCommerceToggleWPHelper.php';

class WooCommerceToggle
{
    private $_wp_helper;

    public function __construct(WooCommerceToggleWPHelper $WPHelper)
    {
        $this->_wp_helper = $WPHelper;
        register_activation_hook(__FILE__, array($this, 'install'));
        add_action('admin_init', array($this, 'manageActivation'));
        add_filter('manage_edit-product_columns',array($this, 'addPublishToggleColumn'), 100);
        add_action('manage_product_posts_custom_column', array($this, 'addPublishToggleColumn'), 100);
        add_filter('manage_edit-product_sortable_columns', array($this, 'addPublishToggleColumn'), 100);
        add_action('wp_ajax_ev_product_visibility', array($this, 'controlAjaxProductVisibility'), 100);
        load_plugin_textdomain(WC_TOGGLE_TEXTDOMAIN, false, basename(dirname(__FILE__)) . '/languages');
    }

    public function install()
    {

    }

    public function manageActivation()
    {
        if (!$this->checkPreConditions()) {
            $this->deActivateThisPlugin();
            add_action('admin_notices', array($this, 'getDisabledNoticeAlertMessage'));
            $this->unsetActivateFromGetParams($_GET);
        }
    }

    public function currentUserCanActivatePlugin()
    {
        return $this->_wp_helper->current_user_can('activate_plugins');
    }

    public function isPluginActive($plugin_name)
    {
        return $this->_wp_helper->is_active_plugin($plugin_name);
    }

    public function isWooCommercePluginActive()
    {
        return $this->isPluginActive("woocommerce/woocommerce.php");
    }

    public function deActivateThisPlugin()
    {
        $this->deActivatePlugin(PLUGIN_NAME);
    }

    public function deActivatePlugin($plugin_name)
    {
        $this->_wp_helper->deactivate_plugins($plugin_name);
    }

    public function checkPreConditions()
    {
        return $this->isWooCommercePluginActive();
    }

    public function unsetActivateFromGetParams($get_params)
    {
        if (isset($get_params['activate'])) {
            unset($get_params['activate']);
        }
    }

    public function getDisabledNoticeAlertMessage()
    {
        echo '<div class="error" style="padding: 15px; background-color: mistyrose;"><strong>' . esc_html__('This plugin requires WooCommerce plugin active', WC_TOGGLE_TEXTDOMAIN) . '</strong></div>';
    }

    public function manageProductVisibility($post_id, $ajax = FALSE)
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

    public function getThePost(){
        global $post;
        return $post;
    }

    public function getTheProduct(){
        global $the_product;
        return $the_product;
    }

    public function isBackoffice(){
        return $this->_wp_helper->is_admin();
    }

    /**
     * Gestión de columnas añadidas por Evolufarma en la lista de productos
     * @param String $columns Columna actual
     * @param Integer $post_id
     * @return string
     */
    public function addPublishToggleColumn($columns)
    {
        $post = $this->getThePost();
        $the_product = $this->getTheProduct();

        if (!$this->isBackoffice()) {
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

    public function controlAjaxProductVisibility()
    {
        $post_id = isset($_GET['product_id']) && (int)$_GET['product_id'] ? (int)$_GET['product_id'] : '';
        $this->manageProductVisibility($post_id, TRUE);
    }
}