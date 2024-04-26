<?php

namespace MDC\Catalog\Model;

use \Magedelight\Catalog\Api\Data\VendorProductInterface;

class ProductAttributes extends \Magento\Framework\DataObject implements \MDC\Catalog\Api\Data\ProductAttributesInterface
{

    /**
     * {@inheritDoc}
     */
    public function getAttributeLabel()
    {
        return $this->getData(self::ATTRIBUTE_LABEL);
    }

    /**
     * {@inheritDoc}
     */
    public function setAttributeLabel($label)
    {
        return $this->setData(self::ATTRIBUTE_LABEL, $label);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeValue()
    {
        return $this->getData(self::ATTRIBUTE_VALUE);
    }

    /**
     * {@inheritDoc}
     */
    public function setAttributeValue($value)
    {
        return $this->setData(self::ATTRIBUTE_VALUE, $value);
    }
}
