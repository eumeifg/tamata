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

class Delete extends \Magedelight\Catalog\Controller\Adminhtml\ProductRequest
{
    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $productRequest;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
    ) {
        $this->productRequest = $productRequestFactory->create();
        parent::__construct($context);
    }
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $this->productRequest->load($id);
            $params = [];
            if ($this->productRequest->getStatus() == 2) {
                $params['status'] = 2;
            } elseif ($this->productRequest->getStatus() == 0) {
                $params['existing'] = 1;
                $params['status'] = 0;
            }
            $eventParams = ['id' => $id];
            $this->_eventManager->dispatch('vendor_product_admin_delete', $eventParams);
            $this->productRequest->delete();
            $this->messageManager->addSuccess(
                __('Product request deleted successfully.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/', $params);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::product_request_existing_delete');
    }
}
