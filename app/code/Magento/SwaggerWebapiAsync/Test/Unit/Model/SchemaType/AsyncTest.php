<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SwaggerWebapiAsync\Test\Unit\Model\SchemaType;

use Magento\Swagger\Api\Data\SchemaTypeInterface;
use Magento\SwaggerWebapiAsync\Model\SchemaType\Async;

class AsyncTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SchemaTypeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $async;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->async = new Async('async');
    }

    /**
     * @covers \Magento\SwaggerWebapiAsync\Model\SchemaType\Async::getCode()
     */
    public function testGetCode()
    {
        $this->assertSame('async', $this->async->getCode());
    }

    /**
     * @covers \Magento\SwaggerWebapiAsync\Model\SchemaType\Async::getSchemaUrlPathProvider
     *
     * @param null|string $store
     * @param $expected
     *
     * @dataProvider getSchemaUrlPathProvider
     */
    public function testGetSchemaUrlPath($expected, $store = null)
    {
        $this->assertSame($expected, $this->async->getSchemaUrlPath($store));
    }

    /**
     * @return array
     */
    public function getSchemaUrlPathProvider()
    {
        return [
            [
                '/rest/all/async/schema?services=all',
                null
            ],
            [
                '/rest/test/async/schema?services=all',
                'test'
            ]
        ];
    }
}
