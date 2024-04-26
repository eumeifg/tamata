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
namespace Magedelight\Vendor\Block\Sellerhtml\Dashboard\Products;

use Magento\Store\Model\StoreRepository;

class ProductsViewed extends \Magedelight\Vendor\Block\Sellerhtml\Dashboard\AbstractDashboard
{
    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     *
     * @var type
     */
    protected $_template = 'Magedelight_Vendor::dashboard/products_viewed.phtml';

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_vendorProductCollectionFactory;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    protected $_storeRepository;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Catalog\Model\ResourceModel\Reports\Product\CollectionFactory $vendorProductCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param StoreRepository $storeRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Catalog\Model\ResourceModel\Reports\Product\CollectionFactory $vendorProductCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        StoreRepository $storeRepository,
        array $data = []
    ) {
        $this->_vendorProductCollectionFactory = $vendorProductCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->vendorHelper = $vendorHelper;
        $this->_storeRepository = $storeRepository;
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        if ($this->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } elseif ($this->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else {
            $storeId = (int)$this->getParam('store');
        }
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        $stores = $this->_storeRepository->getList();
        $storeList = [];
        foreach ($stores as $store) {
            $storeId = $store["store_id"];
            if ($websiteId == $store["website_id"]) {
                $storeList[] = $storeId;
            }
        }

        $collection = $this->_vendorProductCollectionFactory->create()->addFieldToSelect(
            '*'
        )->addViewsCount();
        $collection->addFieldToFilter('report_table_views.store_id', ['in' => $storeList]);
        $collection->getSelect()->join(
            ['rvp'=> 'md_vendor_product'],
            'e.entity_id = rvp.marketplace_product_id',
            ['marketplace_product_id','vendor_id']
        );
        $collection->addFieldToFilter('vendor_id', ['eq' => $this->authSession->getUser()->getVendorId()]);

        if (!empty($this->vendorHelper->getConfigValue('vendor/dashboard_summary/most_viewed_products'))) {
            $collection->setPageSize($this->vendorHelper->getConfigValue(
                'vendor/dashboard_summary/most_viewed_products'
            ))->setCurPage(1);
        }

        /*->setStoreId(
            $storeId
        )->addStoreFilter(
            $storeId
        );*/

        return $collection;
    }

    /**
     * @return \Magedelight\Catalog\Model\ResourceModel\Reports\Product\Collection|\Magento\Framework\Data\Collection\AbstractDb|\Magento\Reports\Model\ResourceModel\Product\Collection
     */
    public function getCollection()
    {
        return $this->_prepareCollection();
    }

    /**
     * @param $sku
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($sku)
    {
        $product = $this->_productRepository->get($sku);
        return $product->getName();
    }
}
