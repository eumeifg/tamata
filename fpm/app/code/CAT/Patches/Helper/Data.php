<?php

namespace CAT\Patches\Helper;

use Magedelight\Vendor\Model\Config\Fields;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magedelight\Vendor\Helper\Data
{
    public function getVendorNameById($vendorId = null)
    {
        $vendor = $this->getVendorDetails($vendorId, ['vendor_id'], ['business_name']);
        if($vendor != null && !empty($vendor->getBusinessName())){
            return $vendor->getBusinessName();
        }
    }
}
