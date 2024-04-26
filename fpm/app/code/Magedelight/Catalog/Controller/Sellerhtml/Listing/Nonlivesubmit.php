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

class Nonlivesubmit extends \Magedelight\Backend\App\Action
{
    protected $_product;
    protected $_productRequest;
    protected $_cacheInterface;
    protected $productWebsiteRepository;
    protected $ProductWebsiteFactory;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Catalog\Model\Product $product
     * @param \Magedelight\Catalog\Model\ProductRequest $productRequest
     * @param \Magento\Framework\App\CacheInterface $cacheInterface
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $ProductWebsiteFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\Product $product,
        \Magedelight\Catalog\Model\ProductRequest $productRequest,
        \Magento\Framework\App\CacheInterface $cacheInterface,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $ProductWebsiteFactory
    ) {
        parent::__construct($context);
        $this->_product = $product;
        $this->_productRequest = $productRequest;
        $this->_cacheInterface = $cacheInterface;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->ProductWebsiteFactory = $ProductWebsiteFactory;
    }

    public function execute()
    {
        $extraid = $this->getRequest()->getParam('extraid_id');
        $id = $this->getRequest()->getParam('vendor_product_id');
        $vendorid = $this->getRequest()->getParam('vendor_id');
        if ($this->getRequest()->getPost()) {
            try {
                if ($extraid == 0) {
                    $model = $this->_productRequest->load($id);

                    $model->setData('qty', $this->getRequest()->getParam('vendorproduct_qty'));
                    $model->setData('price', $this->getRequest()->getParam('vendorproduct_price'));
                    if ($this->getRequest()->getParam('vendorproduct_spprice', false)) {
                        $ProductWebsite->setData(
                            'special_price',
                            $this->getRequest()->getParam('vendorproduct_spprice')
                        );
                    } else {
                        $ProductWebsite->unsetData('special_price');
                    }
                    $model->setData('vendor_id', $vendorid);

                    $ProductWebsite->setData(
                        'special_from_date',
                        $this->getRequest()->getParam('nonlivespecial_from_date')
                    );
                    $ProductWebsite->setData(
                        'special_to_date',
                        $this->getRequest()->getParam('nonlivespecial_to_date')
                    );
                    $ProductWebsite->setData(
                        'reorder_level',
                        $this->getRequest()->getParam('vendorproductreorder_level')
                    );
                    $model->save();
                }
                if ($extraid == 1) {
                    $model = $this->_product->load($id);
                    $model->setData('vendor_id', $vendorid);

                    $model->setData(
                        'qty',
                        $this->getRequest()->getParam('vendorproduct_qty')
                    );

                    if ($id) {
                        $ProductWebsite = $this->productWebsiteRepository->getProductWebsiteData((int)$id);
                    } else {
                        $ProductWebsite = $this->ProductWebsiteFactory->create();
                    }

                    $ProductWebsite->setData(
                        'price',
                        $this->getRequest()->getParam('vendorproduct_price')
                    );
                    if ($this->getRequest()->getParam(
                        'vendorproduct_spprice',
                        false
                    )) {
                        $ProductWebsite->setData(
                            'special_price',
                            $this->getRequest()->getParam('vendorproduct_spprice')
                        );
                    } else {
                        $ProductWebsite->unsetData('special_price');
                    }

                    $ProductWebsite->setData(
                        'special_from_date',
                        $this->getRequest()->getParam('nonlivespecial_from_date')
                    );
                    $ProductWebsite->setData(
                        'special_to_date',
                        $this->getRequest()->getParam('nonlivespecial_to_date')
                    );
                    $ProductWebsite->setData(
                        'reorder_level',
                        $this->getRequest()->getParam('vendorproductreorder_level')
                    );
                    $model->save();
                    $this->productWebsiteRepository->save($ProductWebsite);
                }
                $this->messageManager->addSuccess(
                    __('Product saved successfully')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Product data not found.'));
        }

        $tab = $this->getRequest()->getParam('tab');
        $vpro = $this->getRequest()->getParam('vpro');
        $sfrm = $this->getRequest()->getParam('sfrm');

        $this->_redirect('rbcatalog/listing/index/tab/' . $tab . '/vpro/' . $vpro . '/sfrm/' . $sfrm);
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
