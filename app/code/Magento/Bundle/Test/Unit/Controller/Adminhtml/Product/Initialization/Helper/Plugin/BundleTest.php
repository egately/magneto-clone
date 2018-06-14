<?php
/**
 * Test class for \Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Bundle
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Bundle;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class BundleTest extends TestCase
{
    /**
     * @var Bundle
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var array
     */
    protected $bundleSelections;

    /**
     * @var array
     */
    protected $bundleOptionsRaw;

    /**
     * @var array
     */
    protected $bundleOptionsCleaned;

    protected function setUp()
    {
        $this->requestMock = $this->createMock(Http::class);
        $methods = [
            'getCompositeReadonly',
            'setBundleOptionsData',
            'setBundleSelectionsData',
            'getPriceType',
            'setCanSaveCustomOptions',
            'getProductOptions',
            'setOptions',
            'setCanSaveBundleSelections',
            '__wakeup',
            'getOptionsReadonly',
            'getBundleOptionsData',
            'getExtensionAttributes',
            'setExtensionAttributes',
        ];
        $this->productMock = $this->createPartialMock(Product::class, $methods);
        $optionFactory = $this->getMockBuilder(OptionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $linkInterfaceFactory = $this->getMockBuilder(LinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customOptionFactory = $this->getMockBuilder(
            ProductCustomOptionInterfaceFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->subjectMock = $this->createMock(
            Helper::class
        );
        $this->model = new Bundle(
            $this->requestMock,
            $optionFactory,
            $linkInterfaceFactory,
            $productRepository,
            $storeManager,
            $customOptionFactory
        );

        $this->bundleSelections = [
            ['postValue'],
        ];
        $this->bundleOptionsRaw = [
            'bundle_options' => [
                [
                    'title' => 'Test Option',
                    'bundle_selections' => $this->bundleSelections,
                ],
            ],
        ];
        $this->bundleOptionsCleaned = $this->bundleOptionsRaw['bundle_options'];
        unset($this->bundleOptionsCleaned[0]['bundle_selections']);
    }

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function testAfterInitializeIfBundleAnsCustomOptionsAndBundleSelectionsExist(): void
    {
        $productOptionsBefore = [0 => ['key' => 'value'], 1 => ['is_delete' => false]];
        $valueMap = [
            ['bundle_options', null, $this->bundleOptionsRaw],
            ['affect_bundle_product_selections', null, 1],
        ];
        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($valueMap));
        $this->productMock->expects($this->any())->method('getCompositeReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->once())
            ->method('setBundleOptionsData')
            ->with($this->bundleOptionsCleaned);
        $this->productMock->expects($this->once())->method('setBundleSelectionsData')->with([$this->bundleSelections]);
        $this->productMock->expects($this->once())->method('getPriceType')->will($this->returnValue(0));
        $this->productMock->expects($this->any())->method('getOptionsReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->once())->method('setCanSaveCustomOptions')->with(true);
        $this->productMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->will(
            $this->returnValue($productOptionsBefore)
        );
        $this->productMock->expects($this->once())->method('setOptions')->with(null);
        $this->productMock->expects($this->once())->method('setCanSaveBundleSelections')->with(true);
        $this->productMock->expects($this->once())
            ->method('getBundleOptionsData')
            ->willReturn(['option_1' => ['delete' => 1]]);
        $extentionAttribute = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setBundleProductOptions'])
            ->getMockForAbstractClass();
        $extentionAttribute->expects($this->once())->method('setBundleProductOptions')->with([]);
        $this->productMock->expects($this->once())->method('getExtensionAttributes')->willReturn($extentionAttribute);
        $this->productMock->expects($this->once())->method('setExtensionAttributes')->with($extentionAttribute);
        $this->model->afterInitialize($this->productMock);
    }

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function testAfterInitializeIfBundleSelectionsDoNotExist(): void
    {
        $bundleOptionsRawWithoutSelections = $this->bundleOptionsRaw;
        $bundleOptionsRawWithoutSelections['bundle_options'][0]['bundle_selections'] = false;
        $valueMap = [
            ['bundle_options', null, $bundleOptionsRawWithoutSelections],
            ['affect_bundle_product_selections', null, false],
        ];
        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($valueMap));
        $this->productMock->expects($this->any())->method('getCompositeReadonly')->will($this->returnValue(false));
        $this->expectException(CouldNotSaveException::class);
        $this->model->afterInitialize($this->productMock);
    }
}
