<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\AppendedData;

use Magedelight\Catalog\Api\Data\ProductAdditionalAttributeDataInterface;
use Magento\Framework\DataObject;

class ProductAdditionalAttributeData extends DataObject implements ProductAdditionalAttributeDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData('label');
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(string $attributeLabel)
    {
        return $this->setData('label', $attributeLabel);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData('value');
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(string $attributeValue)
    {
        return $this->setData('value', $attributeValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $attributeCode)
    {
        return $this->setData('code', $attributeCode);
    }
}
