<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var \Magento\Config\Model\Config\SchemaLocator
     */
    protected $_model;

    protected function setUp()
    {
        $this->_moduleReaderMock = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);

        $this->_moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Config'
        )->will(
            $this->returnValue('schema_dir')
        );
        $this->_model = new \Magento\Config\Model\Config\SchemaLocator($this->_moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertSame('schema_dir/system.xsd', $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertSame('schema_dir/system_file.xsd', $this->_model->getPerFileSchema());
    }
}
