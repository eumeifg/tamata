<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Controller\Adminhtml\Report\Sales;

class MyCustomReport extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Check is allowed for report.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Abandonedcart::abandonedcart_reports');
    }

    public function execute()
    {

        $this->_initAction()->_setActiveMenu(
            'Magedelight_Abandonedcart::report_mycustomreport'
        )->_addBreadcrumb(
            __('Abandoned Cart Reports'),
            __('Abandoned Cart Reports')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Abandoned Cart Reports'));
        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_mycustomreport.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
