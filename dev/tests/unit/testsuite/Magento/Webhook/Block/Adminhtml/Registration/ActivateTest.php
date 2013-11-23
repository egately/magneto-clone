<?php
/**
 * \Magento\Webhook\Block\Adminhtml\Registration\Activate
 *
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Block\Adminhtml\Registration;

class ActivateTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Webhook\Block\Adminhtml\Registration\Activate */
    private $_block;

    /** @var \Magento\Core\Model\Url */
    private $_urlBuilder;

    /** @var array  */
    private $_subscription = array(
        'subscription_id' => 333,
        'name' => 'test_subscription',
        'topics' => array('customer/created', 'customer/updated')
    );

    protected function setUp()
    {
        $this->_urlBuilder = $this->getMock('Magento\UrlInterface');

        /** @var  $coreData \Magento\Core\Helper\Data */
        $coreData = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);

        $registry = $this->getMock('Magento\Core\Model\Registry', array('registry'), array(), '', false);
        $registry->expects($this->once())
            ->method('registry')
            ->with('current_subscription')
            ->will($this->returnValue($this->_subscription));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_block = $helper->getObject('\Magento\Webhook\Block\Adminhtml\Registration\Activate',
            array(
                'coreData' => $coreData,
                'registry' => $registry,
                'urlBuilder' => $this->_urlBuilder
            )
        );
    }

    public function testGetAcceptUrl()
    {
        $url = 'example.url.com/id/333';
        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('adminhtml/*/accept', array('id' => 333))
            ->will($this->returnValue($url));

        $this->assertEquals($url, $this->_block->getAcceptUrl());
    }

    public function testGetSubscriptionName()
    {
        $this->assertEquals($this->_subscription['name'], $this->_block->getSubscriptionName());
    }

    public function testGetSubscriptionTopics()
    {
        $this->assertEquals($this->_subscription['topics'], $this->_block->getSubscriptionTopics());
    }
}
