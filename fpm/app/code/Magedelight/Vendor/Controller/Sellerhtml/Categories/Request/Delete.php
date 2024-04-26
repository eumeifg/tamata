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

use Magedelight\Vendor\Model\Category\Request\Source\Status as RequestStatuses;

class Delete extends \Magedelight\Backend\App\Action
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
    
    public function execute()
    {
        $this->design->applyVendorDesign();
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $requestId = $this->getRequest()->getParam('id');
        try {
            $request = $this->categoryRequestRepository->getById($requestId);
            if ($request->getId() && $request->getStatus() == RequestStatuses::STATUS_PENDING) {
                $this->categoryRequestRepository->delete($request);
                $this->messageManager->addSuccess(
                    __('Category request has been successfully deleted.')
                );
            } else {
                $this->messageManager->addSuccess(
                    __('You cannot delete this request.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/', ['tab' => $this->getRequest()->getParam('tab')]);
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
