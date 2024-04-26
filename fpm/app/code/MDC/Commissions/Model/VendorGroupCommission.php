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
namespace MDC\Commissions\Model;

class VendorGroupCommission extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\MDC\Commissions\Model\ResourceModel\VendorGroupCommission::class);
    }
}
