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
 * Adminhtml sales order item renderer
 *
 * @category   Magento
 * @package    Magento_Sales
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer;

class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    public function getItem()
    {
        return $this->_getData('item');
    }

    /**
     * Retrieve real html id for field
     *
     * @param string $name
     * @return string
     */
    public function getFieldId($id)
    {
        return $this->getFieldIdPrefix() . $id;
    }

    /**
     * Retrieve field html id prefix
     *
     * @return string
     */
    public function getFieldIdPrefix()
    {
        return 'order_item_' . $this->getItem()->getId() . '_';
    }

    /**
     * Indicate that block can display container
     *
     * @return boolean
     */
    public function canDisplayContainer()
    {
        return $this->getRequest()->getParam('reload') != 1;
    }

    /**
     * Giftmessage object
     *
     * @var \Magento\GiftMessage\Model\Message
     */
    protected $_giftMessage = array();

    /**
     * Retrive default value for giftmessage sender
     *
     * @return string
     */
    public function getDefaultSender()
    {
        if(!$this->getItem()) {
            return '';
        }

        if($this->getItem()->getOrder()) {
            return $this->getItem()->getOrder()->getBillingAddress()->getName();
        }

        return $this->getItem()->getBillingAddress()->getName();
    }

    /**
     * Retrive default value for giftmessage recipient
     *
     * @return string
     */
    public function getDefaultRecipient()
    {
        if(!$this->getItem()) {
            return '';
        }

        if($this->getItem()->getOrder()) {
            if ($this->getItem()->getOrder()->getShippingAddress()) {
                return $this->getItem()->getOrder()->getShippingAddress()->getName();
            } else if ($this->getItem()->getOrder()->getBillingAddress()) {
                return $this->getItem()->getOrder()->getBillingAddress()->getName();
            }
        }

        if ($this->getItem()->getShippingAddress()) {
            return $this->getItem()->getShippingAddress()->getName();
        } else if ($this->getItem()->getBillingAddress()) {
            return $this->getItem()->getBillingAddress()->getName();
        }

        return '';
    }

    /**
     * Retrive real name for field
     *
     * @param string $name
     * @return string
     */
    public function getFieldName($name)
    {
        return 'giftmessage[' . $this->getItem()->getId() . '][' . $name . ']';
    }

    /**
     * Initialize gift message for entity
     *
     * @return \Magento\Sales\Block\Adminhtml\Order\View\Giftmessage
     */
    protected function _initMessage()
    {
        $this->_giftMessage[$this->getItem()->getGiftMessageId()] =
            $this->helper('Magento\GiftMessage\Helper\Message')->getGiftMessage($this->getItem()->getGiftMessageId());

        // init default values for giftmessage form
        if(!$this->getMessage()->getSender()) {
            $this->getMessage()->setSender($this->getDefaultSender());
        }
        if(!$this->getMessage()->getRecipient()) {
            $this->getMessage()->setRecipient($this->getDefaultRecipient());
        }

        return $this;
    }

    /**
     * Retrive gift message for entity
     *
     * @return \Magento\GiftMessage\Model\Message
     */
    public function getMessage()
    {
        if(!isset($this->_giftMessage[$this->getItem()->getGiftMessageId()])) {
            $this->_initMessage();
        }

        return $this->_giftMessage[$this->getItem()->getGiftMessageId()];
    }

    /**
     * Retrieve save url
     *
     * @return array
     */
    public function getSaveUrl()
    {
        return $this->getUrl('sales/order_view_giftmessage/save', array(
            'entity'    => $this->getItem()->getId(),
            'type'      => 'order_item',
            'reload'    => true
        ));
    }

    /**
     * Retrive block html id
     *
     * @return string
     */
    public function getHtmlId()
    {
        return substr($this->getFieldIdPrefix(), 0, -1);
    }

    /**
     * Indicates that block can display giftmessages form
     *
     * @return boolean
     */
    public function canDisplayGiftmessage()
    {
        return $this->helper('Magento\GiftMessage\Helper\Message')->getIsMessagesAvailable(
            'order_item', $this->getItem(), $this->getItem()->getOrder()->getStoreId()
        );
    }

    /**
     * Display susbtotal price including tax
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function displaySubtotalInclTax($item)
    {
        return $this->displayPrices(
            $this->helper('Magento\Checkout\Helper\Data')->getBaseSubtotalInclTax($item),
            $this->helper('Magento\Checkout\Helper\Data')->getSubtotalInclTax($item)
        );
    }

    /**
     * Display item price including tax
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function displayPriceInclTax(\Magento\Object $item)
    {
        return $this->displayPrices(
            $this->helper('Magento\Checkout\Helper\Data')->getBasePriceInclTax($item),
            $this->helper('Magento\Checkout\Helper\Data')->getPriceInclTax($item)
        );
    }

}
