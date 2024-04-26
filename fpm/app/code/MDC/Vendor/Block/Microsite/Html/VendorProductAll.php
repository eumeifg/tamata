<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   MDC_Vendor
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace MDC\Vendor\Block\Microsite\Html;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;
use Magento\Framework\Url\Helper\Data;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class VendorProductAll extends \Magedelight\Vendor\Block\Microsite\Html\AbstractProduct
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = Toolbar::class;


    protected function _getProductCollection()
    {
        $productIds = array_merge(
            $this->getProductCollectionForSimple()->getAllIds(),
            $this->getProductCollectionForConfig()->getAllIds()
        );
        $collection = $this->_productCollectionFactory->create();
        $maincollection = $this->_productCollectionFactory->create();
        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToSelect('*');
            $maincollection->addFieldToSelect('*');
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $maincollection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $collection->addAttributeToFilter([['attribute'=>'hide_in_microsite','null'=> true],
            ['attribute'=>'hide_in_microsite','eq' => '0']]);
            $maincollection->addAttributeToFilter([['attribute'=>'hide_in_microsite','null'=> true],
            ['attribute'=>'hide_in_microsite','eq' => '0']]);
        } else {
            $collection->addAttributeToSelect('*');
            $maincollection->addAttributeToSelect('*');
            $collection->addAttributeToSort('archive', 'asc');
            $maincollection->addAttributeToSort('archive', 'asc');
            $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
            $maincollection->addAttributeToFilter('entity_id', ['in' => $productIds]);
            $collection->addAttributeToFilter([['attribute'=>'hide_in_microsite','null'=> true],
            ['attribute'=>'hide_in_microsite','eq' => '0']]);
            $maincollection->addAttributeToFilter([['attribute'=>'hide_in_microsite','null'=> true],
            ['attribute'=>'hide_in_microsite','eq' => '0']]);
        }

        if ($this->getRequest()->getParam('catId')) {
            $collection->addCategoryFilter($this->categoryRepository->get($this->getRequest()->getParam('catId')));
            $maincollection->addCategoryFilter($this->categoryRepository->get($this->getRequest()->getParam('catId')));
        }

        $items = [];
        foreach ($maincollection->getItems() as $item) {
            $allprices[] = (int)$item->getData('price');
        }
        $minPrice = $maxPrice = 0;
        if (!empty($allprices)){
           $minPrice = min($allprices);
           $maxPrice = max($allprices);
        }
        $session = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Model\Session::class);
        $session->setMaxPrice($maxPrice);
        $session->setMinPrice($minPrice);

        // Price Filter
        if ($this->getRequest()->getParam('price')){
            $price = $this->getRequest()->getParam('price');
            $maxprices = explode('-',$price);
            $collection->addFieldToFilter('price', array(
                                array('from' => (int)$maxprices[0], 'to' => (int)$maxprices[1]),
                )
            );
            $collection->addFieldToFilter('special_price', array(
                                array('from' => (int)$maxprices[0], 'to' => (int)$maxprices[1]),
                )
            );

        }

        /*$attributeId = $this->_eavAttribute->getIdByCode('catalog_product', 'featured_microsite');
          $collection->getSelect()->joinLeft(
            ['vprodc' => 'catalog_product_entity_int'],
            "e.row_id = vprodc.row_id AND vprodc.attribute_id=".$attributeId,
            ['featured' => 'vprodc.value']
        );
        $collection->getSelect()->order('featured DESC');*/

        $product_list_order = $this->getRequest()->getParam('product_list_order');
        if ($product_list_order == 'ms_position') {
            $microSitePositionAttrId = $this->_eavAttribute->getIdByCode('catalog_product', 'microsite_item_position');
            $collection->getSelect()->joinLeft(
                ['cpei' => 'catalog_product_entity_int'],
                "e.row_id = cpei.row_id AND cpei.attribute_id=".$microSitePositionAttrId,
                ['microsite_position' => 'cpei.value']
            );
            $collection->getSelect()->distinct(true);
            $collection->getSelect()->order('CAST(-`microsite_position` AS unsigned) DESC');
        } elseif ($product_list_order == 'ms_featured') {
            $attributeId = $this->_eavAttribute->getIdByCode('catalog_product', 'featured_microsite');
            $collection->getSelect()->joinLeft(
                ['vprodc' => 'catalog_product_entity_int'],
                "e.row_id = vprodc.row_id AND vprodc.attribute_id=".$attributeId,
                ['featured' => 'vprodc.value']
            );

            $collection->getSelect()->order('featured DESC');
        }
        else {
            $collection->getSelect()->order('created_at DESC');
        }
        /*echo $collection->getSelect(); die();*/


        $this->addToolbarBlock($collection);
        return $collection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * Add toolbar block from product listing layout
     *
     * @param Collection $collection
     */
    private function addToolbarBlock(Collection $collection)
    {
        $toolbarLayout = $this->getToolbarFromLayout();

        if ($toolbarLayout) {
            $this->configureToolbar($toolbarLayout, $collection);
        }
    }

    /**
     * Configures the Toolbar block with options from this block and configured product collection.
     *
     * The purpose of this method is the one-way sharing of different sorting related data
     * between this block, which is responsible for product list rendering,
     * and the Toolbar block, whose responsibility is a rendering of these options.
     *
     * @param ProductList\Toolbar $toolbar
     * @param Collection $collection
     * @return void
     */
    private function configureToolbar(Toolbar $toolbar, Collection $collection)
    {
        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }

    /**
     *
     * @param integer $productId
     * @return float
     * get vendor product price
     */
    public function getVendorProductPrice($productId = '')
    {
        $vendorId = $this->getRequest()->getParam('vid');
        $vendorProductPrice = $this->vendorHelperData->getVendorFinalPrice($vendorId, $productId);
        return $this->priceHelper->currency($vendorProductPrice, true, false);
    }

    /**
     * Get toolbar block from layout
     *
     * @return bool|Toolbar
     */
    private function getToolbarFromLayout()
    {
        $blockName = $this->getToolbarBlockName();

        $toolbarLayout = false;

        if ($blockName) {
            $toolbarLayout = $this->getLayout()->getBlock($blockName);
        }

        return $toolbarLayout;
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        if ($this->getChildBlock('toolbar')) {
            return $this->getChildBlock('toolbar')->getCurrentMode();
        }

        return $this->getDefaultListingMode();
    }

    /**
     * Get listing mode for products if toolbar is removed from layout.
     * Use the general configuration for product list mode from config path catalog/frontend/list_mode as default value
     * or mode data from block declaration from layout.
     *
     * @return string
     */
    private function getDefaultListingMode()
    {
        // default Toolbar when the toolbar layout is not used
        $defaultToolbar = $this->getToolbarBlock();
        $availableModes = $defaultToolbar->getModes();

        // layout config mode
        $mode = $this->getData('mode');

        if (!$mode || !isset($availableModes[$mode])) {
            // default config mode
            $mode = $defaultToolbar->getCurrentMode();
        }

        return $mode;
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from another block.
     * (was problem with search result)
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $collection = $this->_getProductCollection();

        $this->addToolbarBlock($collection);

        if (!$collection->isLoaded()) {
            $collection->load();
        }

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar block from layout or a default Toolbar
     *
     * @return Toolbar
     */
    public function getToolbarBlock()
    {
        $block = $this->getToolbarFromLayout();

        if (!$block) {
            $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        }

        return $block;
    }

    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }
}
