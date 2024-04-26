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
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest\Grid\Renderer;

class ProductName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $mpid = $row->getData('marketplace_product_id');
        //$pname =  ($mpid != null && $mpid != '') ? $row->getData('vendorpname') : $row->getData('name');
        $pname =  ($mpid != null && $mpid != '') ? $row->getData('name') : $row->getData('name');
        return $pname;
    }
}
