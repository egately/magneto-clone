<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

/**
 * Class StrategyTest
 * @package Magento\Indexer\Test\Unit\Model\Indexer\Table
 */
class StrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Strategy object
     *
     * @var \Magento\Framework\Indexer\Table\Strategy
     */
    protected $_model;

    /**
     * Resource mock
     *
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->_resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->_model = new \Magento\Framework\Indexer\Table\Strategy(
            $this->_resourceMock
        );
    }

    /**
     * Test use idx table switcher
     *
     * @return void
     */
    public function testUseIdxTable()
    {
        $this->assertSame(false, $this->_model->getUseIdxTable());
        $this->_model->setUseIdxTable(false);
        $this->assertSame(false, $this->_model->getUseIdxTable());
        $this->_model->setUseIdxTable(true);
        $this->assertSame(true, $this->_model->getUseIdxTable());
        $this->_model->setUseIdxTable();
        $this->assertSame(false, $this->_model->getUseIdxTable());
    }

    /**
     * Test table name preparation
     *
     * @return void
     */
    public function testPrepareTableName()
    {
        $this->assertSame('test_tmp', $this->_model->prepareTableName('test'));
        $this->_model->setUseIdxTable(true);
        $this->assertSame('test_idx', $this->_model->prepareTableName('test'));
        $this->_model->setUseIdxTable(false);
        $this->assertSame('test_tmp', $this->_model->prepareTableName('test'));
    }

    /**
     * Test table name getter
     *
     * @return void
     */
    public function testGetTableName()
    {
        $prefix = 'pre_';
        $this->_resourceMock->expects($this->any())->method('getTableName')->will(
            $this->returnCallback(
                function ($tableName) use ($prefix) {
                    return $prefix . $tableName;
                }
            )
        );
        $this->assertSame('pre_test_tmp', $this->_model->getTableName('test'));
        $this->_model->setUseIdxTable(true);
        $this->assertSame('pre_test_idx', $this->_model->getTableName('test'));
    }
}
