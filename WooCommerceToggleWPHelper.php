<?php

/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 23/10/16
 * Time: 12:37
 */
class WooCommerceToggleWPHelper
{
    public function __construct()
    {
    }

    public function is_active_plugin($plugin_name)
    {
        return is_plugin_active($plugin_name);
    }
}