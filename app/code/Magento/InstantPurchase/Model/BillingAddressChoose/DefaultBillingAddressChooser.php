<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\BillingAddressChoose;

use Magento\Customer\Model\Customer;

/**
 * Billing address chooser implementation to choose customer default billing address.
 */
class DefaultBillingAddressChooser implements BillingAddressChooserInterface
{
    /**
     * @inheritdoc
     */
    public function choose(Customer $customer)
    {
        $address = $customer->getDefaultBillingAddress();
        return $address ?: null;
    }
}
