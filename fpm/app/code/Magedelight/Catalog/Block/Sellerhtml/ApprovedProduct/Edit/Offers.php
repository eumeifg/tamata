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
namespace Magedelight\Catalog\Block\Sellerhtml\ApprovedProduct\Edit;

class Offers extends \Magedelight\Catalog\Block\Sellerhtml\ApprovedProduct\Edit
{

    public function getProductConditionOption()
    {
        return $this->_productCondition->toOptionArray();
    }

    public function getAttributesHtml()
    {
        
        return '';
    }

    public function getUniqueskuPostActionUrl()
    {
        return $this->getUrl('rbcatalog/product/uniquesku');
    }

    public function getUniquemanufacturerskuPostActionUrl()
    {
        $editid = $this->getRequest()->getParam('id');
        if ($editid) {
            return $this->getUrl('rbcatalog/product/uniquemanufacturersku', ['id' => $editid]);
        } else {
            return $this->getUrl('rbcatalog/product/uniquemanufacturersku');
        }
    }

    public function checkManufacturerSkuValidation()
    {
        return $this->vendorHelper->checkManufacturerSkuValidation();
    }
}
