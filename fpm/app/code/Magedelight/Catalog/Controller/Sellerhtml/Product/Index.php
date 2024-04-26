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
namespace Magedelight\Catalog\Controller\Sellerhtml\Product;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param UrlFactory $urlFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magedelight\Vendor\Model\Design $design,
        \Magedelight\Catalog\Helper\Data $helper,
        UrlFactory $urlFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->urlModel = $urlFactory->create();
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
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->helper->getConfigValue('sellexisting/general/enable')) {
            $url = $this->urlModel->getUrl('rbvendor/account/dashboard', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->success($url));
            return $resultRedirect;
        }

        $search = $this->getRequest()->getParam('q');
        $category = $this->getRequest()->getParam('category');
        if ($search) {
            $url = $this->urlModel->getUrl(
                'rbcatalog/product/index',
                ['tab' => '1,2','_query' => ['search' => $search,'category'=>$category], '_secure' => true]
            );
            $resultRedirect->setUrl($this->_redirect->success($url));
            return $resultRedirect;
        }
        $this->design->applyVendorDesign();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Select and Sell'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
