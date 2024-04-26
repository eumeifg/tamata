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
namespace Magedelight\Catalog\Controller\Adminhtml\Offer;

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
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
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
        $this->coreRegistry->register('vendor_offer', $model);
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
