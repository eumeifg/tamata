<?php

namespace CAT\AIRecommend\Block;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;
use Magento\Framework\Url\Helper\Data;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use CAT\AIRecommend\Helper\Data as AIdata;

class ProductAll extends \Magedelight\Vendor\Block\Microsite\Html\AbstractProduct
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = Toolbar::class;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magedelight\Catalog\Helper\Data $vendorHelperData,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        CategoryRepositoryInterface $categoryRepository,
        FlatState $flatState,
        Data $urlHelper,
        AIdata $helperData,
        array $data = []
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->vendorHelperData = $vendorHelperData;
        $this->priceHelper = $priceHelper;
        $this->urlHelper = $urlHelper;
        $this->helperData = $helperData;
        $this->_eavAttribute = $eavAttribute;
        $this->flatState = $flatState;
        parent::__construct($context,$reviewFactory,$catalogProductVisibility,$productCollectionFactory,$vendorHelperData,$priceHelper,$eavAttribute,$categoryRepository,$flatState,$urlHelper,$data);
    }


    protected function _getProductCollection()
    {
        $AIProducts = $this->helperData->getAIProducts();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToSort('archive', 'asc');
        $collection->addAttributeToFilter('entity_id', ['in' => $AIProducts]);
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
