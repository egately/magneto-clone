<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_helper = null;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Json\Helper\Data::class
        );
    }

    public function testJsonEncodeDecode()
    {
        $data = ['one' => 1, 'two' => 'two'];
        $jsonData = '{"one":1,"two":"two"}';
        $this->assertSame($jsonData, $this->_helper->jsonEncode($data));
        $this->assertSame($data, $this->_helper->jsonDecode($jsonData));
    }
}
