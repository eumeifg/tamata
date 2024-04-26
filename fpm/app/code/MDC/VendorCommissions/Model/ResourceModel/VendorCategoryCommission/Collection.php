<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_VendorCommissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\VendorCommissions\Model\ResourceModel\VendorCategoryCommission;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
     /**
      * @var string
      */
    protected $_idFieldName = 'vendor_category_commission_id';
    
    protected function _construct()
    {
        $this->_init(
            \MDC\VendorCommissions\Model\VendorCategoryCommission::class,
            \MDC\VendorCommissions\Model\ResourceModel\VendorCategoryCommission::class
        );
    }
}
