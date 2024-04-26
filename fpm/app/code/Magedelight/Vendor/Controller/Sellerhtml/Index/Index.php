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
namespace Magedelight\Vendor\Controller\Sellerhtml\Index;

/**
 * Description of Index
 *
 * @author Rocket Bazaar Core Team
 */
/**
 * Seller Panel index controller
 * @author Rocket Bazaar Core Team
 *  Created at 21 Jan, 2018 12:23:07 PM
 */
class Index extends \Magedelight\Vendor\Controller\Sellerhtml\Index
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @return type
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\Design $design,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->design = $design;
        $this->pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        if ($this->_auth->isLoggedIn()) {
            $this->_redirect('rbvendor/account/dashboard');
            return;
        }
        $requestUrl = $this->getRequest()->getUri();
        $backendUrl = $this->getUrl('*');
        // redirect according to rewrite rule
        if ($requestUrl != $backendUrl) {
            return $this->getRedirect($backendUrl);
        }
        $this->design->applyVendorDesign();
        return $this->pageFactory->create();
    }
}
