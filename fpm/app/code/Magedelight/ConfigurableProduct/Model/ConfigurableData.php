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

use Magedelight\ConfigurableProduct\Api\Data\ConfigurableDataInterface;

class ConfigurableData extends \Magento\Framework\DataObject implements ConfigurableDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->getData(ConfigurableDataInterface::ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes($attributes)
    {
        return $this->setData(ConfigurableDataInterface::ATTRIBUTES, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        return $this->getData(ConfigurableDataInterface::DEFAULT_VALUES);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultValues(array $defaultValues)
    {
        return $this->setData(ConfigurableDataInterface::DEFAULT_VALUES, $defaultValues);
    }

    /**
     * {@inheritdoc}
     */
    public function getInStockIds()
    {
        return $this->getData(ConfigurableDataInterface::IN_STOCK_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setInStockIds(array $productIds)
    {
        return $this->setData(ConfigurableDataInterface::IN_STOCK_IDS, $productIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex()
    {
        return $this->getData(ConfigurableDataInterface::INDEX);
    }

    /**
     * {@inheritdoc}
     */
    public function setIndex($index)
    {
        return $this->setData(ConfigurableDataInterface::INDEX, $index);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMobileAttributes(){
        return $this->getData(ConfigurableDataInterface::MOBILEATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    public function setMobileAttributes($mobileAttributes){
        return $this->setData(ConfigurableDataInterface::MOBILEATTRIBUTES, $mobileAttributes);
    }

}
