<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Website;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link as ProductWebsiteLink;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /** @var  ProductWebsiteLink */
    private $productWebsiteLink;

    /**
     * ReadHandler constructor.
     * @param ProductWebsiteLink $resourceModel
     */
    public function __construct(
        ProductWebsiteLink $productWebsiteLink
    ) {
        $this->productWebsiteLink = $productWebsiteLink;
    }

    /**
     * @param ProductInterface $product
     * @param array $arguments
     * @return ProductInterface
     */
    public function execute($product, $arguments = [])
    {
        if ($product->getExtensionAttributes()->getWebsiteIds() !== null) {
            return $product;
        }
        $websiteIds = $this->productWebsiteLink->getWebsiteIdsByProductId($product->getId());

        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes->setWebsiteIds($websiteIds);
        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }
}