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
namespace MDC\VendorCommissions\Model;

class VendorCategoryCommission extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\MDC\VendorCommissions\Model\ResourceModel\VendorCategoryCommission::class);
    }
}
