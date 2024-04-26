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
namespace Magedelight\Vendor\Model\ResourceModel\Microsite;

/**
 * Description of Collection
 *
 * @author Rocket Bazaar Core Team
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null         $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * @var string
     */
    protected $_idFieldName = 'microsite_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Vendor\Model\Microsite::class, \Magedelight\Vendor\Model\ResourceModel\Microsite::class);
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|void
     */
    protected function _afterLoad()
    {
        $adapter = $this->getConnection();
        foreach ($this->_items as $item) {
            $vendorId = $item['vendor_id'];
            /*$select = $adapter->select()->from(
                'md_vendor',
                ['rvwd.business_name']
            )->joinLeft(
                ['rvwd'=>'md_vendor_website_data'],
                'rvwd.vendor_id = md_vendor.vendor_id and rvwd.website_id = '
                    . $this->storeManager->getStore()->getWebsiteId()
            )->where(
                'md_vendor.vendor_id = ?',$vendorId
            );
            $adapter->addFilterToMap('store_id', 'main_table.store_id'); */
            $select = $adapter->select()->from(
                'md_vendor',
                ['rvwd.business_name']
            )->joinLeft(['rvwd'=>'md_vendor_website_data'], 'rvwd.vendor_id = md_vendor.vendor_id')->where(
                'md_vendor.vendor_id = ?',
                $vendorId
            );

            $bussinessName = $adapter->fetchCol($select);
            if ($bussinessName) {
                $item['business_name'] = $bussinessName[0];
            }
        }
    }
}
