<?php

namespace Ktpl\ExtendedAdminSalesGrid\Ui\Component\Listing\Column;

class OrderVendorName implements \Magento\Framework\Option\ArrayInterface
{

    public $vendorModel;
    protected $storeManager;
    public $allvendor;

    public function __construct(
        \Magedelight\Vendor\Model\Vendor $vendorModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Commissions\Model\Source\VendorBusiness $allvendor
    ) {
        $this->vendorModel = $vendorModel;
        $this->storeManager = $storeManager;
        $this->allvendor = $allvendor;
    }

    public function toOptionArray()
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $allVendors = $this->allvendor->toOptionArray($websiteId);
        $arr = [];
        foreach ($allVendors as $key => $value) {
            $arr[$key]['value'] = $value['value'];
            $arr[$key]['label'] = $value['label']->getText();
        }
        return $arr;
    }
}
