<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Transaction;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\EntityStorage;
use Magento\Sales\Model\EntityStorageFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Resource\Metadata;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Sales\Model\Resource\Order\Payment\Transaction as TransactionResource;

/**
 * Repository class for \Magento\Sales\Model\Order\Payment\Transaction
 */
class Repository implements TransactionRepositoryInterface
{
    /**
     * Collection Result Factory
     *
     * @var SearchResultFactory
     */
    private $searchResultFactory = null;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Metadata
     */
    private $metaData;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var EntityStorage
     */
    private $entityStorage;

    /**
     * Repository constructor
     *
     * @param SearchResultFactory $searchResultFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Metadata $metaData
     * @param EntityStorageFactory $entityStorageFactory
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Metadata $metaData,
        EntityStorageFactory $entityStorageFactory,
        OrderPaymentRepositoryInterface $paymentRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->metaData = $metaData;
        $this->entityStorage = $entityStorageFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException(__('ID required'));
        }
        if (!$this->entityStorage->has($id)) {
            /** @var \Magento\Sales\Api\Data\TransactionInterface $entity */
            $entity = $this->metaData->getMapper()->load($this->metaData->getNewInstance(), $id);
            if (!$entity->getTransactionId()) {
                throw new NoSuchEntityException('Requested entity doesn\'t exist');
            }
            $this->entityStorage->add($entity);
        }
        return $this->entityStorage->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getByTxnType($txnType, $paymentId, $orderId)
    {
        if (!$txnType) {
            throw new \Magento\Framework\Exception\InputException(__('Txn Id required'));
        }
        if (!$paymentId) {
            throw new \Magento\Framework\Exception\InputException(__('Payment Id required'));
        }
        $identityFieldsForCache = [$txnType, $paymentId];
        $cacheStorage = 'txn_type';
        $entity = $this->entityStorage->getByIdentifyingFields($identityFieldsForCache, $cacheStorage);
        if (!$entity) {
            $filters[] = $this->filterBuilder
                ->setField(TransactionInterface::TXN_TYPE)
                ->setValue($txnType)
                ->create();
            $filters[] = $this->filterBuilder
                ->setField(TransactionInterface::PAYMENT_ID)
                ->setValue($paymentId)
                ->create();
            $transactionIdSort = $this->sortOrderBuilder
                ->setField('transaction_id')
                ->setDirection(Collection::SORT_ORDER_DESC)
                ->create();
            $createdAtSort = $this->sortOrderBuilder
                ->setField('created_at')
                ->setDirection(Collection::SORT_ORDER_DESC)
                ->create();
            $entity = current(
                $this->getList(
                    $this->searchCriteriaBuilder
                        ->addFilters($filters)
                        ->addSortOrder($transactionIdSort)
                        ->addSortOrder($createdAtSort)
                        ->create()
                )->getItems()
            );
            if ($entity) {
                $this->entityStorage->addByIdentifyingFields($entity, $identityFieldsForCache, $cacheStorage);
            }
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getByTxnId($txnId, $paymentId, $orderId)
    {
        $identityFieldsForCache = [$txnId, $paymentId, $orderId];
        $cacheStorage = 'txn_type';
        $entity = $this->entityStorage->getByIdentifyingFields($identityFieldsForCache, $cacheStorage);
        if (!$entity) {
            $entity = $this->metaData->getMapper()->loadObjectByTxnId(
                $this->metaData->getNewInstance(),
                $orderId,
                $paymentId,
                $txnId
            );
            if ($entity && $entity->getId()) {
                $this->entityStorage->addByIdentifyingFields($entity, $identityFieldsForCache, $cacheStorage);
            }
        }
        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria)
    {
        /** @var TransactionResource\Collection $collection */
        $collection = $this->searchResultFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $collection->addPaymentInformation(['method']);
        $collection->addOrderInformation(['increment_id']);
        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Sales\Api\Data\TransactionInterface $entity)
    {
        $this->metaData->getMapper()->delete($entity);
        $this->entityStorage->remove($entity->getTransactionId());
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Sales\Api\Data\TransactionInterface $entity)
    {
        $this->metaData->getMapper()->save($entity);
        $this->entityStorage->add($entity);
        return $this->entityStorage->get($entity->getTransactionId());
    }

    /**
     * Creates new Transaction instance.
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface Transaction interface.
     */
    public function create()
    {
        return $this->metaData->getNewInstance();
    }
}
