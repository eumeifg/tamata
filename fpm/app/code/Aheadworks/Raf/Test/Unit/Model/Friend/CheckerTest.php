<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Test\Unit\Model\Friend;

use Aheadworks\Raf\Model\Friend\Checker;
use Aheadworks\Raf\Api\Data\FriendMetadataInterface;
use Aheadworks\Raf\Model\ResourceModel\Friend\Order as FriendOrderResource;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CheckerTest
 *
 * @package Aheadworks\Raf\Test\Unit\Model\Friend
 */
class CheckerTest extends TestCase
{
    /**
     * @var Checker
     */
    private $object;

    /**
     * @var FriendOrderResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $friendOrderResourceMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->friendOrderResourceMock = $this->createPartialMock(
            FriendOrderResource::class,
            ['getNumberOfOrders']
        );

        $this->object = $objectManager->getObject(
            Checker::class,
            [
                'friendOrderResource' => $this->friendOrderResourceMock,
            ]
        );
    }

    /**
     * Test for canApplyDiscount method
     *
     * @dataProvider canApplyDiscountProvider
     * @param int $numberOfOrders
     * @param bool $result
     */
    public function testCanApplyDiscount($numberOfOrders, $result)
    {
        $friendMetadataMock =  $this->getMockForAbstractClass(FriendMetadataInterface::class);
        $this->friendOrderResourceMock->expects($this->once())
            ->method('getNumberOfOrders')
            ->with($friendMetadataMock)
            ->willReturn($numberOfOrders);
        $this->assertSame($result, $this->object->canApplyDiscount($friendMetadataMock));
    }

    /**
     * Data provider for testCanApplyDiscount method
     *
     * @return array
     */
    public function canApplyDiscountProvider()
    {
        return [
            'case 1' => [2, false],
            'case 2' => [0, true],
        ];
    }
}
