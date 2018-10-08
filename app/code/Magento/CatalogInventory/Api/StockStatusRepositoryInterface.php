<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockStatusRepositoryInterface
 * @api
 * @since 100.0.2
 *
 * @deprecated CatalogInventory will be replaced by Multi-Source Inventory (MSI)
 *             see https://github.com/magento-engcom/msi/wiki/Technical-Vision.-Catalog-Inventory
 */
interface StockStatusRepositoryInterface
{
    /**
     * Save StockStatus data
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function save(\Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus);

    /**
     * Load StockStatus data by given stockStatusId and parameters
     *
     * @param string $stockStatusId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function get($stockStatusId);

    /**
     * Load Stock Status data collection by given search criteria
     *
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $searchCriteria
     * @return \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface
     */
    public function getList(StockStatusCriteriaInterface $searchCriteria);

    /**
     * Delete StockStatus entity
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus
     * @return bool
     */
    public function delete(\Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus);
}
