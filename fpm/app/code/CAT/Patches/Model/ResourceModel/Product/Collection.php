<?php

namespace CAT\Patches\Model\ResourceModel\Product;

use Magedelight\Catalog\Model\Config\Source\DefaultVendor\Criteria;
use Magedelight\Catalog\Model\Product;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;

class Collection extends \Magedelight\Catalog\Model\ResourceModel\Product\Collection
{

    protected function getRatingAvg()
    {
        if (!$this->ratingAvg) {
            $connection = $this->_resource->getConnection();
            $select = $connection->select()->from(
                ['rvrt' => $this->_resource->getTable('md_vendor_rating_rating_type')],
                [
                    '(SUM(`rvrt`.`rating_avg`)/(SELECT count(*) FROM '
                    . $this->_resource->getTable('md_vendor_rating') . ' where main_table.vendor_id = '
                    . $this->_resource->getTable('md_vendor_rating') . '.vendor_id AND '
                    . $this->_resource->getTable('md_vendor_rating') . '.is_shared = 1)  / 20)',
                ]
            );
            $select->joinLeft(
                ['rvr' => $this->_resource->getTable('md_vendor_rating')],
                '`rvr`.`vendor_rating_id` = `rvrt`.`vendor_rating_id`',
                []
            );

            $select->where('`rvr`.`vendor_id` = `main_table`.`vendor_id` AND `rvr`.`is_shared` = 1');
            $this->ratingAvg = $select;
        }
        return $this->ratingAvg;
    }

    /**
     * Prepare statistics data
     *
     * @return \Magedelight\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _prepareStatisticsData($marketplaceProductId = null, $vendorId = null, $isSpecialPrice = false)
    {
        $currentWebsiteId = ($this->storeManager->getStore()->getWebsiteId()) ?: 0;
        /**
         * Changes RH : Speed Test
         */
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $mdProductFactory = $objectManager->create(\Magedelight\Catalog\Model\Product::class);
        // $mdProduct = $mdProductFactory->getCollection();
        // if ($marketplaceProductId) {
        //     $mdProduct->addFieldToFilter('marketplace_product_id', ['eq' => $marketplaceProductId]);
        // }
        // if ($vendorId) {
        //     $mdProduct->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
        // }
        // if ($isSpecialPrice) {
        //     $now = date('Y-m-d H:i:s');
        //     $mdProduct->addFieldToFilter('special_from_date', ['gteq' => strtotime($now)])->addFieldToFilter('special_to_date', ['lteq' => strtotime($now)]);

        // }
        // $mdProduct->getSelect()->columns(['qty_sum' => new \Zend_Db_Expr('SUM(qty)')])
        //     ->columns(['min_price' => new \Zend_Db_Expr('MIN(rbvpw.price)')])
        //     ->columns(['max_price' => new \Zend_Db_Expr('MAX(rbvpw.price)')])
        //     ->columns(['min_special_price' => new \Zend_Db_Expr('MIN(rbvpw.special_price)')])
        //     ->group('marketplace_product_id');
        // $row = [];
        // foreach ($mdProduct as $key => $value) {
        //     $row[] = $value['vendor_product_id'];
        //     $row[] = $value['marketplace_product_id'];
        //     $row[] = $value['qty_sum'];
        //     $row[] = $value['min_price'];
        //     $row[] = $value['max_price'];
        //     $row[] = $value['min_special_price'];
        //     $row[] = $value['status'];

        // }
        /**
         * Changes RH : Speed Test
         */

        $connection = $this->_resource->getConnection();
        $mainTableName = $this->_resource->getTable('md_vendor_product');
        $websiteTableName = $this->_resource->getTable('md_vendor_product_website');
        $select = $connection->select()
            ->from(
                ['rvp' => $mainTableName],
                [
                    'vendor_product_id',
                    'marketplace_product_id',
                    'SUM(qty)',
                    'MIN(rbvpw.price)',
                    'MAX(rbvpw.price)',
                    'MIN(rbvpw.special_price)',
                ]
            )->joinLeft(
                ['rbvpw' => $websiteTableName],
                '(rvp.vendor_product_id = rbvpw.vendor_product_id and rbvpw.status = "' . Product::STATUS_LISTED . '")',
                ['status']
            );
            /*$select->where(
                'rbvpw.website_id = (SELECT website_id from '
                . $websiteTableName . ' where marketplace_product_id = '
                . $marketplaceProductId . ' group by marketplace_product_id)'
            );*/
        if ($marketplaceProductId) {
            $select->where('rvp.marketplace_product_id = ?', $marketplaceProductId);
        }

        if ($vendorId) {
            $select->where('rvp.vendor_id = ?', $vendorId);
        }

        if ($isSpecialPrice) {
            $select->where('(CURDATE() between rbvpw.special_from_date AND rbvpw.special_to_date)');
        }

        $select->where('rbvpw.website_id = ?', $currentWebsiteId);

        $row = $this->getConnection()->fetchRow($select, $this->_bindParams, \Zend_Db::FETCH_NUM);

        $this->maxPrice = (double) $row[4]; /*set product maximum price from all offers*/
        $this->minPrice = (double) $row[3]; /*set product minimum price from all offers*/

        if ($row[5] == '' || $row[5] == null) {
            $this->minSpecialPrice = (double) $row[4]; /*set product special price */
        } else {
            $this->minSpecialPrice = (double) $row[5]; /*set product special price */
        }

        return $this;
    }
}
