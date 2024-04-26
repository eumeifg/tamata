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
namespace Magedelight\Vendor\Block\Sellerhtml\Review\Customer\Review;

class Summary extends \Magedelight\Vendor\Block\Sellerhtml\Review\Customer\Review\AbstractRatings
{
    /**
     *
     * @return mixed
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAvarageSummary()
    {
        $select = $this->getCustomerReviews()->getSelect();
        $collection = $this->_reviewCollectionFactory->create()->getAvarageSummaryForVendor($select);
        return $collection['vendor_rating_avg'];
    }

    /**
     *
     * @return int
     */
    /*public function getAvgRating() {
        $vendorId = $this->getVendorLoggedInData()->getVendorId();
       $collection = $this->_vendorRating->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)->addFieldToFilter('is_shared', 1);
        //$collection->addFieldToFilter('is_shared', ['eq' => 1 ]);
        $collection->getSelect()->joinLeft(
            ['rvrt' => 'md_vendor_rating_rating_type'],
            "main_table.vendor_rating_id = rvrt.vendor_rating_id",
            //['ROUND(SUM(`rvrt`.`rating_avg`)/(SELECT  count(*) FROM md_vendor_rating
                where md_vendor_rating.vendor_id = main_table.vendor_id)) as rating_avg']
              ["ROUND(SUM(`rvrt`.`rating_avg`)/(SELECT  count(*) FROM md_vendor_rating
                where md_vendor_rating.vendor_id = {$vendorId})) as rating_avg"]
        );
        return $collection->getFirstItem()->getRatingAvg();
    }*/
}
