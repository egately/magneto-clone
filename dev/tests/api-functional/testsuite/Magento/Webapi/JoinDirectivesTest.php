<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\FilterBuilder;

class JoinDirectivesTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\User\Model\User
     */
    private $user;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->searchBuilder = $objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->sortOrderBuilder = $objectManager->create(\Magento\Framework\Api\SortOrderBuilder::class);
        $this->filterBuilder = $objectManager->create(\Magento\Framework\Api\FilterBuilder::class);
        $this->user = $objectManager->create(\Magento\User\Model\User::class);
    }

    /**
     * Rollback rules
     * @magentoApiDataFixture Magento/SalesRule/_files/rules_rollback.php
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetList()
    {
        /** @var SortOrder $sortOrder */
        $sortOrder = $this->sortOrderBuilder->setField('store_id')->setDirection(SortOrder::SORT_ASC)->create();
        $this->searchBuilder->setSortOrders([$sortOrder]);
        $searchCriteria = $this->searchBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchCriteria];

        $restResourcePath = '/V1/TestModuleJoinDirectives/';
        $soapService = 'testModuleJoinDirectivesTestRepositoryV1';
        $expectedExtensionAttributes = $this->getExpectedExtensionAttributes();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $restResourcePath . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => $soapService,
                'operation' => $soapService . 'GetList',
            ],
        ];
        $searchResult = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertArrayHasKey('items', $searchResult);
        $itemData = array_pop($searchResult['items']);
        $this->assertArrayHasKey('extension_attributes', $itemData);
        $this->assertArrayHasKey('quote_api_test_attribute', $itemData['extension_attributes']);
        $testAttribute = $itemData['extension_attributes']['quote_api_test_attribute'];
        $this->assertSame($expectedExtensionAttributes['firstname'], $testAttribute['first_name']);
        $this->assertSame($expectedExtensionAttributes['lastname'], $testAttribute['last_name']);
        $this->assertSame($expectedExtensionAttributes['email'], $testAttribute['email']);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/invoice.php
     */
    public function testAutoGeneratedGetList()
    {
        $this->markTestSkipped(
            'Invoice repository is not autogenerated anymore and does not have joined extension attributes'
        );
        $this->getExpectedExtensionAttributes();
        /** @var SortOrder $sortOrder */
        $sortOrder = $this->sortOrderBuilder->setField('store_id')->setDirection(SortOrder::SORT_ASC)->create();
        $this->searchBuilder->setSortOrders([$sortOrder]);
        $this->searchBuilder->addFilters([$this->filterBuilder->setField('state')->setValue(2)->create()]);
        $searchCriteria = $this->searchBuilder->create()->__toArray();
        $requestData = ['criteria' => $searchCriteria];

        $restResourcePath = '/V1/invoices/';
        $soapService = 'salesInvoiceRepositoryV1';
        $expectedExtensionAttributes = $this->getExpectedExtensionAttributes();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $restResourcePath . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => $soapService,
                'operation' => $soapService . 'GetList',
            ],
        ];
        $searchResult = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertArrayHasKey('items', $searchResult);
        $itemData = array_pop($searchResult['items']);
        $this->assertArrayHasKey('extension_attributes', $itemData);
        $this->assertArrayHasKey('invoice_api_test_attribute', $itemData['extension_attributes']);
        $testAttribute = $itemData['extension_attributes']['invoice_api_test_attribute'];
        $this->assertSame($expectedExtensionAttributes['firstname'], $testAttribute['first_name']);
        $this->assertSame($expectedExtensionAttributes['lastname'], $testAttribute['last_name']);
        $this->assertSame($expectedExtensionAttributes['email'], $testAttribute['email']);
    }

    /**
     * Retrieve the admin user's information.
     *
     * @return array
     */
    private function getExpectedExtensionAttributes()
    {
        $this->user->load(1);
        return [
            'firstname' => $this->user->getFirstname(),
            'lastname' => $this->user->getLastname(),
            'email' => $this->user->getEmail()
        ];
    }
}
