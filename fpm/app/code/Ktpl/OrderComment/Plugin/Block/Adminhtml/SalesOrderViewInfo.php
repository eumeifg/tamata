<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\OrderComment\Plugin\Block\Adminhtml;

use Magento\Sales\Block\Adminhtml\Order\View\Info as ViewInfo;
use Ktpl\OrderComment\Api\Data\OrderCommentInterface;

class SalesOrderViewInfo
{
    /**
     * @param ViewInfo $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        ViewInfo $subject,
        $result
    ) {
        $commentBlock = $subject->getLayout()
            ->getBlock('order_comments');

        if ($commentBlock !== false) {
            $commentBlock->setOrderComment($subject->getOrder()
                ->getData(OrderCommentInterface::COLUMN_NAME));
            $result = $result . $commentBlock->toHtml();
        }

        return $result;
    }
}
