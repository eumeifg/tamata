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
namespace Magedelight\Catalog\Controller\Sellerhtml\Ajaxsearch;

use Magedelight\Backend\App\Action\Context;
use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Search\Model\QueryFactory;

class Suggest extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    public $vendorHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_productStatus;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Maximum results to display
     *
     * @var int
     */
    const MAX_RESULT_DISPLAY = 5;

    /**
     * Autocomplete
     *
     * @var  \Magedelight\Catalog\Model\Autocomplete\Vendor\SearchDataProvider
     */
    protected $autocomplete;

    /**
     * Query factory
     *
     * @var QueryFactory
     */
    protected $_queryFactory;

    /**
     * Search helper
     *
     * @var SearchHelper
     */
    protected $_searchHelper;

    /**
     * @type \Magento\CatalogSearch\Helper\Data
     */
    protected $_helper;

    /**
     * @var FlatState
     */
    protected $flatState;

    /**
     * Suggest constructor.
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param \Magedelight\Catalog\Model\Autocomplete\Vendor\SearchDataProvider $autocomplete
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param SearchHelper $searchHelper
     * @param FlatState $flatState
     * @param \Magento\CatalogSearch\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        \Magedelight\Catalog\Model\Autocomplete\Vendor\SearchDataProvider $autocomplete,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        SearchHelper $searchHelper,
        FlatState $flatState,
        \Magento\CatalogSearch\Helper\Data $helper
    ) {
        $this->flatState = $flatState;
        $this->autocomplete = $autocomplete;
        $this->_url = $context->getUrl();
        $this->_queryFactory = $queryFactory;
        $this->_searchHelper = $searchHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productStatus = $productStatus;
        $this->_vendorProductFactory = $vendorProductFactory;
        $this->_productVisibility = $productVisibility;
        $this->vendorHelper = $context->getHelper();
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * Render results
     *
     * @return Json
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->_view->loadLayout();

        if (!$this->getRequest()->getParam('q', false)) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());
            return $resultRedirect;
        }

        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();

        $autocompleteItems = $this->autocomplete->setVendor($this->_auth->getUser())->getItems();

        if ($query->getQueryText() != '') {
            if ($this->_helper->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
                $query->saveNumResults(sizeof($autocompleteItems));
            }
        }

        $responseData = $this->_formatData($autocompleteItems, $query);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }

    /**
     * @return string
     */
    public function getQueryParamName()
    {
        return QueryFactory::QUERY_VAR_NAME;
    }
    protected function _formatData($collection, $query)
    {
        return [
            'results' => $this->_limitResponseData($collection),
            'info' => [
                'size' => $this->getSellingProductCollectionCount(),
                'url'  => $this->_url->getUrl(
                    'rbcatalog/product/index',
                    [
                        '_query' => [QueryFactory::QUERY_VAR_NAME => $query->getQueryText()],
                        '_secure' => $this->_request->isSecure()
                    ]
                )
            ],
        ];
    }

    /**
     * Limit response elements
     *
     * @param array $responseData Response Data
     * @return array
     */
    protected function _limitResponseData($responseData)
    {
        return array_slice($responseData, 0, self::MAX_RESULT_DISPLAY);
    }

    /**
     * @return int
     */
    public function getSellingProductCollectionCount()
    {
        $vendorId = $this->_auth->getUser()->getVendorId();
        $search = trim($this->getRequest()->getParam('q'));
        $vendorCollection = $this->_vendorProductFactory->create()->getCollection()
            ->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('type_id', ['neq' => 'configurable']);
        $excludeIds[] = $vendorCollection->getColumnValues('marketplace_product_id');

        $collection = $this->_productCollectionFactory->create();
        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToSelect('name');
            $collection->addFieldToSelect('model_number');
            $collection->addFieldToSelect('small_image');
            $collection->addFieldToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
            $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
            if ($excludeIds[0] != null) {
                $collection->addIdFilter($excludeIds, true);
            }
            $collection->addFieldToFilter([['attribute' => 'name', 'like' => '%' . $search . '%'],
                                                ['attribute' => 'sku', 'like' => trim($search)]]);
        } else {
            $collection->addAttributeToSelect('name');
            $collection->addAttributeToSelect('model_number');
            $collection->addAttributeToSelect('small_image');
            $collection->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
            $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
            if ($excludeIds[0] != null) {
                $collection->addIdFilter($excludeIds, true);
            }
            $collection->addAttributeToFilter([['attribute' => 'name', 'like' => '%' . $search . '%'],
                                                    ['attribute' => 'sku', 'like' => trim($search)]]);
        }
        return $collection->count();
    }
}
