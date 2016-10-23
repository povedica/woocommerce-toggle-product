<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';

/**
 * Class WooCommerceToggleTest
 *
 * @package Woocommerce_Toggle_Product
 */
class WooCommerceToggleTest extends WP_UnitTestCase
{
    private $_prophet;

    function setup()
    {
        require_once dirname(__FILE__) . '/../woocommerce-toggle-product.php';
        $this->_prophet = new Prophecy\Prophet();
    }

    /**
     * Test if WooCommerce plugin is Active
     * @test testIfWooCommerceIsActive
     */
    public function testIfWooCommerceIsActive()
    {
        $wPHelperProphecy = $this->_prophet->prophesize('WooCommerceToggleWPHelper');
        /** @var WooCommerceToggleWPHelper $wPHelperProphecy */
        $wPHelperProphecy->is_active_plugin("woocommerce/woocommerce.php")->willReturn(true);
        $woo_toggle = new WooCommerceToggle($wPHelperProphecy->reveal());
        $this->assertTrue($woo_toggle->isWooCommercePluginActive());
    }

    public function testIfDesactivatePluginWorks()
    {
        $wooCommerceToggle = new WooCommerceToggle(new WooCommerceToggleWPHelper());
        $wooCommerceToggle->deActivatePlugin("woocommerce-toggle-product/woocommerce-toggle-product.php");
        $this->assertFalse($wooCommerceToggle->isPluginActive("woocommerce-toggle-product/woocommerce-toggle-product.php"));
    }

    public function testIfUnsetPluginFromGetParams(){
        $arr = array('activate' => '');
        $wooCommerceToggle = new WooCommerceToggle(new WooCommerceToggleWPHelper());
        $arr = $wooCommerceToggle->unsetActivateFromGetParams($arr);
        $this->assertEquals(false,isset($arr['activate']));
    }

    /*
    public function testIfPreConditionsWorks()
    {
        $wPHelperProphecy = $this->_prophet->prophesize('WooCommerceToggleWPHelper');
        $woo_toggle = new WooCommerceToggle($wPHelperProphecy->reveal());
        $this->assertTrue($woo_toggle->checkPreConditions());
    }
    */
}