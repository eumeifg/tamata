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

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * @author Rocket Bazaar Core Team
 *  Created at 16 March, 2016 3:10 PM
 */
class ForgotPassword extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;
    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @param Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory
    ) {
        $this->design = $design;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Forgot vendor password page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->_session->isLoggedIn()) {
            $resultRedirect->setPath('rbvendor/account/index');
            return $resultRedirect;
        }
        
        $this->design->applyVendorDesign();
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('forgotPassword')->setEmailValue($this->_session->getForgottenEmail());

        $this->_session->unsForgottenEmail();

        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}
