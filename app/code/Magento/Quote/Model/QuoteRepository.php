<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory;
use Magento\Quote\Model\QuoteRepository\SaveHandler;
use Magento\Quote\Model\QuoteRepository\LoadHandler;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteRepository implements CartRepositoryInterface
{
    /**
     * @var Quote[]
     */
    private $quotesById = [];

    /**
     * @var Quote[]
     */
    private $quotesByCustomerId = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CartSearchResultsInterfaceFactory
     */
    private $searchResultsDataFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var CartInterfaceFactory
     */
    private $cartFactory;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var LoadHandler
     */
    private $loadHandler;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CartSearchResultsInterfaceFactory $searchResultsDataFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param CartInterfaceFactory $cartFactory
     * @param SaveHandler $saveHandler
     * @param LoadHandler $loadHandler
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CartSearchResultsInterfaceFactory $searchResultsDataFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        QuoteCollectionFactory $quoteCollectionFactory,
        CartInterfaceFactory $cartFactory,
        SaveHandler $saveHandler,
        LoadHandler $loadHandler
    ) {
        $this->storeManager = $storeManager;
        $this->searchResultsDataFactory = $searchResultsDataFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cartFactory = $cartFactory;
        $this->saveHandler = $saveHandler;
        $this->loadHandler = $loadHandler;
    }

    /**
     * @inheritdoc
     */
    public function get($cartId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesById[$cartId])) {
            $quote = $this->loadQuote('loadByIdWithoutStore', 'cartId', $cartId, $sharedStoreIds);
            $this->loadHandler->load($quote);
            $this->quotesById[$cartId] = $quote;
        }
        return $this->quotesById[$cartId];
    }

    /**
     * @inheritdoc
     */
    public function getForCustomer($customerId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesByCustomerId[$customerId])) {
            $quote = $this->loadQuote('loadByCustomer', 'customerId', $customerId, $sharedStoreIds);
            $this->loadHandler->load($quote);
            $this->quotesById[$quote->getId()] = $quote;
            $this->quotesByCustomerId[$customerId] = $quote;
        }
        return $this->quotesByCustomerId[$customerId];
    }

    /**
     * @inheritdoc
     */
    public function getActive($cartId, array $sharedStoreIds = [])
    {
        $quote = $this->get($cartId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('cartId', $cartId);
        }
        return $quote;
    }

    /**
     * @inheritdoc
     */
    public function getActiveForCustomer($customerId, array $sharedStoreIds = [])
    {
        $quote = $this->getForCustomer($customerId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('customerId', $customerId);
        }
        return $quote;
    }

    /**
     * @inheritdoc
     */
    public function save(CartInterface $quote)
    {
        if ($quote->getId()) {
            $currentQuote = $this->get($quote->getId(), [$quote->getStoreId()]);

            foreach ($currentQuote->getData() as $key => $value) {
                if (!$quote->hasData($key)) {
                    $quote->setData($key, $value);
                }
            }
        }

        $this->saveHandler->save($quote);
        unset($this->quotesById[$quote->getId()], $this->quotesByCustomerId[$quote->getCustomerId()]);
    }

    /**
     * @inheritdoc
     */
    public function delete(CartInterface $quote)
    {
        $quoteId = $quote->getId();
        $customerId = $quote->getCustomerId();
        $quote->delete();
        unset($this->quotesById[$quoteId], $this->quotesByCustomerId[$customerId]);
    }

    /**
     * Load quote with different methods
     *
     * @param string $loadMethod
     * @param string $loadField
     * @param int $identifier
     * @param int[] $sharedStoreIds
     * @throws NoSuchEntityException
     * @return CartInterface
     */
    private function loadQuote($loadMethod, $loadField, $identifier, array $sharedStoreIds = [])
    {
        /** @var CartInterface $quote */
        $quote = $this->cartFactory->create();
        if ($sharedStoreIds && is_callable([$quote, 'setSharedStoreIds'])) {
            $quote->setSharedStoreIds($sharedStoreIds);
        }
        $quote->setStoreId($this->storeManager->getStore()->getId())->$loadMethod($identifier);
        if (!$quote->getId()) {
            throw NoSuchEntityException::singleField($loadField, $identifier);
        }
        return $quote;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        /** @var \Magento\Quote\Api\Data\CartSearchResultsInterface $searchData */
        $searchData = $this->searchResultsDataFactory->create();
        $searchData->setSearchCriteria($searchCriteria);

        $this->collectionProcessor->process($searchCriteria, $quoteCollection);
        $this->extensionAttributesJoinProcessor->process($quoteCollection);
        foreach ($quoteCollection->getItems() as $quote) {
            /** @var CartInterface $quote */
            $this->loadHandler->load($quote);
        }
        $searchData->setItems($quoteCollection->getItems());
        $searchData->setTotalCount($quoteCollection->getSize());
        return $searchData;
    }

    /**
     * Adds a specified filter group to the specified quote collection.
     *
     * @param FilterGroup $filterGroup The filter group.
     * @param QuoteCollection $collection The quote collection.
     * @return void
     * @deprecated 100.2.0
     * @throws InputException The specified filter group or quote collection does not exist.
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, QuoteCollection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $fields[] = $filter->getField();
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }
}
