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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * oAuth token controller
 */
namespace Magento\Integration\Controller;

class Token extends \Magento\Core\Controller\Front\Action
{
    /** @var  \Magento\Oauth\OauthInterface */
    protected $_oauthService;

    /** @var  \Magento\Oauth\Helper\Request */
    protected $_helper;

    /**
     * @param \Magento\Oauth\OauthInterface $oauthService
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Oauth\Helper\Request $helper
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Oauth\OauthInterface $oauthService,
        \Magento\Oauth\Helper\Request $helper
    ) {
        parent::__construct($context);
        $this->_oauthService = $oauthService;
        $this->_helper = $helper;
    }

    /**
     *  Initiate RequestToken request operation
     */
    public function requestAction()
    {
        try {
            $requestUrl = $this->_helper->getRequestUrl($this->getRequest());
            $request = $this->_helper->prepareRequest($this->getRequest(), $requestUrl);

            // Request request token
            $response = $this->_oauthService->getRequestToken(
                $request, $requestUrl, $this->getRequest()->getMethod());
        } catch (\Exception $exception) {
            $response = $this->_helper->prepareErrorResponse(
                $exception,
                $this->getResponse()
            );
        }
        $this->getResponse()->setBody(http_build_query($response));
    }

    /**
     * Initiate AccessToken request operation
     */
    public function accessAction()
    {
        try {
            $requestUrl = $this->_helper->getRequestUrl($this->getRequest());
            $request = $this->_helper->prepareRequest($this->getRequest(), $requestUrl);

            // Request access token in exchange of a pre-authorized token
            $response = $this->_oauthService->getAccessToken(
                $request, $requestUrl, $this->getRequest()->getMethod());
        } catch (\Exception $exception) {
            $response = $this->_helper->prepareErrorResponse(
                $exception,
                $this->getResponse()
            );
        }
        $this->getResponse()->setBody(http_build_query($response));
    }
}
