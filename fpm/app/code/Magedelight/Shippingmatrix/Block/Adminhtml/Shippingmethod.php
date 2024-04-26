<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Block\Adminhtml;

class Shippingmethod extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_shippingmethod';
        $this->_blockGroup = 'Magedelight_Shippingmatrix';
        $this->_headerText = __('Shipping Method');
        $this->_addButtonLabel = __('Add New Shipping Method');
        parent::_construct();
    }
}
