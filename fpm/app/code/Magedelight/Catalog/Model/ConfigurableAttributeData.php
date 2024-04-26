<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

class ConfigurableAttributeData extends \Magento\ConfigurableProduct\Model\ConfigurableAttributeData
{

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_helper;

    /**
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->_helper = $helper;
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
                'vendorname' => $this->_helper->getDefaultVendorName($childId),
                'products' => isset($config[$attribute->getAttributeId()][$optionId])
                    ? $config[$attribute->getAttributeId()][$optionId]
                    : [],
            ];
        }
        
        return $attributeOptionsData;
    }
}
