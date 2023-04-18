<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\Cart as CustomerDataCart;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Tax\Block\Item\Price\Renderer;

/**
 * Process quote items price, considering tax configuration.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Cart
{
    /**
     * @var Quote|null
     */
    protected $quote = null;

    /**
     * @var array|null
     */
    protected $totals = null;

    /**
     * @param Session $checkoutSession
     * @param CheckoutHelper $checkoutHelper
     * @param Renderer $itemPriceRenderer
     */
    public function __construct(
        protected readonly Session $checkoutSession,
        protected readonly CheckoutHelper $checkoutHelper,
        protected readonly Renderer $itemPriceRenderer
    ) {
    }

    /**
     * Add tax data to result
     *
     * @param CustomerDataCart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(CustomerDataCart $subject, $result)
    {
        $result['subtotal_incl_tax'] = $this->checkoutHelper->formatPrice($this->getSubtotalInclTax());
        $result['subtotal_excl_tax'] = $this->checkoutHelper->formatPrice($this->getSubtotalExclTax());

        $items =$this->getQuote()->getAllVisibleItems();
        if (is_array($result['items'])) {
            foreach ($result['items'] as $key => $itemAsArray) {
                if ($item = $this->findItemById($itemAsArray['item_id'], $items)) {
                    $this->itemPriceRenderer->setItem($item);
                    $this->itemPriceRenderer->setTemplate('checkout/cart/item/price/sidebar.phtml');
                    $result['items'][$key]['product_price']=$this->itemPriceRenderer->toHtml();
                    if ($this->itemPriceRenderer->displayPriceExclTax()) {
                        $result['items'][$key]['product_price_value'] = $item->getCalculationPrice();
                    } elseif ($this->itemPriceRenderer->displayPriceInclTax()) {
                        $result['items'][$key]['product_price_value'] = $item->getPriceInclTax();
                    } elseif ($this->itemPriceRenderer->displayBothPrices()) {
                        //unset product price value in case price already has been set as scalar value.
                        unset($result['items'][$key]['product_price_value']);
                        $result['items'][$key]['product_price_value']['incl_tax'] = $item->getPriceInclTax();
                        $result['items'][$key]['product_price_value']['excl_tax'] = $item->getCalculationPrice();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get subtotal, including tax
     *
     * @return float
     */
    protected function getSubtotalInclTax()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValueInclTax() ?: $totals['subtotal']->getValue();
        }
        return $subtotal;
    }

    /**
     * Get subtotal, excluding tax
     *
     * @return float
     */
    protected function getSubtotalExclTax()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValueExclTax() ?: $totals['subtotal']->getValue();
        }
        return $subtotal;
    }

    /**
     * Get totals
     *
     * @return array
     */
    public function getTotals()
    {
        // TODO: TODO: MAGETWO-34824 duplicate \Magento\Checkout\CustomerData\Cart::getSectionData
        if (empty($this->totals)) {
            $this->totals = $this->getQuote()->getTotals();
        }
        return $this->totals;
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Find item by id in items haystack
     *
     * @param int $id
     * @param array $itemsHaystack
     * @return QuoteItem | bool
     */
    protected function findItemById($id, $itemsHaystack)
    {
        if (is_array($itemsHaystack)) {
            foreach ($itemsHaystack as $item) {
                /** @var QuoteItem $item */
                if ((int)$item->getItemId() == $id) {
                    return $item;
                }
            }
        }
        return false;
    }
}
