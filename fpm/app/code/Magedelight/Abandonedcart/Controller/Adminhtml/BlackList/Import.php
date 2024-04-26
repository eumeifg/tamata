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

namespace Magedelight\Abandonedcart\Controller\Adminhtml\BlackList;
 
use Magedelight\Abandonedcart\Controller\Adminhtml\Blacklist;
use Magento\Framework\Controller\ResultFactory;

class Import extends Blacklist
{
    
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magedelight_Abandonedcart::blacklist');
        $resultPage->addBreadcrumb(__('Abandonedcart'), __('Import CSV'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import Blacklist CSV'));
        return $resultPage;
    }
}
