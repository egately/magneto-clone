<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model;

use Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase;

class ConfigurableTest extends AbstractProductExportImportTestCase
{
    public function exportImportDataProvider()
    {
        return [
            'configurable-product' => [
                [
                    'Magento/ConfigurableProduct/_files/product_configurable.php'
                ],
                [
                    'configurable',
                ],
                ['_cache_instance_products', '_cache_instance_configurable_attributes'],
                [
                    'Magento/ConfigurableProduct/_files/product_configurable_rollback.php'
                ]
            ],
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $origProduct
     * @param \Magento\Catalog\Model\Product $newProduct
     */
    protected function assertEqualsSpecificAttributes($origProduct, $newProduct)
    {
        $origProductExtensionAttributes = $origProduct->getExtensionAttributes();
        $newProductExtensionAttributes = $newProduct->getExtensionAttributes();

        $this->assertEquals(
            $origProductExtensionAttributes,
            $newProductExtensionAttributes
        );
    }
}
