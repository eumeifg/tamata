<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Controller\Sellerhtml\Bulkimport;

class Index extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;
    
    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\Design $design,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->design = $design;
        $this->helper = $helper;
        parent::__construct($context);
    }
    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->design->applyVendorDesign();
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->helper->getConfigValue('md_bulkimport/general/enable')) {
            $resultRedirect->setPath('rbvendor/account/dashboard', ['_secure' => true]);
            return $resultRedirect;
        }
        $resultPage->getConfig()->getTitle()->set(__('Bulk Import'));
        return $resultPage;
    }
    
    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
