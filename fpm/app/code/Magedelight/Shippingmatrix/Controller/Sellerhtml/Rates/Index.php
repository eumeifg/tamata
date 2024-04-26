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
namespace Magedelight\Shippingmatrix\Controller\Sellerhtml\Rates;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;

class Index extends \Magedelight\Backend\App\Action
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
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @param Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magedelight\Vendor\Model\Design $design,
        UrlFactory $urlFactory,
        PageFactory $resultPageFactory,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->urlModel = $urlFactory->create();
        $this->design = $design;
        $this->helper = $helper;
        parent::__construct($context);
    }
        
    public function execute()
    {
        $this->design->applyVendorDesign();
        $resultPage = $this->resultPageFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->helper->getConfigValue('carriers/rbmatrixrate/active')) {
            $resultRedirect->setPath('rbvendor/account/dashboard', ['_secure' => true]);
            return $resultRedirect;
        }
        $resultPage->getConfig()->getTitle()->set(__('Shipping Rates'));
        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::shippingmethod');
    }
}
