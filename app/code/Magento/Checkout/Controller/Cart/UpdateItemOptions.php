<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework;
use Magento\Framework\Controller\ResultFactory;

class UpdateItemOptions extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Registry $coreRegistry
     * @codeCoverageIgnore
     */
    public function __construct(
        Framework\App\Action\Context $context,
        Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Registry $coreRegistry = null
    ) {

        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
         $this->_coreRegistry = $coreRegistry ?: \Magento\Framework\App\ObjectManager::getInstance()
                              ->get(\Magento\Framework\Registry::class);
         $this->_coreRegistry->register('configure_item',true);
    }
    
    /**
     * Update product configuration for a cart item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = [];
        }
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $this->cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The quote item isn't found. Verify the item and try again.")
                );
            }

            $item = $this->cart->updateItem($id, new \Magento\Framework\DataObject($params));
            if (is_string($item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item));
            }
            if ($item->getHasError()) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            $this->_eventManager->dispatch(
                'checkout_cart_update_item_complete',
                ['item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );
            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        '%1 was updated in your shopping cart.',
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)
                            ->escapeHtml($item->getProduct()->getName())
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
                return $this->_goBack($this->_url->getUrl('checkout/cart'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ($url) {
                return $this->resultRedirectFactory->create()->setUrl($url);
            } else {
                $cartUrl = $this->_objectManager->get(\Magento\Checkout\Helper\Cart::class)->getCartUrl();
                return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($cartUrl));
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the item right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->_goBack();
        }
        return $this->resultRedirectFactory->create()->setPath('*/*');
    }
}
