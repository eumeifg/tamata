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

use Magedelight\Catalog\Api\Data\FilterOptionDataInterface;

class FilterOptionData extends \Magento\Framework\DataObject implements FilterOptionDataInterface
{

   /**
    * {@inheritdoc}
    */
    public function getLabel()
    {
        return $this->getData(FilterOptionDataInterface::KEY_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        return $this->setData(FilterOptionDataInterface::KEY_LABEL, $label);
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getData(FilterOptionDataInterface::KEY_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCount($count)
    {
        return $this->setData(FilterOptionDataInterface::KEY_COUNT, $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(FilterOptionDataInterface::KEY_OPTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(FilterOptionDataInterface::KEY_OPTION_ID, $id);
    }
}
