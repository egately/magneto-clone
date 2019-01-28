<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\Config;

use Magento\Analytics\ReportXml\Config\Mapper;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp()
    {
        $this->mapper = new Mapper();
    }

    public function testExecute()
    {
        $configData['config'][0]['report'] = [
            [
                'source' => ['product'],
                'name' => 'Product',
            ]
        ];
        $expectedResult = [
          'Product' => [
              'source' => 'product',
              'name' => 'Product',
          ]
        ];
        $this->assertSame($this->mapper->execute($configData), $expectedResult);
    }

    public function testExecuteWithoutReports()
    {
        $configData = [];
        $this->assertSame($this->mapper->execute($configData), []);
    }
}
