<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Test\Unit\Model\Service;

use Aheadworks\Raf\Model\Service\RuleService;
use Aheadworks\Raf\Api\Data\RuleInterface;
use Aheadworks\Raf\Api\RuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Raf\Api\Data\RuleSearchResultsInterface;

/**
 * Class RuleServiceTest
 *
 * @package Aheadworks\Raf\Test\Unit\Model\Service
 */
class RuleServiceTest extends TestCase
{
    /**
     * @var RuleService
     */
    private $object;

    /**
     * @var RuleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->ruleRepositoryMock = $this->getMockForAbstractClass(
            RuleRepositoryInterface::class
        );

        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = $objectManager->getObject(
            RuleService::class,
            [
                'ruleRepository' => $this->ruleRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * Testing of getActiveRule method
     */
    public function testGetActiveRule()
    {
        $websiteId = 2;

        $ruleMock = $this->getMockForAbstractClass(RuleInterface::class);

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(RuleInterface::WEBSITE_IDS, $websiteId)
            ->will($this->returnSelf());

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteriaMock));

        $searchResultsMock = $this->getMockForAbstractClass(RuleSearchResultsInterface::class);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$ruleMock]));

        $this->ruleRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->will($this->returnValue($searchResultsMock));

        $this->assertSame($ruleMock, $this->object->getActiveRule($websiteId));
    }
}
