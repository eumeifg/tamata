<?php
namespace CAT\VIP\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

class Edit extends Action
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \CAT\VIP\Model\VIPProductsFactory $vendorProductFactory
    ) {
        $this->vendorProductFactory = $vendorProductFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->vendorProductFactory->create();
        if ($id) {
            $model->setWebsiteId($this->getRequest()->getParam('website_id'));
            $model->setStoreId($this->getRequest()->getParam('store_id'));
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Product no longer exists. '));
            }
        }
        $this->coreRegistry->register('vip_offer', $model);
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
