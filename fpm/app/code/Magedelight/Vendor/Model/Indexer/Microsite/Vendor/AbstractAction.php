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
namespace Magedelight\Vendor\Model\Indexer\Microsite\Vendor;

use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlRewrite;

/**
 * Abstract action reindex class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractAction
{

    /**
     * Entity type code
     */
    const ENTITY_TYPE = 'microsite';

    /**
     * @var Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $_urlRewrite;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Message manager
     *
     */
    protected $_messageManager;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Microsite\CollectionFactory
     */
    protected $_micrositeCollectionFactory;

    /**
     * @param UrlRewrite $urlRewrite
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magedelight\Vendor\Model\ResourceModel\Microsite\CollectionFactory $micrositeCollectionFactory
     */
    public function __construct(
        UrlRewrite $urlRewrite,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magedelight\Vendor\Model\ResourceModel\Microsite\CollectionFactory $micrositeCollectionFactory
    ) {
        $this->_urlRewrite = $urlRewrite;
        $this->storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_micrositeCollectionFactory = $micrositeCollectionFactory;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     *
     * @return void
     */
    abstract public function execute($ids);

    /**
     * Reindex all
     *
     * @return void
     */
    public function reindexAll()
    {
        $this->_clearUrlRewrite();
        $this->_addUrlRewrite();
        $this->_addUrlRewriteForProducts();
    }

    /**
     * Refresh entities index
     *
     * @param array $micrositeIds
     * @return array Affected ids
     */
    protected function _reindexRows($micrositeIds = [])
    {
        $this->_clearUrlRewriteRows($micrositeIds);
        $this->_addUrlRewriteRows($micrositeIds);
        $this->_addUrlRewriteRowsForProduct($micrositeIds);
    }

    public function _clearUrlRewriteRows($micrositeIds)
    {
        foreach ($this->storeManager->getStores() as $store) {
            $urlCollection = $this->_urlRewrite->getCollection();
            $urlCollection->addFieldToFilter('store_id', $store->getStoreId());
            $urlCollection->addFieldToFilter('entity_type', self::ENTITY_TYPE);
            $urlCollection->addFieldToFilter('entity_id', ['in' => $micrositeIds]);
            $urlCollection->walk('delete');
        }
    }

    public function _addUrlRewriteRows($micrositeIds)
    {
        $micrositeCollection = $this->_micrositeCollectionFactory->create();
        $micrositeCollection->addFieldToFilter('microsite_id', ['in' => $micrositeIds]);
        $data = [];
        if (empty($micrositeCollection->getData())) {
            return;
        }
        $model = $this->_urlRewrite;
        foreach ($micrositeCollection->getData() as $microsite) {
            $data['entity_type'] = self::ENTITY_TYPE;
            $data['entity_id'] = $microsite['microsite_id'];
            $data['request_path'] = $microsite['url_key'];
            $data['target_path'] = 'rbvendor/microsite_vendor/index/vid/' . $microsite['vendor_id'];
            $data['redirect_type'] = 0;
            $data['store_id'] = $microsite['store_id'];
            $model->setData($data);
            try {
                $model->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
    }

    public function _addUrlRewriteRowsForProduct($micrositeIds)
    {
        $micrositeCollection = $this->_micrositeCollectionFactory->create();
        $micrositeCollection->addFieldToFilter('microsite_id', ['in' => $micrositeIds]);
        $data = [];
        if (empty($micrositeCollection->getData())) {
            return;
        }
        $model = $this->_urlRewrite;
        foreach ($micrositeCollection->getData() as $microsite) {
            $data['entity_type'] = self::ENTITY_TYPE;
            $data['entity_id'] = $microsite['microsite_id'];
            $data['request_path'] = $microsite['url_key'] . '/product';
            $data['target_path'] = 'rbvendor/microsite_vendor/product/vid/' . $microsite['vendor_id'];
            $data['redirect_type'] = 0;
            $data['store_id'] = $microsite['store_id'];
            $model->setData($data);
            try {
                $model->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
    }

    public function _clearUrlRewrite()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $urlCollection = $this->_urlRewrite->getCollection();
            $urlCollection->addFieldToFilter('store_id', $store->getStoreId());
            $urlCollection->addFieldToFilter('entity_type', self::ENTITY_TYPE);
            $urlCollection->walk('delete');
        }
    }

    public function _addUrlRewrite()
    {
        $micrositeCollection = $this->_micrositeCollectionFactory->create();
        $data = [];
        if (empty($micrositeCollection->getData())) {
            return;
        }
        $model = $this->_urlRewrite;
        foreach ($micrositeCollection->getData() as $microsite) {
            $data['entity_type'] = self::ENTITY_TYPE;
            $data['entity_id'] = $microsite['microsite_id'];
            $data['request_path'] = $microsite['url_key'];
            $data['target_path'] = 'rbvendor/microsite_vendor/index/vid/' . $microsite['vendor_id'];
            $data['redirect_type'] = 0;
            $data['store_id'] = $microsite['store_id'];
            $model->setData($data);
            try {
                $model->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
    }

    public function _addUrlRewriteForProducts()
    {
        $micrositeCollection = $this->_micrositeCollectionFactory->create();
        $data = [];
        if (empty($micrositeCollection->getData())) {
            return;
        }
        $model = $this->_urlRewrite;
        foreach ($micrositeCollection->getData() as $microsite) {
            $data['entity_type'] = self::ENTITY_TYPE;
            $data['entity_id'] = $microsite['microsite_id'];
            $data['request_path'] = $microsite['url_key'] . '/product';
            $data['target_path'] = 'rbvendor/microsite_vendor/product/vid/' . $microsite['vendor_id'];
            $data['redirect_type'] = 0;
            $data['store_id'] = $microsite['store_id'];
            $model->setData($data);
            try {
                $model->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
    }
}
