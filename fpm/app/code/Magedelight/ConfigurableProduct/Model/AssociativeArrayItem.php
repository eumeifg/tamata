<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Model;

use Magedelight\ConfigurableProduct\Api\Data\AssociativeArrayItemInterface;

class AssociativeArrayItem extends \Magento\Framework\DataObject implements AssociativeArrayItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->getData(AssociativeArrayItemInterface::KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setKey($key)
    {
        return $this->setData(AssociativeArrayItemInterface::KEY, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(AssociativeArrayItemInterface::KEY_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this->setData(AssociativeArrayItemInterface::KEY_VALUE, $value);
    }
}
