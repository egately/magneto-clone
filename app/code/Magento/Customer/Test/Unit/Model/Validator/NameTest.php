<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Validator\Name;
use Magento\Security\Model\Validator\Pattern\NameValidator as PatternNameValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Customer name validator tests
 */
class NameTest extends TestCase
{
    /**
     * @var PatternNameValidator|MockObject
     */
    private MockObject $patternNameValidatorMock;

    /**
     * @var Name
     */
    private Name $nameValidator;

    /**
     * @var Customer|MockObject
     */
    private MockObject $customerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->patternNameValidatorMock = $this->createMock(PatternNameValidator::class);
        $this->nameValidator = new Name($this->patternNameValidatorMock);
        $this->customerMock = $this
            ->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getFirstname', 'getMiddlename', 'getLastname'])
            ->getMock();
    }

    /**
     * Test for allowed punctuation characters in customer names
     *
     * @param string $firstName
     * @param string $middleName
     * @param string $lastName
     * @param string $message
     * @return void
     * @dataProvider expectedPunctuationInNamesDataProvider
     */
    public function testValidateCorrectPunctuationInNames(
        string $firstName,
        string $middleName,
        string $lastName,
        string $message
    ) {
        $this->customerMock->expects($this->once())->method('getFirstname')->willReturn($firstName);
        $this->customerMock->expects($this->once())->method('getMiddlename')->willReturn($middleName);
        $this->customerMock->expects($this->once())->method('getLastname')->willReturn($lastName);

        $this->patternNameValidatorMock->expects($this->exactly(3))
            ->method('isValid')
            ->withConsecutive([$firstName], [$middleName], [$lastName])
            ->willReturn(true);

        $isValid = $this->nameValidator->isValid($this->customerMock);
        $this->assertTrue($isValid, $message);
    }

    /**
     * @return array
     */
    public function expectedPunctuationInNamesDataProvider(): array
    {
        return [
            [
                'firstName' => 'John',
                'middleName' => '',
                'lastName' => 'O’Doe',
                'message' => 'Inclined apostrophe must be allowed in names (iOS Smart Punctuation compatibility)'
            ],
            [
                'firstName' => 'John',
                'middleName' => '',
                'lastName' => 'O\'Doe',
                'message' => 'Legacy straight apostrophe must be allowed in names'
            ],
            [
                'firstName' => 'John',
                'middleName' => '',
                'lastName' => 'O`Doe',
                'message' => 'Grave accent back quote character must be allowed in names'
            ],
            [
                'firstName' => 'John & Smith',
                'middleName' => '',
                'lastName' => 'O`Doe',
                'message' => 'Special character ampersand(&) must be allowed in names'
            ]
        ];
    }
}
