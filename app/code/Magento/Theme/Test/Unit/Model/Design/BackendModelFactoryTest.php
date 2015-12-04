<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design;

class BackendModelFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Model\Design\BackendModelFactory */
    protected $model;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    /** @var \Magento\Theme\Model\Design\Config\MetadataProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $metadataProviderMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /** @var \Magento\Theme\Model\ResourceModel\Design\Config\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $collection;

    /** @var \Magento\Framework\App\Config\Value|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModel;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->getMockForAbstractClass();
        $this->metadataProviderMock = $this->getMockBuilder('Magento\Theme\Model\Design\Config\MetadataProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(
                'Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory'
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Design\Config\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModel = $this->getMockBuilder('Magento\Framework\App\Config\Value')
            ->disableOriginalConstructor()
            ->setMethods(['setValue'])
            ->getMock();
        
        $this->model = new \Magento\Theme\Model\Design\BackendModelFactory(
            $this->objectManagerMock,
            $this->metadataProviderMock,
            $this->collectionFactoryMock
        );
    }

    public function testCreate()
    {
        $scope = 'website';
        $scopeId = 1;
        $data = [
            'scope' => $scope,
            'scopeId' => $scopeId,
            'value' => 'value',
            'config' => [
                'path' => 'design/head/default_title',
                'backend_model' => 'Magento\Framework\App\Config\Value'
            ]
        ];
        $this->metadataProviderMock->expects($this->once())
            ->method('get')
            ->willReturn([
                'head_default_title' => [
                    'path' => 'design/head/default_title'
                ]
            ]);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collection);
        $this->collection->expects($this->once())
            ->method('addPathsFilter')
            ->with(['head_default_title' => 'design/head/default_title']);
        $this->collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('scope', $scope);
        $this->collection->expects($this->once())
            ->method('addScopeIdFilter')
            ->with($scopeId);
        $this->collection->expects($this->once())
            ->method('getData')
            ->willReturn([
                [
                    'config_id' => 1
                ]
            ]);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                'Magento\Framework\App\Config\Value',
                [
                    'data' => [
                        'path' => 'design/head/default_title',
                        'scope' => $scope,
                        'scope_id' => $scopeId,
                        'field_config' => $data['config'],
                        'config_id' => 1
                    ]
                ]
            )
            ->willReturn($this->backendModel);
        $this->backendModel->expects($this->once())
            ->method('setValue')
            ->willReturn('value');
        $this->assertSame($this->backendModel, $this->model->create($data));
    }
}
