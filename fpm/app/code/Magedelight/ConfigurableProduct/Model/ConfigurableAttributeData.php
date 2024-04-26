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

use Magedelight\ConfigurableProduct\Api\Data\ConfigurableAttributeDataInterface;

class ConfigurableAttributeData extends \Magento\Framework\DataObject implements ConfigurableAttributeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(ConfigurableAttributeDataInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(ConfigurableAttributeDataInterface::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(ConfigurableAttributeDataInterface::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code)
    {
        return $this->setData(ConfigurableAttributeDataInterface::CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendInput()
    {
        return $this->getData(ConfigurableAttributeDataInterface::FRONTEND_INPUT);
    }

    /**
     * {@inheritdoc}
     */
    public function setFrontendInput(string $frontendInput)
    {
        return $this->setData(ConfigurableAttributeDataInterface::FRONTEND_INPUT, $frontendInput);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(ConfigurableAttributeDataInterface::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(string $label)
    {
        return $this->setData(ConfigurableAttributeDataInterface::LABEL, $label);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->getData(ConfigurableAttributeDataInterface::OPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        return $this->setData(ConfigurableAttributeDataInterface::OPTIONS, $options);
    }
}
