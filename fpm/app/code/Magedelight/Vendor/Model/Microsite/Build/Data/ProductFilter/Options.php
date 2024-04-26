<?php

namespace Magedelight\Vendor\Model\Microsite\Build\Data\ProductFilter;

use Magedelight\Vendor\Api\Data\Microsite\ProductFilter\OptionsInterface;

class Options extends \Magento\Framework\DataObject implements \Magedelight\Vendor\Api\Data\Microsite\ProductFilter\OptionsInterface
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return $this->getData(OptionsInterface::LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setLabel($label)
    {
        return $this->setData(OptionsInterface::LABEL, $label);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->getData(OptionsInterface::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        return $this->setData(OptionsInterface::VALUE, $value);
    }
}
