<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

/**
 * Interface for quote totals calculation
 * @api
 */
interface TotalsInformationManagementInterface
{
    /**
     * Calculate quote totals based on address and shipping method.
     *
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function calculate(
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    );
}
