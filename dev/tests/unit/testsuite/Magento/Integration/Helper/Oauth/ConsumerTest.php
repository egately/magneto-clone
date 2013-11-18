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

namespace Magento\Integration\Helper\Oauth;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Core\Model\StoreManagerInterface */
    protected $_storeManagerMock;

    /** @var \Magento\Integration\Model\Oauth\Consumer\Factory */
    protected $_consumerFactory;

    /** @var \Magento\Integration\Model\Oauth\Consumer */
    protected $_consumerMock;

    /** @var \Magento\HTTP\ZendClient */
    protected $_httpClientMock;

    /** @var \Magento\Integration\Model\Oauth\Token\Factory */
    protected $_tokenFactory;

    /** @var \Magento\Integration\Model\Oauth\Token */
    protected $_tokenMock;

    /** @var \Magento\Core\Model\Store */
    protected $_storeMock;

    /** @var \Magento\Integration\Helper\Oauth\Data */
    protected $_dataHelper;

    /** @var \Magento\Integration\Helper\Oauth\Consumer */
    protected $_consumerHelper;

    /** @var \Magento\Logger */
    protected $_loggerMock;

    protected function setUp()
    {
        $this->_consumerFactory = $this->getMockBuilder('Magento\Integration\Model\Oauth\Consumer\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_consumerMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\Consumer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_consumerFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_consumerMock));

        $this->_tokenFactory = $this->getMockBuilder('Magento\Integration\Model\Oauth\Token\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_tokenMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\Token')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_tokenFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_tokenMock));

        $this->_storeManagerMock = $this->getMockBuilder('Magento\Core\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_storeMock = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->_storeMock));

        $this->_dataHelper = $this->getMockBuilder('Magento\Integration\Helper\Oauth\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_httpClientMock = $this->getMockBuilder('Magento\HTTP\ZendClient')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_loggerMock = $this->getMockBuilder('Magento\Logger')
            ->disableOriginalConstructor()
            ->setMethods(array('logException'))
            ->getMock();

        $this->_consumerHelper = new \Magento\Integration\Helper\Oauth\Consumer(
            $this->_storeManagerMock,
            $this->_consumerFactory,
            $this->_tokenFactory,
            $this->_dataHelper,
            $this->_httpClientMock,
            $this->_loggerMock
        );
    }

    protected function tearDown()
    {
        unset($this->_storeManagerMock);
        unset($this->_consumerFactory);
        unset($this->_tokenFactory);
        unset($this->_dataHelper);
        unset($this->_httpClientMock);
        unset($this->_loggerMock);
        unset($this->_consumerHelper);
    }

    public function testCreateConsumer()
    {
        $key = $this->_generateRandomString(\Magento\Oauth\Helper\Oauth::LENGTH_CONSUMER_KEY);
        $secret = $this->_generateRandomString(\Magento\Oauth\Helper\Oauth::LENGTH_CONSUMER_SECRET);

        $consumerData = array(
            'name' => 'Integration Name',
            'key' => $key,
            'secret' => $secret
        );

        $this->_consumerMock->expects($this->once())
            ->method('save')
            ->will($this->returnSelf());
        $this->_consumerMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($consumerData));

        $responseData = $this->_consumerHelper->createConsumer($consumerData);

        $this->assertEquals($key, $responseData['key'], 'Checking Oauth Consumer Key');
        $this->assertEquals($secret, $responseData['secret'], 'Checking Oauth Consumer Secret');
    }

    public function testPostToConsumer()
    {
        $consumerId = 1;

        $key = $this->_generateRandomString(\Magento\Oauth\Helper\Oauth::LENGTH_CONSUMER_KEY);
        $secret = $this->_generateRandomString(\Magento\Oauth\Helper\Oauth::LENGTH_CONSUMER_SECRET);
        $oauthVerifier = $this->_generateRandomString(\Magento\Oauth\Helper\Oauth::LENGTH_TOKEN_VERIFIER);

        $consumerData = array(
            'entity_id' => $consumerId,
            'key' => $key,
            'secret' => $secret
        );

        $this->_consumerMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo($consumerId))
            ->will($this->returnSelf());
        $this->_consumerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($consumerId));
        $this->_consumerMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($consumerData));
        $this->_httpClientMock->expects($this->once())
            ->method('setUri')
            ->with('http://www.magento.com')
            ->will($this->returnSelf());
        $this->_httpClientMock->expects($this->once())
            ->method('setParameterPost')
            ->will($this->returnSelf());
        $this->_tokenMock->expects($this->once())
            ->method('createVerifierToken')
            ->with($consumerId)
            ->will($this->returnSelf());
        $this->_tokenMock->expects($this->any())
            ->method('getVerifier')
            ->will($this->returnValue($oauthVerifier));
        $this->_dataHelper->expects($this->once())
            ->method('getConsumerPostMaxRedirects')
            ->will($this->returnValue(5));
        $this->_dataHelper->expects($this->once())
            ->method('getConsumerPostTimeout')
            ->will($this->returnValue(120));

        $verifier = $this->_consumerHelper->postToConsumer($consumerId, 'http://www.magento.com');

        $this->assertEquals($oauthVerifier, $verifier, 'Checking Oauth Verifier');
    }

    private function _generateRandomString($length)
    {
        return substr(
            str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, $length
        );
    }
}
