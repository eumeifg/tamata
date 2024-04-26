<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Test\Unit\Model\Service;

use Aheadworks\Raf\Model\Service\QuoteService;
use Aheadworks\Raf\Api\Data\TotalsInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class QuoteServiceTest
 *
 * @package Aheadworks\Raf\Test\Unit\Model\Service
 */
class QuoteServiceTest extends TestCase
{
    /**
     * @var QuoteService
     */
    private $object;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->quoteRepositoryMock = $this->getMockForAbstractClass(
            CartRepositoryInterface::class
        );

        $this->object = $objectManager->getObject(
            QuoteService::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock
            ]
        );
    }

    /**
     * Testing of updateReferralLink method
     */
    public function testUpdateReferralLink()
    {
        $quoteId = 129;
        $referralLink = 'RAF123456789';

        $quoteMock = $this->createPartialMock(Quote::class, ['getId', 'setData']);

        $quoteMock->expects($this->once())
            ->method('setData')
            ->with(TotalsInterface::AW_RAF_REFERRAL_LINK, $referralLink)
            ->willReturnSelf();

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId)
            ->willReturn($quoteMock);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock)
            ->willReturn($quoteMock);

        $this->assertSame(true, $this->object->updateReferralLink($quoteId, $referralLink));
    }

    /**
     * Testing of updateReferralLink method on exception
     */
    public function testUpdateReferralLinkOnException()
    {
        $quoteId = 765;
        $referralLink = 'RAF123456789';

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId)
            ->willThrowException(new NoSuchEntityException());

        $this->assertSame(false, $this->object->updateReferralLink($quoteId, $referralLink));
    }
}
