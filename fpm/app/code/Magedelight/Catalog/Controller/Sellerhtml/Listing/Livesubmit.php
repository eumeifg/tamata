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
namespace Magedelight\Catalog\Controller\Sellerhtml\Listing;

use Magento\Framework\Stdlib\DateTime\DateTime;

class Livesubmit extends \Magedelight\Backend\App\Action
{
    protected $_product;
    protected $_cacheInterface;
    protected $productWebsiteRepository;
    protected $ProductWebsiteFactory;
    protected $_datetime;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Catalog\Model\Product $product
     * @param \Magento\Framework\App\CacheInterface $cacheInterface
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $ProductWebsiteFactory
     * @param DateTime $datetime
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\Product $product,
        \Magento\Framework\App\CacheInterface $cacheInterface,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $ProductWebsiteFactory,
        DateTime $datetime
    ) {
        $this->_product = $product;
        $this->_cacheInterface = $cacheInterface;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->ProductWebsiteFactory = $ProductWebsiteFactory;
        $this->_datetime = $datetime;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('vendor_product_id');
        $vendorid = $this->getRequest()->getParam('vendor_id');
        if ($this->getRequest()->getPost()) {
            try {
                $model = $this->_product->load($id);
                $oldQty = $model->getQty();
                $model->setData('vendor_id', $vendorid);
                $model->setData('qty', $this->getRequest()->getParam('vendorproduct_qty'));

                if ($id) {
                    $ProductWebsite = $this->productWebsiteRepository->getProductWebsiteData((int) $id);
                } else {
                    $ProductWebsite = $this->ProductWebsiteFactory->create();
                }
                $specialpriceFromDate = null;
                if ($this->getRequest()->getParam('livespecial_from_date')) {
                    $specialpriceFromDate = $this->getRequest()->getParam('livespecial_from_date');
                } else {
                    $specialpriceFromDate = $this->_datetime->gmtDate();
                }
                $specialPrice = ($this->getRequest()->getParam('vendorproduct_spprice')) ?: null;
                $ProductWebsite->setData('special_price', $specialPrice);
                $ProductWebsite->setData('special_from_date', $specialpriceFromDate);
                $ProductWebsite->setData('special_to_date', $this->getRequest()->getParam('livespecial_to_date'));
                $ProductWebsite->setData(
                    'reorder_level',
                    $this->getRequest()->getParam('vendorproductreorder_level')
                );
                $ProductWebsite->setData('price', $this->getRequest()->getParam('vendorproduct_price'));

                $model->save();
                $this->productWebsiteRepository->save($ProductWebsite);

                $eventParams = [
                    'marketplace_product_id' => $model->getMarketplaceProductId(),
                    'old_qty' => $oldQty,
                    'vendor_product' => $model,
                ];
                $this->_eventManager->dispatch('frontend_vendor_product_save_after', $eventParams);
                $this->_cacheInterface->clean('catalog_product_' . $model->getMarketplaceProductId());

                $this->messageManager->addSuccess(
                    __('Product saved successfully.')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Product data not found'));
        }
        $queryParams = [
            'p' => $this->getRequest()->getParam('p', 1),
            'sfrm' => $this->getRequest()->getParam('sfrm', 'l'),
            'limit' => $this->getRequest()->getParam('limit', 10),
        ];
        $this->_redirect('rbcatalog/listing/index', ['tab' => '1,0', '_query' => $queryParams]);
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
