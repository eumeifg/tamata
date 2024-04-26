<?php

namespace Magedelight\Abandonedcart\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportMyCustomReportExcel extends \Magento\Reports\Controller\Adminhtml\Report\Sales
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
        $fileName = 'mycustomreport.xml';
        $grid = $this->_view->getLayout()->createBlock(\Magedelight\Abandonedcart
           \Block\Adminhtml\Sales\Mycustomreport\Grid::class);
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName), DirectoryList::VAR_DIR);
    }
}
