<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Test\Unit\Model\Rule\Discount\Customer\Advocate;

use Aheadworks\Raf\Api\AdvocateManagementInterface;
use Magento\Quote\Model\Quote;
use Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate\Validator;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;

/**
 * Class ValidatorTest
 *
 * @package Aheadworks\Raf\Test\Unit\Model\Rule\Discount\Customer\Advocate
 */
class ValidatorTest extends TestCase
{
    /**
     * Validator
     */
    private $object;

    /**
     * @var AdvocateManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $advocateManagementMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->advocateManagementMock = $this->getMockForAbstractClass(
            AdvocateManagementInterface::class
        );

        $this->object = $objectManager->getObject(
            Validator::class,
            [
                'advocateManagement' => $this->advocateManagementMock
            ]
        );
    }

    /**
     * Test for isValid method
     *
     * @dataProvider isValidDataProvider
     * @param int $customerId
     * @param bool $isParticipant
     * @param bool $canUseRaf
     * @param bool $result
     */
    public function testIsValid($customerId, $isParticipant, $canUseRaf, $result)
    {
        $websiteId = 1;

        $quote = $this->createPartialMock(
            Quote::class,
            ['getCustomerId', 'getStore']
        );
        $store = $this->createPartialMock(
            Store::class,
            ['getWebsiteId']
        );
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $quote->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $quote->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $this->advocateManagementMock->expects($this->any())
            ->method('isParticipantOfReferralProgram')
            ->with($customerId, $websiteId)
            ->willReturn($isParticipant);
        $this->advocateManagementMock->expects($this->any())
            ->method('canUseReferralProgramAndSpend')
            ->with($customerId, $websiteId)
            ->willReturn($canUseRaf);

        $this->assertSame($result, $this->object->isValid($quote));
    }

    /**
     * Data provider for testIsValid method
     */
    public function isValidDataProvider()
    {
        return [
            'everything is OK' => [1, true, true, true],
            'customer id is not specified' => [null, true, true, false],
            'customer is not participant' => [1, false, true, false],
            'customer cannot use raf program' => [1, true, false, false]
        ];
    }
}
