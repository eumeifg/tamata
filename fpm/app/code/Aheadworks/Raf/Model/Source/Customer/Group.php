<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source\Customer;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Convert\DataObject;
use Magento\Customer\Api\Data\GroupInterface;

/**
 * Class Group
 * @package Aheadworks\Raf\Model\Source\Customer
 */
class Group implements OptionSourceInterface
{
    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var DataObject
     */
    private $objectConverter;

    /**
     * Group constructor.
     * @param GroupManagementInterface $groupManagement
     * @param DataObject $objectConverter
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        DataObject $objectConverter
    ) {
        $this->groupManagement = $groupManagement;
        $this->objectConverter = $objectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $groups = $this->groupManagement->getLoggedInGroups();
        return $this->objectConverter->toOptionArray($groups, GroupInterface::ID, GroupInterface::CODE);
    }
}
