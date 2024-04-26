<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Block\Adminhtml;

class Vendorcommission extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_vendorcommission';
        $this->_blockGroup = 'Magedelight_Commissions';
        $this->_headerText = __('Commissions');
        $this->_addButtonLabel = __('Add New Vendor commission');
        parent::_construct();
    }
}
