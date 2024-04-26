<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\OrderComment\Api\Data;

/**
 * Interface OrderCommentInterface
 * @api
 */
interface OrderCommentInterface
{
    const COLUMN_NAME = 'order_comment';

    /**
     * @return string|null
     */
    public function getOrderComment();

    /**
     * @param string $comment
     * @return OrderCommentInterface
     */
    public function setOrderComment($comment);
}
