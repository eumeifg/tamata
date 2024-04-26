<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Controller\Adminhtml\Categorycommission;

use Magedelight\Commissions\Controller\Adminhtml\Commissions;
use Magento\Framework\Registry;
use Magedelight\Commissions\Model\CommissionFactory;
use Magedelight\Commissions\Api\CategoryCommissionRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Commissions
{
    /**
     * backend session
     *
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * constructor
     *
     * @param Registry $registry
     * @param $categoryCommissionRepository $commissionFactory
     * @param PageFactory $resultPageFactory
     * @param Context $context
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        CategoryCommissionRepositoryInterface $categoryCommissionRepository,
        Context $context
    ) {
        $this->backendSession = $context->getSession();
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($registry, $categoryCommissionRepository, $context);
    }
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $commissionId = $this->getRequest()->getParam('commission_id');
        if ($commissionId) {
            $commission = $this->initCommission($commissionId);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Magedelight_Commissions::commission');

            if (!$commission->getId()) {
                $this->messageManager->addError(__('This commission no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'md_commissions/*/edit',
                    [
                        'commission_id' => $commission->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Category Commission'));
            $resultPage->getLayout()->unsetChild("page.main.actions", "store_switcher");
        } else {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Magedelight_Commissions::commission');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Add New Category Commission'));
        }
        
        $data = $this->backendSession->getData('md_commissions_commission_data', true);
        if (!empty($data)) {
            $commission->setData($data);
        }
        return $resultPage;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::view_detail');
    }
}
