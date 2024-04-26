<?php

namespace Magedelight\Vendor\Model\Microsite\Build\Data;

use Magedelight\Vendor\Api\Data\Microsite\ProductFilterInterface;
use Magento\Framework\DataObject;

class ProductFilter extends DataObject implements \Magedelight\Vendor\Api\Data\Microsite\ProductFilterInterface
{

    /**
     * @inheritDoc
     */
    public function getAttributeCode()
    {
        return $this->getData(ProductFilterInterface::ATTRIBUTE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(ProductFilterInterface::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return $this->getData(ProductFilterInterface::LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setLabel($label)
    {
        return $this->setData(ProductFilterInterface::LABEL, $label);
    }

    /**
     * @inheritDoc
     */
    public function getOptions()
    {
        return $this->getData(ProductFilterInterface::OPTIONS);
    }

    /**
     * @inheritDoc
     */
    public function setOptions($options)
    {
        return $this->setData(ProductFilterInterface::OPTIONS, $options);
    }
}
