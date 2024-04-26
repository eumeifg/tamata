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
namespace Magedelight\Vendor\Helper\Dashboard;

use Magento\Framework\App\ObjectManager;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\Collection
     */
    protected $_orderCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Helper collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|array
     */
    protected $_collection;

    /**
     * Parameters for helper
     *
     * @var array
     */
    protected $_params = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magedelight\Sales\Model\ResourceModel\Reports\Order\Collection $orderCollection
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magedelight\Sales\Model\ResourceModel\Reports\Order\Collection $orderCollection,
        \Magedelight\Backend\Model\Auth\Session $authSession
    ) {
        $this->_orderCollection = $orderCollection;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    protected function _initCollection()
    {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');

        $period = $this->getParam('period') ?: '2y';
        $this->_collection = $this->_orderCollection->prepareSummary($period, 0, 0, $isFilter);

        if ($this->getParam('store')) {
            $this->_collection->addFieldToFilter('store_id', $this->getParam('store'));
        } elseif ($this->getParam('website')) {
            $storeIds = $this->getStoreManager()->getWebsite($this->getParam('website'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', ['in' => implode(',', $storeIds)]);
        } elseif ($this->getParam('group')) {
            $storeIds = $this->getStoreManager()->getGroup($this->getParam('group'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', ['in' => implode(',', $storeIds)]);
        } elseif (!$this->_collection->isLive()) {
            $this->_collection->addFieldToFilter(
                'store_id',
                ['eq' => $this->getStoreManager()->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId()]
            );
        }
        $this->_collection->load();
    }

    /**
     * Get Store Manager
     *
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        if (!$this->_storeManager) {
            $this->_storeManager = ObjectManager::getInstance()->get(\Magento\Store\Model\StoreManager::class);
        }

        return $this->_storeManager;
    }

    /**
     * @return array|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        if ($this->_collection === null) {
            $this->_initCollection();
        }
        return $this->_collection;
    }
    /**
     * Returns collection items
     *
     * @return array
     */
    public function getItems()
    {
        return is_array($this->getCollection()) ? $this->getCollection() : $this->getCollection()->getItems();
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return sizeof($this->getItems());
    }

    /**
     * @param string $index
     * @return array
     */
    public function getColumn($index)
    {
        $result = [];
        foreach ($this->getItems() as $item) {
            if (is_array($item)) {
                if (isset($item[$index])) {
                    $result[] = $item[$index];
                } else {
                    $result[] = null;
                }
            } elseif ($item instanceof \Magento\Framework\DataObject) {
                $result[] = $item->getData($index);
            } else {
                $result[] = null;
            }
        }
        return $result;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     * @param array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
}
