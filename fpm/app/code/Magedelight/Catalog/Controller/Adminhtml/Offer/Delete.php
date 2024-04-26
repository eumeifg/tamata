<?php
/*
 * Copyright © 2018 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
namespace Magedelight\Catalog\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

class Delete extends Action
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magedelight\Catalog\Model\VendorProductRepository
     */
    protected $vendorProductRepository;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Catalog\Model\VendorProductRepository $vendorProductRepository
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Catalog\Model\VendorProductRepository $vendorProductRepository
    ) {
        $this->vendorProductRepository = $vendorProductRepository;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $vendorProduct = $this->vendorProductRepository->getById($id);
                if ($vendorProduct->getId()) {
                    $mainProduct = $vendorProduct->getData('marketplace_product_id');
                    $this->vendorProductRepository->delete($vendorProduct);
                    $this->coreRegistry->unregister('vendor_offer');
                    $this->messageManager->addSuccess(__('This Supplier Offer has been deleted.'));
                    return $this->_redirect('catalog/product/edit', ['id' => $mainProduct]);
                } else {
                    $this->messageManager->addError(__('This Supplier Offer does not exist.'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        return $this->_redirect('catalog/product/index');
    }
}
