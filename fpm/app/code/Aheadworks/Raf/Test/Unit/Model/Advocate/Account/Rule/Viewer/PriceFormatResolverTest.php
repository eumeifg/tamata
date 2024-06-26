<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Test\Unit\Model\Advocate\Account\Rule\Viewer;

use Aheadworks\Raf\Model\Advocate\Account\Rule\Viewer\PriceFormatResolver;
use Aheadworks\Raf\Model\Advocate\PriceFormatter;
use Aheadworks\Raf\Model\Source\Rule\BaseOffType;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class PriceFormatResolverTest
 *
 * @package Aheadworks\Raf\Test\Unit\Model\Advocate\Account\Rule\Viewer
 */
class PriceFormatResolverTest extends TestCase
{
    /**
     * List of constants used for testing
     */
    const PRICE = 14.50;
    const STORE_ID = 1;

    /**
     * @var PriceFormatResolver
     */
    private $object;

    /**
     * @var PriceFormatter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceFormatterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->priceFormatterMock = $this->createPartialMock(
            PriceFormatter::class,
            ['getFormattedFixedPriceByStore', 'getFormattedPercentPrice']
        );

        $this->object = $objectManager->getObject(
            PriceFormatResolver::class,
            [
                'priceFormatter' => $this->priceFormatterMock
            ]
        );
    }

    /**
     * Test for resolve method in case price type is fixed
     */
    public function testResolveMethodForFixedPrice()
    {
        $result = 'some_formatted_price';
        $priceType = BaseOffType::FIXED;

        $this->priceFormatterMock->expects($this->once())
            ->method('getFormattedFixedPriceByStore')
            ->with(self::PRICE, self::STORE_ID)
            ->willReturn($result);
        $this->assertSame($result, $this->object->resolve(self::PRICE, $priceType, self::STORE_ID));
    }

    /**
     * Test for resolve method in case price type is fixed
     */
    public function testResolveMethodForOtherPriceType()
    {
        $result = 'some_formatted_price';
        $priceType = 'other_type';

        $this->priceFormatterMock->expects($this->once())
            ->method('getFormattedPercentPrice')
            ->with(self::PRICE)
            ->willReturn($result);
        $this->assertSame($result, $this->object->resolve(self::PRICE, $priceType, self::STORE_ID));
    }
}
