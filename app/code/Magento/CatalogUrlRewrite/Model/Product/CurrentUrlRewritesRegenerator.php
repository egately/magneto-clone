<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\ObjectManager;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrentUrlRewritesRegenerator
{
    /**
     * @var Product
     * @deprecated 100.1.4
     */
    protected $product;

    /**
     * @var ObjectRegistry
     * @deprecated 100.1.4
     */
    protected $productCategories;

    /**
     * @var UrlFinderInterface
     * @deprecated 100.1.4
     */
    protected $urlFinder;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    protected $productUrlPathGenerator;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite
     */
    private $urlRewritePrototype;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder
     */
    private $urlRewriteFinder;

    /**
     * @var \Magento\UrlRewrite\Model\MergeDataProvider
     */
    private $mergeDataProviderPrototype;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlRewriteFinder|null $urlRewriteFinder
     * @param \Magento\UrlRewrite\Model\MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param CategoryRepository|null $categoryRepository
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        ProductUrlPathGenerator $productUrlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory,
        UrlRewriteFinder $urlRewriteFinder = null,
        MergeDataProviderFactory $mergeDataProviderFactory = null,
        CategoryRepository $categoryRepository = null
    ) {
        $this->urlFinder = $urlFinder;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewritePrototype = $urlRewriteFactory->create();
        $this->urlRewriteFinder = $urlRewriteFinder ?: ObjectManager::getInstance()->get(UrlRewriteFinder::class);
        if (!isset($mergeDataProviderFactory)) {
            $mergeDataProviderFactory = ObjectManager::getInstance()->get(MergeDataProviderFactory::class);
        }
        $this->categoryRepository = $categoryRepository ?: ObjectManager::getInstance()->get(CategoryRepository::class);
        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
    }

    /**
     * Generate product rewrites based on current rewrites without anchor categories
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
     */
    public function generate($storeId, Product $product, ObjectRegistry $productCategories, $rootCategoryId = null)
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $currentUrlRewrites = $this->urlRewriteFinder->findAllByData(
            $product->getEntityId(),
            $storeId,
            ProductUrlRewriteGenerator::ENTITY_TYPE,
            $rootCategoryId
        );

        foreach ($currentUrlRewrites as $currentUrlRewrite) {
            $category = $this->retrieveCategoryFromMetadata($currentUrlRewrite, $productCategories);
            if ($category === false) {
                continue;
            }
            $mergeDataProvider->merge(
                $currentUrlRewrite->getIsAutogenerated()
                ? $this->generateForAutogenerated($currentUrlRewrite, $storeId, $category, $product)
                : $this->generateForCustom($currentUrlRewrite, $storeId, $category, $product)
            );
        }

        return $mergeDataProvider->getData();
    }

    /**
     * Generate product rewrites for anchor categories based on current rewrites
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
     */
    public function generateAnchor(
        $storeId,
        Product $product,
        ObjectRegistry $productCategories,
        $rootCategoryId = null
    ) {
        $anchorCategoryIds = [];
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;

        $currentUrlRewrites = $this->urlRewriteFinder->findAllByData(
            $product->getEntityId(),
            $storeId,
            ProductUrlRewriteGenerator::ENTITY_TYPE,
            $rootCategoryId
        );

        foreach ($productCategories->getList() as $productCategory) {
            $anchorCategoryIds = array_merge($productCategory->getAnchorsAbove(), $anchorCategoryIds);
        }

        foreach ($currentUrlRewrites as $currentUrlRewrite) {
            $metadata = $currentUrlRewrite->getMetadata();
            if (isset($metadata['category_id']) && $metadata['category_id'] > 0) {
                $category = $this->categoryRepository->get($metadata['category_id'], $storeId);
                if (in_array($category->getId(), $anchorCategoryIds)) {
                    $mergeDataProvider->merge(
                        $currentUrlRewrite->getIsAutogenerated()
                        ? $this->generateForAutogenerated($currentUrlRewrite, $storeId, $category, $product)
                        : $this->generateForCustom($currentUrlRewrite, $storeId, $category, $product)
                    );
                }
            }
        }

        return $mergeDataProvider->getData();
    }

    /**
     * @param UrlRewrite $url
     * @param int $storeId
     * @param Category|null $category
     * @param Product|null $product
     * @return UrlRewrite[]
     */
    protected function generateForAutogenerated($url, $storeId, $category, $product = null)
    {
        if ($product->getData('save_rewrites_history')) {
            $targetPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category);
            if ($url->getRequestPath() !== $targetPath) {
                $generatedUrl = clone $this->urlRewritePrototype;
                $generatedUrl->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                    ->setEntityId($product->getEntityId())
                    ->setRequestPath($url->getRequestPath())
                    ->setTargetPath($targetPath)
                    ->setRedirectType(OptionProvider::PERMANENT)
                    ->setStoreId($storeId)
                    ->setDescription($url->getDescription())
                    ->setIsAutogenerated(0)
                    ->setMetadata($url->getMetadata());
                return [$generatedUrl];
            }
        }
        return [];
    }

    /**
     * @param UrlRewrite $url
     * @param int $storeId
     * @param Category|null $category
     * @param Product|null $product
     * @return UrlRewrite[]
     */
    protected function generateForCustom($url, $storeId, $category, $product = null)
    {
        $targetPath = $url->getRedirectType()
            ? $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category)
            : $url->getTargetPath();
        if ($url->getRequestPath() !== $targetPath) {
            $generatedUrl = clone $this->urlRewritePrototype;
            $generatedUrl->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($product->getEntityId())
                ->setRequestPath($url->getRequestPath())
                ->setTargetPath($targetPath)
                ->setRedirectType($url->getRedirectType())
                ->setStoreId($storeId)
                ->setDescription($url->getDescription())
                ->setIsAutogenerated(0)
                ->setMetadata($url->getMetadata());
            return [$generatedUrl];
        }
        return [];
    }

    /**
     * @param UrlRewrite $url
     * @param ObjectRegistry|null $productCategories
     * @return Category|null|bool
     */
    protected function retrieveCategoryFromMetadata($url, ObjectRegistry $productCategories = null)
    {
        $metadata = $url->getMetadata();
        if (isset($metadata['category_id'])) {
            $category = $productCategories->get($metadata['category_id']);
            return $category === null ? false : $category;
        }
        return null;
    }
}
