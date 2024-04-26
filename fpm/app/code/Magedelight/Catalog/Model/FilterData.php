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
namespace Magedelight\Catalog\Model;

use Magedelight\Catalog\Api\Data\FilterInterface;

class FilterData extends \Magento\Framework\DataObject implements FilterInterface
{

   /**
    * {@inheritdoc}
    */
    public function getLabel()
    {
        return $this->getData(FilterInterface::KEY_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        return $this->setData(FilterInterface::KEY_LABEL, $label);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(FilterInterface::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        return $this->setData(FilterInterface::KEY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->getData(FilterInterface::KEY_OPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        return $this->setData(FilterInterface::KEY_OPTIONS, $options);
    }
}
