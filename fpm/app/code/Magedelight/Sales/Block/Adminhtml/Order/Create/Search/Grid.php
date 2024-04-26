<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Block\Adminhtml\Order\Create\Search;

/**
 * Description of Grid
 *
 * @author Rocket Bazaar Core Team
 */
class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid
{

    protected function _prepareColumns()
    {
        $this->addColumn(
            'vendor_id',
            [
            'header' => __('Vendor ID'),
            'header_css_class' => 'no-display',
            'column_css_class' => 'no-display',
            'index' => '1'
            ]
        );
    }
}
