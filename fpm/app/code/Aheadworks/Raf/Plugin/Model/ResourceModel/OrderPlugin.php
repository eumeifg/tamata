<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Plugin\Model\ResourceModel;

use Aheadworks\Raf\Api\AdvocateRewardManagementInterface;
use Aheadworks\Raf\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class OrderPlugin
 *
 * @package Aheadworks\Raf\Plugin\Model\ResourceModel
 */
class OrderPlugin
{
    /**
     * @var AdvocateRewardManagementInterface
     */
    private $advocateRewardManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AdvocateRewardManagementInterface $advocateRewardManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        AdvocateRewardManagementInterface $advocateRewardManagement,
        LoggerInterface $logger
    ) {
        $this->advocateRewardManagement = $advocateRewardManagement;
        $this->logger = $logger;
    }

    /**
     * Save order
     *
     * @param \Magento\Sales\Model\ResourceModel\Order $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order|OrderInterface $object
     * @return \Magento\Sales\Model\ResourceModel\Order
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave($subject, \Closure $proceed, $object)
    {
        $result = $proceed($object);
        try {
            $this->advocateRewardManagement->giveRewardForFriendPurchase($object->getStore()->getWebsiteId(), $object);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return $result;
    }
}
