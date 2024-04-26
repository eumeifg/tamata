<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Test\Unit\Model\Advocate\Account\Checker;

use Aheadworks\Raf\Model\Advocate\Account\Checker\CustomerGroup;
use Aheadworks\Raf\Model\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CustomerGroupTest
 *
 * @package Aheadworks\Raf\Test\Unit\Model\Advocate\Account\Checker
 */
class CustomerGroupTest extends TestCase
{
    /**
     * Constants defined for testing
     */
    const CUSTOMER_ID = 10;
    const WEBSITE_ID = 1;

    /**
     * @var CustomerGroup
     */
    private $object;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->configMock = $this->createPartialMock(
            Config::class,
            ['getCustomerGroupsToJoinReferralProgram']
        );

        $this->object = $objectManager->getObject(
            CustomerGroup::class,
            [
                'config' => $this->configMock,
                'customerRepository' => $this->customerRepositoryMock
            ]
        );
    }

    /**
     * Testing isCustomerInReferralProgramGroup method
     */
    public function testIsCustomerInReferralProgramGroup()
    {
        $rafGroups = '1,2,3';
        $currentGroup = '3';

        $this->configMock->expects($this->once())
            ->method('getCustomerGroupsToJoinReferralProgram')
            ->with(self::WEBSITE_ID)
            ->willReturn($rafGroups);
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($customerMock);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($currentGroup);

        $this->assertSame(true, $this->object->isCustomerInReferralProgramGroup(self::CUSTOMER_ID, self::WEBSITE_ID));
    }

    /**
     * Testing isCustomerInReferralProgramGroup method on exception
     */
    public function testIsCustomerInReferralProgramGroupOnException()
    {
        $rafGroups = '1,2,3';
        $exception = new NoSuchEntityException(__('some_exception'));

        $this->configMock->expects($this->once())
            ->method('getCustomerGroupsToJoinReferralProgram')
            ->with(self::WEBSITE_ID)
            ->willReturn($rafGroups);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willThrowException($exception);

        $this->assertSame(false, $this->object->isCustomerInReferralProgramGroup(self::CUSTOMER_ID, self::WEBSITE_ID));
    }
}
