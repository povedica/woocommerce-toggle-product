<?php
/**
 * Class SampleTest
 *
 * @package Woocommerce_Toggle_Product
 */
require_once dirname(__FILE__) . '/../vendor/autoload.php';

/**
 * Sample test case.
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
     * A single example test.
     */
    function test_sample()
    {
        // Replace this with some actual testing code.
        $this->assertTrue(true);
    }

    /**
     * A single example test.
     */
    function testWooCommerceIsActive()
    {
        $myWPHelperProphecy = $this->_prophet->prophesize('WooCommerceToggleWPHelper');
        $collaboratorResponse = true;
        /** @var WooCommerceToggleWPHelper $myWPHelperProphecy */
        $myWPHelperProphecy->is_active_plugin("woocommerce/woocommerce.php")->willReturn($collaboratorResponse);
        $myWPHelper = $myWPHelperProphecy->reveal();
        $woo_toggle = new WooCommerceToggle($myWPHelper);
        $this->assertTrue($woo_toggle->isWooCommercePluginActive());
    }
}