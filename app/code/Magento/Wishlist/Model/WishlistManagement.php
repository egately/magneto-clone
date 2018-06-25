<?php

namespace Magento\Wishlist\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Wishlist\Api\WishlistManagementInterface;

class WishlistManagement implements WishlistManagementInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * WishlistRepository constructor.
     * @param ProductRepository $productRepository
     * @param WishlistFactory $wishlistFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        WishlistFactory $wishlistFactory
    ) {
        $this->productRepository = $productRepository;
        $this->wishlistFactory = $wishlistFactory;
    }


    /**
     * @inheritdoc
     */
    public function getWishlistByCustomerId($customerId)
    {
        /** @var \Magento\Wishlist\Model\ResourceModel\Wishlist $resourceModel */
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, false);
        if (!$wishlist->getId()) {
            throw new NoSuchEntityException(__('No wishlist for customer.'));
        }

        return $wishlist;
    }

    /**
     * @inheritdoc
     */
    public function addWishlistItemByCustomerId($customerId, $sku)
    {
        /** @var Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);

        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new StateException(__('No product with sku:' . $sku));
        }

        try {
            $item = $wishlist->addNewItem($product);
            return $item->getId();
        } catch (LocalizedException $exception) {
            throw new StateException(__('Product with id: ' . $product->getId() . ' already attached to wishlist'));
        }

    }
}
