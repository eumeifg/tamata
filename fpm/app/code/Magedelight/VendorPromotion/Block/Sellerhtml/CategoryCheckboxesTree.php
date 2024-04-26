<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Block\Sellerhtml;

use Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree;

class CategoryCheckboxesTree extends Tree
{
    public function getLoadTreeUrl($expanded = null)
    {
        $params = ['_current'=>true, 'id'=>null,'store'=>null];
        if ($expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('rbvendor/salesrule/categoriesJson', $params);
    }
    
    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Magedelight_VendorPromotion::salesrule/renderer/fieldset/conditions/category/checkboxes/tree.phtml');
    }
}
