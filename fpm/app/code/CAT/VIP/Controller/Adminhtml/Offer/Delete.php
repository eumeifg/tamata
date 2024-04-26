<?php

namespace CAT\VIP\Controller\Adminhtml\Offer;

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
    protected $vipProductRepository;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \CAT\VIP\Model\VIPProductsFactory $vipProductRepository
    ) {
        $this->vipProductRepository = $vipProductRepository;
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
                $vipProduct = $this->vipProductRepository->create()->load($id);
                if ($vipProduct->getId()) {
                    $mainProduct = $vipProduct->getData('product_id');
                    $vipProduct->delete();
                    return $this->_redirect('catalog/product/edit', ['id' => $mainProduct]);
                } else {
                    $this->messageManager->addError(__('This VIP Offer does not exist.'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        return $this->_redirect('catalog/product/index');
    }
}
