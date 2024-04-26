<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Block\Sellerhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Helper\Data as HelperData;
use Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;

class PromoWidgetChooserSku extends Sku
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Collection
     */
    protected $_productCollection;

    protected $_template = 'Magedelight_VendorPromotion::widget/grid/extended.phtml';

    /**
     * @param Context $context
     * @param HelperData $backendHelper
     * @param CollectionFactory $eavAttSetCollection
     * @param ProductCollectionFactory $cpCollection
     * @param Type $catalogType
     * @param Collection $productCollection
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $backendHelper,
        CollectionFactory $eavAttSetCollection,
        ProductCollectionFactory $cpCollection,
        Type $catalogType,
        Collection $productCollection,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        array $data = []
    ) {
        $this->_productCollection = $productCollection;
        $this->authSession = $authSession;
        $this->_storeManager = $_storeManager;
        parent::__construct($context, $backendHelper, $eavAttSetCollection, $cpCollection, $catalogType, $data);
    }

    protected function _prepareCollection()
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->_productCollection
            ->setStoreId($storeId)
            ->addAttributeToSelect('name', 'type_id', 'attribute_set_id');

        /**
         * @todo filter vendor products only using md_vendor_product table join
         */
        $collection->getSelect()->join(
            ['vendor_product' => 'md_vendor_product'],
            "e.entity_id = vendor_product.marketplace_product_id AND vendor_product.vendor_id = {$this->authSession->getUser()->getVendorId()}",
            ['sku' => 'vendor_product.vendor_sku', 'product_vendor_id' => 'vendor_product.vendor_id', 'vendor_product_id_main' => 'vendor_product.vendor_product_id']
        );

        $collection->getSelect()->join(
            ['rbvps' => 'md_vendor_product_store'],
            "rbvps.vendor_product_id = vendor_product.vendor_product_id and rbvps.store_id = {$storeId} and rbvps.website_id = {$websiteId}",
            ['store_id_main' => 'rbvps.store_id', 'website_id_main' => 'rbvps.website_id']
        );
        
        $collection->getSelect()->join(
            ['rbvpw' => 'md_vendor_product_website'],
            "rbvps.vendor_product_id = rbvpw.vendor_product_id and rbvpw.status = 1 and rbvpw.website_id = {$websiteId}",
            []
        );
        
        $this->setCollection($collection);
        return Grid::_prepareCollection();
    }
    
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'rbvendor/salesrule/chooser',
            ['_current' => true, 'current_grid_id' => $this->getId(), 'collapse' => null]
        );
    }
}
