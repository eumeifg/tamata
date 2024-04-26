<?php

namespace Magedelight\Vendor\Model\Microsite\Build\Data;

use Magedelight\Vendor\Api\Data\Microsite\SortFieldsInterface;
use Magento\Framework\DataObject;

class SortFields extends DataObject implements \Magedelight\Vendor\Api\Data\Microsite\SortFieldsInterface
{

    /**
     * @inheritDoc
     */
    public function getDefault()
    {
        return $this->getData(SortFieldsInterface::DEFAULT);
    }

    /**
     * @inheritDoc
     */
    public function setDefault($default)
    {
        return $this->setData(SortFieldsInterface::DEFAULT, $default);
    }

    /**
     * @inheritDoc
     */
    public function getOptions()
    {
        return $this->getData(SortFieldsInterface::OPTIONS);
    }

    /**
     * @inheritDoc
     */
    public function setOptions($options)
    {
        return $this->setData(SortFieldsInterface::OPTIONS, $options);
    }
}
