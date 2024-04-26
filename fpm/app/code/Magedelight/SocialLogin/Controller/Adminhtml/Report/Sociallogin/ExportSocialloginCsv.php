<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\SocialLogin\Controller\Adminhtml\Report\Sociallogin;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportSocialloginCsv extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Export sales report by category grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'social_login.csv';
        $grid = $this->_view->getLayout()->createBlock('Magedelight\SocialLogin\Block\Adminhtml\SocialLogin\SocialLogin\Grid');
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
