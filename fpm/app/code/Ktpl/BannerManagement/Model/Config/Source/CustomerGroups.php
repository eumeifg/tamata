<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;

/**
 * Class Type
 *
 * @package Ktpl\BannerManagement\Model\Config\Source
 */
class CustomerGroups implements ArrayInterface
{
     /**
      * @var \Magento\Customer\Api\GroupRepositoryInterface
      */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    /**
     * Slider constructor.
     *
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param DataObject               $objectConverter
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject $objectConverter
    ) {
        $this->groupRepository       = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter       = $objectConverter;
    }
    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options= $this->getCustomerGroups();
        return $options;
    }
    public function getCustomerGroups()
    {
        $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $options=$this->objectConverter->toOptionArray($customerGroups, 'id', 'code');
        return $options;
    }
}
