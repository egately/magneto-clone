<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Controller\Paypal;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\BraintreeTwo\Gateway\Config\PayPal\Config;
use Magento\BraintreeTwo\Model\Paypal\Helper\UpdateShippingMethod;

/**
 * Class SaveShippingMethod
 */
class SaveShippingMethod extends AbstractAction
{
    /**
     * @var UpdateShippingMethod
     */
    private $updateShippingMethod;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param UpdateShippingMethod $updateShippingMethod
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        UpdateShippingMethod $updateShippingMethod
    ) {
        parent::__construct($context, $config, $checkoutSession);
        $this->updateShippingMethod = $updateShippingMethod;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->getParam('isAjax');
        $quote = $this->checkoutSession->getQuote();

        try {
            $this->validateQuote($quote);

            $this->updateShippingMethod->execute(
                $this->getRequest()->getParam('shipping_method'),
                $quote
            );

            if ($isAjax) {
                /** @var Page $response */
                $response = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                $layout = $response->addHandle('paypal_express_review_details')->getLayout();

                $response = $layout->getBlock('page.block')->toHtml();
                $this->getResponse()->setBody($response);

                return;
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        $path = $this->_url->getUrl('*/*/review', ['_secure' => true]);

        if ($isAjax) {
            $this->getResponse()->setBody(sprintf('<script>window.location.href = "%s";</script>', $path));

            return;
        }

        $this->_redirect($path);
    }
}
