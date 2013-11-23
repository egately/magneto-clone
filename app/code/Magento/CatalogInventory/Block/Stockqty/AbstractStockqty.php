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
 * @package     Magento_CatalogInventory
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product stock qty abstarct block
 *
 * @category   Magento
 * @package    Magento_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogInventory\Block\Stockqty;

abstract class AbstractStockqty extends \Magento\View\Block\Template
{
    const XML_PATH_STOCK_THRESHOLD_QTY = 'cataloginventory/options/stock_threshold_qty';

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\View\Block\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\View\Block\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $coreData, $data);
    }

    /**
     * Retrieve current product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve current product stock qty
     *
     * @return float
     */
    public function getStockQty()
    {
        if (!$this->hasData('product_stock_qty')) {
            $qty = 0;
            $stockItem = $this->getProduct()->getStockItem();
            if ($stockItem) {
                $qty = (float) $stockItem->getStockQty();
            }
            $this->setData('product_stock_qty', $qty);
        }
        return $this->getData('product_stock_qty');
    }

    /**
     * Retrieve threshold of qty to display stock qty message
     *
     * @return string
     */
    public function getThresholdQty()
    {
        if (!$this->hasData('threshold_qty')) {
            $qty = (float) $this->_storeConfig->getConfig(self::XML_PATH_STOCK_THRESHOLD_QTY);
            $this->setData('threshold_qty', $qty);
        }
        return $this->getData('threshold_qty');
    }

    /**
     * Retrieve id of message placeholder in template
     *
     * @return string
     */
    public function getPlaceholderId()
    {
        return 'stock-qty-' . $this->getProduct()->getId();
    }

    /**
     * Retrieve visibility of stock qty message
     *
     * @return bool
     */
    public function isMsgVisible()
    {
        return ($this->getStockQty() > 0 && $this->getStockQty() <= $this->getThresholdQty());
    }
}
