<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_Commissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\Commissions\Model\ResourceModel\VendorGroupCommission;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
     /**
      * @var string
      */
    protected $_idFieldName = 'vendor_group_commission_id';
    
    protected function _construct()
    {
        $this->_init(
            \MDC\Commissions\Model\VendorGroupCommission::class,
            \MDC\Commissions\Model\ResourceModel\VendorGroupCommission::class
        );
    }
}
