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
        \WP_Mock::setUp();
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

    public function testIfCurrentUserCanActivatePlugin(){
        $wPHelperProphecy = $this->_prophet->prophesize('WooCommerceToggleWPHelper');
        $wPHelperProphecy->current_user_can('activate_plugins')->willReturn(false);
        $woo_toggle = new WooCommerceToggle($wPHelperProphecy->reveal());
        $this->assertFalse($woo_toggle->currentUserCanActivatePlugin());
    }

    public function testIfGetThePostReturnsWPPostObject(){
        $wooCommerceToggle = new WooCommerceToggle(new WooCommerceToggleWPHelper());
        global $post;
        $post = new WP_Post((object)array('ID' =>-1));
        $this->assertInstanceOf('WP_Post', $wooCommerceToggle->getThePost());
    }

    public function testIfGetTheProductReturnsWCProductObject(){
        $wooCommerceToggle = new WooCommerceToggle(new WooCommerceToggleWPHelper());
        global $the_product;
        $the_product = new WC_Product();
        $this->assertInstanceOf('WC_Product', $wooCommerceToggle->getTheProduct());
    }

    public function testIfIsBackoffice()
    {
        $wPHelperProphecy = $this->_prophet->prophesize('WooCommerceToggleWPHelper');
        $wPHelperProphecy->is_admin()->willReturn(false);
        $woo_toggle = new WooCommerceToggle($wPHelperProphecy->reveal());
        $this->assertFalse($woo_toggle->isBackoffice());
    }

    public function tearDown() {
        \WP_Mock::tearDown();
    }

}

if(!class_exists('WC_Product')){
    class WC_Product{
        public function __construct()
        {
        }
    }
}
