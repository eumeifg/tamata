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
namespace Magedelight\Vendor\Controller\Sellerhtml\Categories\Request;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magedelight\Vendor\Model\CategoryRequest;

class View extends \Magedelight\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface
     */
    protected $categoryRequestRepository;
    
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;
    
    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\Design $design,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->categoryRequestRepository = $categoryRequestRepository;
        $this->design = $design;
        parent::__construct($context);
    }
    
    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $requestId = $this->getRequest()->getParam('id');
        if ($requestId) {
            try {
                $request = $this->categoryRequestRepository->getById($requestId);
                
                if (!$request->getId()) {
                    $this->messageManager->addErrorMessage(__('No such category request found.'));
                    return $resultRedirect->setPath('rbvendor/categories_request/index');
                } elseif ($request->getVendorId() != $this->_auth->getUser()->getVendorId()) {
                    /* Block current vendor from opening other vendor's requests. */
                    $this->messageManager->addErrorMessage(__('No such category request found.'));
                    return $resultRedirect->setPath('rbvendor/categories_request/index');
                }
            } catch (NoSuchEntityException $e) {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('rbvendor/categories_request/index');
            }
        }
        $this->design->applyVendorDesign();
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(CategoryRequest::REQUEST_PREFIX.$request->getRequestId());
        return $resultPage;
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products_requested_categories');
    }
}
