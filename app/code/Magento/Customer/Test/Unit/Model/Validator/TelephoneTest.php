<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\Telephone;
use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\GlobalPhoneValidation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Customer telephone validator tests
 */
class TelephoneTest extends TestCase
{
    /**
     * @var Telephone
     */
    private Telephone $telephoneValidator;

    /**
     * @var GlobalPhoneValidation
     */
    private GlobalPhoneValidation $globalPhoneValidation;

    /**
     * @var Customer|MockObject
     */
    private MockObject $customerMock;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->telephoneValidator = new Telephone();
        $this->globalPhoneValidation = new GlobalPhoneValidation();
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getTelephone'])
            ->getMock();
    }

    /**
     * Test for allowed characters in customer telephone numbers.
     *
     * @param string $telephone
     * @param string $message
     * @return void
     * @dataProvider expectedPunctuationInTelephoneDataProvider
     */
    public function testValidateCorrectPunctuationInTelephone(
        string $telephone,
        string $message
    ) {
        $this->customerMock->expects($this->once())->method('getTelephone')->willReturn($telephone);

        // Validate using the Telephone validator
        $isValid = $this->telephoneValidator->isValid($this->customerMock);
        $this->assertTrue($isValid, $message);

        // Validate using the GlobalPhoneValidation directly
        $isValidGlobal = $this->globalPhoneValidation->isValidPhone($telephone);
        $this->assertTrue($isValidGlobal, $message);
    }

    /**
     * Data provider for testValidateCorrectPunctuationInTelephone.
     *
     * @return array
     */
    public function expectedPunctuationInTelephoneDataProvider(): array
    {
        return [
            [
                'telephone' => '(1)99887766',
                'message' => 'Parentheses must be allowed in telephone numbers.'
            ],
            [
                'telephone' => '+6255554444',
                'message' => 'Plus sign must be allowed in telephone numbers.'
            ],
            [
                'telephone' => '555-555-555',
                'message' => 'Hyphen must be allowed in telephone numbers.'
            ],
            [
                'telephone' => '123456789',
                'message' => 'Digits (numbers) must be allowed in telephone numbers.'
            ]
        ];
    }
}
