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

use Magedelight\ConfigurableProduct\Api\Data\ConfigurableOptionDataInterface;

class ConfigurableOptionData extends \Magento\Framework\DataObject implements ConfigurableOptionDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(ConfigurableOptionDataInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(ConfigurableOptionDataInterface::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(ConfigurableOptionDataInterface::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(string $label)
    {
        return $this->setData(ConfigurableOptionDataInterface::LABEL, $label);
    }

    /**
     * {@inheritdoc}
     */
    public function getSwatchValue()
    {
        return $this->getData(ConfigurableOptionDataInterface::SWATCH);
    }

    /**
     * {@inheritdoc}
     */
    public function setSwatchValue($swatch)
    {
        return $this->setData(ConfigurableOptionDataInterface::SWATCH, $swatch);
    }
}
