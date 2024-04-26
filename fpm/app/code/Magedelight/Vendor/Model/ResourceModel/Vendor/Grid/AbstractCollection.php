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
namespace Magedelight\Vendor\Model\ResourceModel\Vendor\Grid;

/**
 * Description of AbstractCollection
 *
 * @author Rocket Bazaar Core Team
 */

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

abstract class AbstractCollection extends SearchResult
{
    
    protected $_vendorWebsites = null;
    
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $resourceModel
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFilterToMap('vendor_id', "main_table.vendor_id");
        return $this;
    }
    
    public function _addWebsiteData($columns = ['*'], $websiteId = null)
    {
        $websiteFilter = '';
        if ($websiteId) {
            $websiteFilter = ' and rvwd.website_id = ('.$websiteId.')';
        }
        $this->getSelect()->join(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = main_table.vendor_id'.$websiteFilter,
            $columns
        );
        $this->getSelect()->group('rvwd.vendor_id');
        $this->addFilterToMap('website_id', "rvwd.website_id");
        return $this;
    }
    
    protected function _getCurrentWebsiteId()
    {
        return $this->getConnection()->select()->from(
            ['website' => 'store_website'],
            [
                    'website_id'
                ]
        )->where('website.is_default = 1');
    }
    
    /**
     *
     * @param type $collection
     */
    protected function _addWebsiteIds($status = null)
    {
        return $this->getSelect()->columns(['websites' => $this->getVendorWebsites($status)]);
    }
    
    /**
     *
     * @return type
     */
    protected function getVendorWebsites($status = null)
    {
        if (!$this->_vendorWebsites) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(
                ['rvwd' => 'md_vendor_website_data'],
                [
                      'GROUP_CONCAT(website_id)'
                ]
            );

            $select->where('`rvwd`.`vendor_id` = `main_table`.`vendor_id`');
            if (isset($status)) {
                $select->where('`rvwd`.`status` = '.$status);
            }
            $this->_vendorWebsites = $select;
        }
        return $this->_vendorWebsites;
    }
}
