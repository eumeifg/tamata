<?php

declare(strict_types=1);

namespace MDC\GetItTogether\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use MDC\GetItTogether\Api\Data\OrderGetItTogetherInterface;
/**
 * 
 */
class OrderGetItTogether extends AbstractSimpleObject implements OrderGetItTogetherInterface
{
	
	 /**
     * {@inheritDoc}
     */
    public function getGetItTogether()
    {
        return $this->_get(self::COLUMN_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setGetItTogether($getItTogether)
    {
        return $this->setData(self::COLUMN_NAME, $getItTogether);
    }
}