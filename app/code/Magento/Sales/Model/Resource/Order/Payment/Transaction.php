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
 * Sales transaction resource model
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Resource\Order\Payment;

class Transaction extends \Magento\Sales\Model\Resource\Order\AbstractOrder
{
    /**
     * Serializeable field: additional_information
     *
     * @var array
     */
    protected $_serializableFields   = array(
        'additional_information' => array(null, array())
    );

    /**
     * Initialize main table and the primary key field name
     *
     */
    protected function _construct()
    {
        $this->_init('sales_payment_transaction', 'transaction_id');
    }

    /**
     * Update transactions in database using provided transaction as parent for them
     * have to repeat the business logic to avoid accidental injection of wrong transactions
     *
     * @param \Magento\Sales\Model\Order\Payment\Transaction $transaction
     */
    public function injectAsParent(\Magento\Sales\Model\Order\Payment\Transaction $transaction)
    {
        $txnId = $transaction->getTxnId();
        if ($txnId && \Magento\Sales\Model\Order\Payment\Transaction::TYPE_PAYMENT === $transaction->getTxnType()
            && $id = $transaction->getId()
        ) {
            $adapter = $this->_getWriteAdapter();

            // verify such transaction exists, determine payment and order id
            $verificationRow = $adapter->fetchRow(
                $adapter->select()->from($this->getMainTable(), array('payment_id', 'order_id'))
                    ->where("{$this->getIdFieldName()} = ?", (int)$id)
            );
            if (!$verificationRow) {
                return;
            }
            list($paymentId, $orderId) = array_values($verificationRow);

            // inject
            $where = array(
                $adapter->quoteIdentifier($this->getIdFieldName()) . '!=?' => $id,
                new \Zend_Db_Expr('parent_id IS NULL'),
                'payment_id = ?'    => (int)$paymentId,
                'order_id = ?'      => (int)$orderId,
                'parent_txn_id = ?' => $txnId
            );
            $adapter->update($this->getMainTable(),
                array('parent_id' => $id),
                $where
            );
        }
    }

    /**
     * Load the transaction object by specified txn_id
     *
     * @param \Magento\Sales\Model\Order\Payment\Transaction $transaction
     * @param int $orderId
     * @param int $paymentId
     * @param string $txnId
     * @return \Magento\Sales\Model\Order\Payment\Transaction
     */
    public function loadObjectByTxnId(\Magento\Sales\Model\Order\Payment\Transaction $transaction, $orderId, $paymentId,
        $txnId
    ) {
        $select = $this->_getLoadByUniqueKeySelect($orderId, $paymentId, $txnId);
        $data   = $this->_getWriteAdapter()->fetchRow($select);
        if (!$data) {
            return $transaction;
        }
        $transaction->setData($data);
        $this->unserializeFields($transaction);
        $this->_afterLoad($transaction);

        return $transaction;
    }

    /**
     * Retrieve order website id
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderWebsiteId($orderId)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array(':entity_id' => $orderId);
        $select  = $adapter->select()
            ->from(array('so' => $this->getTable('sales_flat_order')), 'cs.website_id')
            ->joinInner(array('cs' => $this->getTable('core_store')), 'cs.store_id = so.store_id')
            ->where('so.entity_id = :entity_id');
        return $adapter->fetchOne($select, $bind);
    }

    /**
     * Lookup for parent_id in already saved transactions of this payment by the order_id
     * Also serialize additional information, if any
     *
     * @throws \Magento\Core\Exception
     *
     * @param \Magento\Sales\Model\Order\Payment\Transaction $transaction
     * @return \Magento\Sales\Model\Resource\Order\Payment\Transaction
     */
    protected function _beforeSave(\Magento\Core\Model\AbstractModel $transaction)
    {
        $parentTxnId = $transaction->getData('parent_txn_id');
        $txnId       = $transaction->getData('txn_id');
        $orderId     = $transaction->getData('order_id');
        $paymentId   = $transaction->getData('payment_id');
        $idFieldName = $this->getIdFieldName();

        if ($parentTxnId) {
            if (!$txnId || !$orderId || !$paymentId) {
                throw new \Magento\Core\Exception(
                    __('We don\'t have enough information to save the parent transaction ID.'));
            }
            $parentId = (int)$this->_lookupByTxnId($orderId, $paymentId, $parentTxnId, $idFieldName);
            if ($parentId) {
                $transaction->setData('parent_id', $parentId);
            }
        }

        // make sure unique key won't cause trouble
        if ($transaction->isFailsafe()) {
            $autoincrementId = (int)$this->_lookupByTxnId($orderId, $paymentId, $txnId, $idFieldName);
            if ($autoincrementId) {
                $transaction->setData($idFieldName, $autoincrementId)->isObjectNew(false);
            }
        }

        return parent::_beforeSave($transaction);
    }

    /**
     * Load cell/row by specified unique key parts
     *
     * @param int $orderId
     * @param int $paymentId
     * @param string $txnId
     * @param mixed (array|string|object) $columns
     * @param bool $isRow
     * @param string $txnType
     * @return mixed (array|string)
     */
    private function _lookupByTxnId($orderId, $paymentId, $txnId, $columns, $isRow = false, $txnType = null)
    {
        $select = $this->_getLoadByUniqueKeySelect($orderId, $paymentId, $txnId, $columns);
        if ($txnType) {
            $select->where('txn_type = ?', $txnType);
        }
        if ($isRow) {
            return $this->_getWriteAdapter()->fetchRow($select);
        }
        return $this->_getWriteAdapter()->fetchOne($select);
    }

    /**
     * Get select object for loading transaction by the unique key of order_id, payment_id, txn_id
     *
     * @param int $orderId
     * @param int $paymentId
     * @param string $txnId
     * @param string|array|Zend_Db_Expr $columns
     * @return \Magento\DB\Select
     */
    private function _getLoadByUniqueKeySelect($orderId, $paymentId, $txnId, $columns = '*')
    {
        return $this->_getWriteAdapter()->select()
            ->from($this->getMainTable(), $columns)
            ->where('order_id = ?', $orderId)
            ->where('payment_id = ?', $paymentId)
            ->where('txn_id = ?', $txnId);
    }
}
