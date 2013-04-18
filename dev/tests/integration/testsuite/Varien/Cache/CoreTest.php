<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Varien_Cache_Core test case
 */
class Varien_Cache_CoreTest extends PHPUnit_Framework_TestCase
{
    public function testSetBackendSuccess()
    {
        $mockBackend = $this->getMock('Zend_Cache_Backend_File');
        $config = array(
            'backend_decorators' => array(
                'test_decorator' => array(
                    'class' => 'Magento_Cache_Backend_Decorator_Compression',
                    'options' => array(
                        'compression_threshold' => '100',
                    )
                )
            )
        );

        $core = new Varien_Cache_Core($config);
        $core->setBackend($mockBackend);

        $this->assertInstanceOf('Magento_Cache_Backend_Decorator_DecoratorAbstract', $core->getBackend());
    }

    /**
     * @expectedException Zend_Cache_Exception
     */
    public function testSetBackendException()
    {
        $mockBackend = $this->getMock('Zend_Cache_Backend_File');
        $config = array(
            'backend_decorators' => array(
                'test_decorator' => array(
                    'class' => 'Zend_Cache_Backend',
                )
            )
        );

        $core = new Varien_Cache_Core($config);
        $core->setBackend($mockBackend);
    }
}
