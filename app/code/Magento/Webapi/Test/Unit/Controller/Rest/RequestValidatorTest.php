<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Controller\Rest;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Controller\Rest\RequestValidator;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\Router\Route;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestValidatorTest
 */
class RequestValidatorTest extends TestCase
{
    /**
     * Service method.
     *
     * @var string
     */
    const SERVICE_METHOD = 'testMethod';

    /**
     * Service id.
     *
     * @var string
     */
    const SERVICE_ID = 'Magento\Webapi\Controller\Rest\TestService';

    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @var Request|MockObject
     */
    private $requestMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var Authorization|MockObject
     */
    private $authorizationMock;

    /**
     * @var MockObject|Route
     */
    private $routeMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Request::class)
            ->onlyMethods(
                [
                    'isSecure',
                    'getRequestData',
                    'getParams',
                    'getParam',
                    'getRequestedServices',
                    'getPathInfo',
                    'getHttpHost',
                    'getMethod',
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $this->requestMock
            ->expects($this->any())
            ->method('getHttpHost')
            ->willReturn('testHostName.com');
        $routerMock = $this->getMockBuilder(Router::class)
            ->onlyMethods(['match'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->routeMock = $this->getMockBuilder(Route::class)
            ->onlyMethods(['isSecure', 'getServiceMethod', 'getServiceClass', 'getAclResources', 'getParameters'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(Authorization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);

        $this->requestValidator =
            $objectManager->getObject(
                RequestValidator::class,
                [
                    'request' => $this->requestMock,
                    'router' => $routerMock,
                    'authorization' => $this->authorizationMock,
                    'storeManager' => $this->storeManagerMock
                ]
            );

        // Set default expectations used by all tests
        $this->routeMock->expects($this->any())->method('getServiceClass')->willReturn(self::SERVICE_ID);
        $this->routeMock->expects($this->any())->method('getServiceMethod')
            ->willReturn(self::SERVICE_METHOD);
        $routerMock->expects($this->any())->method('match')->willReturn($this->routeMock);

        parent::setUp();
    }

    /**
     * Test Secure Request and Secure route combinations.
     *
     * @param bool $isSecureRoute
     * @param bool $isSecureRequest
     *
     * @return void
     * @throws AuthorizationException|Exception
     * @dataProvider dataProviderSecureRequestSecureRoute
     */
    public function testSecureRouteAndRequest(bool $isSecureRoute, bool $isSecureRequest): void
    {
        $this->routeMock->expects($this->any())->method('isSecure')->willReturn($isSecureRoute);
        $this->routeMock->expects($this->any())->method('getAclResources')->willReturn(['1']);
        $this->requestMock->expects($this->any())->method('getRequestData')->willReturn([]);
        $this->requestMock->expects($this->any())->method('isSecure')->willReturn($isSecureRequest);
        $this->authorizationMock->expects($this->once())->method('isAllowed')->willReturn(true);
        $this->requestValidator->validate();
    }

    /**
     * Data provider for testSecureRouteAndRequest.
     *
     * @return array
     */
    public function dataProviderSecureRequestSecureRoute(): array
    {
        // Each array contains return type for isSecure method of route and request objects.
        return [[true, true], [false, true], [false, false]];
    }

    /**
     * Test insecure request for a secure route.
     *
     * @return void
     * @throws AuthorizationException
     */
    public function testInSecureRequestOverSecureRoute(): void
    {
        $this->expectException('Magento\Framework\Webapi\Exception');
        $this->expectExceptionMessage('Operation allowed only in HTTPS');
        $this->routeMock->expects($this->any())->method('isSecure')->willReturn(true);
        $this->routeMock->expects($this->any())->method('getAclResources')->willReturn(['1']);
        $this->requestMock->expects($this->any())->method('isSecure')->willReturn(false);
        $this->authorizationMock->expects($this->once())->method('isAllowed')->willReturn(true);

        $this->requestValidator->validate();
    }

    /**
     * Test authorization failed.
     *
     * @return void
     * @throws AuthorizationException|Exception
     */
    public function testAuthorizationFailed(): void
    {
        $this->expectException('Magento\Framework\Exception\AuthorizationException');
        $this->expectExceptionMessage('The consumer isn\'t authorized to access 5, 6.');
        $this->authorizationMock->expects($this->once())->method('isAllowed')->willReturn(false);
        $this->routeMock->expects($this->any())->method('getAclResources')->willReturn(['5', '6']);
        $this->requestValidator->validate();
    }
}
