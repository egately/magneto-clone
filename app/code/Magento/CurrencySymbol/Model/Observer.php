<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Currency Symbol Observer
 */
namespace Magento\CurrencySymbol\Model;

use Magento\Framework\Locale\Currency;

class Observer
{
    /**
     * @var \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory
     */
    protected $symbolFactory;

    /**
     * @param \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory
     */
    public function __construct(\Magento\CurrencySymbol\Model\System\CurrencysymbolFactory $symbolFactory)
    {
        $this->symbolFactory = $symbolFactory;
    }

    /**
     * Generate options for currency displaying with custom currency symbol
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function currencyDisplayOptions(\Magento\Framework\Event\Observer $observer)
    {
        $baseCode = $observer->getEvent()->getBaseCode();
        $currencyOptions = $observer->getEvent()->getCurrencyOptions();
        $currencyOptions->setData($this->getCurrencyOptions($baseCode));

        return $this;
    }

    /**
     * Get currency display options
     *
     * @param string $baseCode
     * @return array
     */
    protected function getCurrencyOptions($baseCode)
    {
        $currencyOptions = [];
        if ($baseCode) {
            $customCurrencySymbol = $this->symbolFactory->create()->getCurrencySymbol($baseCode);
            if ($customCurrencySymbol) {
                $currencyOptions[Currency::CURRENCY_OPTION_SYMBOL] = $customCurrencySymbol;
                $currencyOptions[Currency::CURRENCY_OPTION_DISPLAY] = \Magento\Framework\Currency::USE_SYMBOL;
            }
        }

        return $currencyOptions;
    }
}
