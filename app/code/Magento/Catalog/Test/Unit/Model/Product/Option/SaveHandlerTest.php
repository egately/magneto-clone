<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Repository;
use Magento\Catalog\Model\Product\Option\SaveHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    /**
     * @var SaveHandler|MockObject
     */
    protected $model;

    /**
     * @var Product|MockObject
     */
    protected $entity;

    /**
     * @var Option|MockObject
     */
    protected $option;

    /**
     * @var Repository|MockObject
     */
    protected $optionRepository;

    protected function setUp(): void
    {
        $this->entity = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->methods(['getOptionsSaved', 'getCanSaveCustomOptions', 'getOptions', 'dataHasChangedFor', 'getSku'])
            ->getMock();
        $this->option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionRepository = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SaveHandler($this->optionRepository);
    }

    public function testExecute()
    {
        $this->entity->expects($this->once())->method('getOptionsSaved')->willReturn(false);
        $this->entity->expects($this->once())->method('getCanSaveCustomOptions')->willReturn(true);
        $this->option->expects($this->any())->method('getOptionId')->willReturn(5);
        $this->entity->expects($this->once())->method('getOptions')->willReturn([$this->option]);

        $secondOptionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $secondOptionMock->expects($this->once())->method('getOptionId')->willReturn(6);

        $this->optionRepository
            ->expects($this->once())
            ->method('getProductOptions')
            ->with($this->entity)
            ->willReturn([$this->option, $secondOptionMock]);

        $this->optionRepository->expects($this->once())->method('delete');
        $this->optionRepository->expects($this->once())->method('save')->with($this->option);

        $this->assertEquals($this->entity, $this->model->execute($this->entity));
    }
}
