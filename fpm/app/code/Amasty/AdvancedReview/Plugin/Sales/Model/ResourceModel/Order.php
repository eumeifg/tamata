<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_AdvancedReview
 */


namespace Amasty\AdvancedReview\Plugin\Sales\Model\ResourceModel;

use Amasty\AdvancedReview\Plugin\Sales\Model\Service\OrderService;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\ResourceModel\Order as OrderSubject;

class Order extends OrderService
{
    /**
     * @param OrderSubject $subject
     * @param AbstractModel $object
     *
     * @return array
     */
    public function aroundSave(OrderSubject $subject, \Closure $proceed, AbstractModel $object)
    {
        $statusBefore = $object->getOrigData('status');
        $result = $proceed($object);
        $triggerOrderStatus = $this->config->getTriggerOrderStatus((int)$object->getStoreId());

        if ($this->config->isReminderEnabled((int)$object->getStoreId())
            && count($triggerOrderStatus)
            && $object->getStatus() != $statusBefore
            && in_array($object->getStatus(), $triggerOrderStatus)
        ) {
            $this->saveOrderToReminder($object);
        }

        return $result;
    }
}
