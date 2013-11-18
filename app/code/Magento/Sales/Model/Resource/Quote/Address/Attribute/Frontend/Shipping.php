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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Quote address attribute frontend shipping resource model
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend;

class Shipping
    extends \Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend
{
    /**
     * Fetch totals
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return \Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend\Shipping
     */
    public function fetchTotals(\Magento\Sales\Model\Quote\Address $address)
    {
        $amount = $address->getShippingAmount();
        if ($amount != 0) {
            $title = __('Shipping & Handling');
            if ($address->getShippingDescription()) {
                $title .= sprintf(' (%s)', $address->getShippingDescription());  
            }
            $address->addTotal(array(
                'code'  => 'shipping',
                'title' => $title,
                'value' => $address->getShippingAmount()
            ));
        }
        return $this;
    }
}
