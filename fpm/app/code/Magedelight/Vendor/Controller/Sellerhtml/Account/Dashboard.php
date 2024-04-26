<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Sellerhtml\Account;

use Magento\Framework\View\Result\PageFactory;
use Magedelight\Backend\App\Action\Context;

class Dashboard extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    private $design;
    
    /**
     *
     * @var PageFactory
     */
    protected $pageFactory;

    public function __construct(
        Context $context,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->design = $design;
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->design->applyVendorDesign();
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Dashboard'));
        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::account_dashboard');
    }
}
