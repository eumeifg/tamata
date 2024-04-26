<?php

namespace CAT\VIP\Model;

use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;

class ConfigurableAttributeData extends \Magedelight\Catalog\Model\ConfigurableAttributeData

{

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_helper;
    protected $vip_helper;

    /**
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Catalog\Helper\Data $helper,
        \CAT\VIP\Helper\Data $viphelper
    ) {
        $this->_helper = $helper;
        $this->vip_helper = $viphelper;
    }

    /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getAttributeOptionsData($attribute, $config)
    {
        $attributeOptionsData = [];
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption['value_index'];
            
            $childId = isset($config[$attribute->getAttributeId()][$optionId][0])
                    ? $config[$attribute->getAttributeId()][$optionId][0]
                    : null;
           
            $attributeOptionsData[] = [
                'id' => $optionId,
                'label' => $attributeOption['label'],
                'vendorid' => $this->_helper->getDefaultVendorId($childId),
                'vipdiscount' => $this->vip_helper->getvipProductDiscountPrice($childId,$this->_helper->getDefaultVendorId($this->_helper->getDefaultVendorId($childId),$childId),$this->_helper->getDefaultVendorId($childId)),
                'vendorname' => $this->_helper->getDefaultVendorName($childId),
                'products' => isset($config[$attribute->getAttributeId()][$optionId])
                    ? $config[$attribute->getAttributeId()][$optionId]
                    : [],
            ];
        }
        return $attributeOptionsData;
    }
}

