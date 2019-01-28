<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder;
use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class UrlRewriteFinderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlRewriteFinderTest extends \PHPUnit\Framework\TestCase
{
    /** @var DatabaseMapPool|\PHPUnit_Framework_MockObject_MockObject */
    private $databaseMapPoolMock;

    /** @var UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $urlRewriteFactoryMock;

    /** @var UrlRewrite|\PHPUnit_Framework_MockObject_MockObject */
    private $urlRewritePrototypeMock;

    /** @var UrlFinderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $urlFinderMock;

    /** @var UrlRewriteFinder|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $serializerMock;

    protected function setUp()
    {
        $this->serializerMock = $this->createMock(Json::class);
        $this->databaseMapPoolMock = $this->createMock(DatabaseMapPool::class);
        $this->urlFinderMock = $this->createMock(UrlFinderInterface::class);
        $this->urlRewriteFactoryMock = $this->createPartialMock(UrlRewriteFactory::class, ['create']);
        $this->urlRewritePrototypeMock = new UrlRewrite([], $this->serializerMock);

        $this->urlRewriteFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->urlRewritePrototypeMock);

        $urlRewriteClassesNamesArray = [
            UrlRewriteFinder::ENTITY_TYPE_PRODUCT => DataProductUrlRewriteDatabaseMap::class,
            UrlRewriteFinder::ENTITY_TYPE_CATEGORY => DataCategoryUrlRewriteDatabaseMap::class
        ];

        $this->model = (new ObjectManager($this))->getObject(
            UrlRewriteFinder::class,
            [
                'databaseMapPool' => $this->databaseMapPoolMock,
                'urlFinder' => $this->urlFinderMock,
                'urlRewriteFactory' => $this->urlRewriteFactoryMock,
                'urlRewriteClassNames' => $urlRewriteClassesNamesArray
            ]
        );
    }

    /**
     * test findAllByData using urlFinder
     */
    public function testGetByIdentifiersFallback()
    {
        $expected = [1, 2, 3];
        $this->databaseMapPoolMock->expects($this->never())
            ->method('getDataMap');

        $this->urlFinderMock->expects($this->exactly(7))
            ->method('findAllByData')
            ->willReturn($expected);

        $this->assertSame($expected, $this->model->findAllByData(1, 1, UrlRewriteFinder::ENTITY_TYPE_CATEGORY));
        $this->assertSame($expected, $this->model->findAllByData(1, 1, UrlRewriteFinder::ENTITY_TYPE_PRODUCT));
        $this->assertSame($expected, $this->model->findAllByData('a', 1, UrlRewriteFinder::ENTITY_TYPE_PRODUCT), 1);
        $this->assertSame($expected, $this->model->findAllByData('a', 'a', UrlRewriteFinder::ENTITY_TYPE_PRODUCT), 1);
        $this->assertSame($expected, $this->model->findAllByData(1, 'a', UrlRewriteFinder::ENTITY_TYPE_PRODUCT), 1);
        $this->assertSame($expected, $this->model->findAllByData(1, 1, 'cms', 1));
        $this->assertSame($expected, $this->model->findAllByData(1, 1, 'cms'));
    }

    /**
     * test findAllByData Product URL rewrites
     */
    public function testGetByIdentifiersProduct()
    {
        $data =[
            [
                'url_rewrite_id' => '1',
                'entity_type' => 'product',
                'entity_id' => '3',
                'request_path' => 'request_path',
                'target_path' => 'target_path',
                'redirect_type' => 'redirect_type',
                'store_id' => '4',
                'description' => 'description',
                'is_autogenerated' => '1',
                'metadata' => '{}'
            ]
        ];

        $dataProductMapMock = $this->createMock(DataProductUrlRewriteDatabaseMap::class);
        $this->databaseMapPoolMock->expects($this->once())
            ->method('getDataMap')
            ->with(DataProductUrlRewriteDatabaseMap::class, 1)
            ->willReturn($dataProductMapMock);

        $this->urlFinderMock->expects($this->never())
            ->method('findAllByData')
            ->willReturn([]);

        $dataProductMapMock->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $urlRewriteResultArray = $this->model->findAllByData(1, 1, UrlRewriteFinder::ENTITY_TYPE_PRODUCT, 1);
        $this->assertSame($data[0], $urlRewriteResultArray[0]->toArray());
    }

    /**
     * test findAllByData Category URL rewrites
     */
    public function testGetByIdentifiersCategory()
    {
        $data =[
            [
                'url_rewrite_id' => '1',
                'entity_type' => 'category',
                'entity_id' => '3',
                'request_path' => 'request_path',
                'target_path' => 'target_path',
                'redirect_type' => 'redirect_type',
                'store_id' => '4',
                'description' => 'description',
                'is_autogenerated' => '1',
                'metadata' => '{}'
            ]
        ];

        $dataCategoryMapMock = $this->createMock(DataCategoryUrlRewriteDatabaseMap::class);
        $this->databaseMapPoolMock->expects($this->once())
            ->method('getDataMap')
            ->with(DataCategoryUrlRewriteDatabaseMap::class, 1)
            ->willReturn($dataCategoryMapMock);

        $this->urlFinderMock->expects($this->never())
            ->method('findAllByData')
            ->willReturn([]);

        $dataCategoryMapMock->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $urlRewriteResultArray = $this->model->findAllByData(1, 1, UrlRewriteFinder::ENTITY_TYPE_CATEGORY, 1);
        $this->assertSame($data[0], $urlRewriteResultArray[0]->toArray());
    }
}
