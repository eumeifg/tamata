<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\Order\Item;

use Magedelight\Sales\Api\Data\OrderItemAttributeInfoInterface;

class OrderItemAttributeInfo extends \Magento\Framework\DataObject implements OrderItemAttributeInfoInterface
{
    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return $this->getData('label');
    }

    /**
     * {@inheritDoc}
     */
    public function setLabel($label)
    {
        return $this->setData('label', $label);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        return $this->getData('value');
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        return $this->setData('value', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionId()
    {
        return $this->getData('option_id');
    }

    /**
     * {@inheritDoc}
     */
    public function setOptionId($optionId)
    {
        return $this->setData('option_id', $optionId);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionValue()
    {
        return $this->getData('option_value');
    }

    /**
     * {@inheritDoc}
     */
    public function setOptionValue($optionValue)
    {
        return $this->setData('option_value', $optionValue);
    }
}
