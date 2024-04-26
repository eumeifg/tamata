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

class RelatedData extends \Magento\Framework\DataObject implements \Magedelight\Catalog\Api\Data\RelatedDataInterface
{
     /**
     * {@inheritdoc}
     */
    public function getMinPrice()
    {
        return $this->getData('vendor_min_price');
    }

     /**
     * {@inheritdoc}
     */
    public function setMinPrice($minPrice)
    {
        return $this->setData('vendor_min_price',$minPrice);
    }

     /**
     * {@inheritdoc}
     */
    public function getMinSpecialPrice()
    {
        return $this->getData('vendor_min_sp_price');
    }

     /**
     * {@inheritdoc}
     */
    public function setMinSpecialPrice($minSpPrice)
    {
        return $this->setData('vendor_min_sp_price',$minSpPrice);
    }
     
}
