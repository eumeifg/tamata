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

use Magedelight\Catalog\Api\Data\SortOrderInterface;

class SortOrderData extends \Magento\Framework\DataObject implements SortOrderInterface
{

   /**
    * {@inheritdoc}
    */
    public function getKey()
    {
        return $this->getData(SortOrderInterface::KEY_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setKey($key)
    {
        return $this->setData(SortOrderInterface::KEY_KEY, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(SortOrderInterface::KEY_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        return $this->setData(SortOrderInterface::KEY_LABEL, $label);
    }
}
