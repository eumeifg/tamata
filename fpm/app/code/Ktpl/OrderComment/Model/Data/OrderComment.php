<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\OrderComment\Model\Data;

use Ktpl\OrderComment\Api\Data\OrderCommentInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class OrderComment extends AbstractSimpleObject implements OrderCommentInterface
{
    /**
     * {@inheritDoc}
     */
    public function getOrderComment()
    {
        return $this->_get(self::COLUMN_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderComment($comment)
    {
        return $this->setData(self::COLUMN_NAME, $comment);
    }
}
