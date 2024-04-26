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
namespace Magedelight\Catalog\Controller\Adminhtml\ProductRequest;

class MassDelete extends \Magedelight\Catalog\Controller\Adminhtml\ProductRequest
{

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $productRequestFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
    ) {
        $this->productRequestFactory = $productRequestFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('product_request');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select product request(s).'));
        } else {
            try {
                $eventParams = ['ids' => $ids];
                $this->_eventManager->dispatch('vendor_product_admin_mass_delete', $eventParams);
                foreach ($ids as $id) {
                    $model = $this->productRequestFactory->create()->load($id);
                    $model->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::vendor_products_disapproved_delete');
    }
}
