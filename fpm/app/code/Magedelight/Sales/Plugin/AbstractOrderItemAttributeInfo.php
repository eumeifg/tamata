<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Plugin;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

abstract class AbstractOrderItemAttributeInfo
{
    /**
     * @param $result
     * @return mixed
     */
    protected function setAttributesInfo($result)
    {
        foreach ($result as $item) {
            $productOptions = $item->getProductOptions();
            if ($item->getProductType() == Configurable::TYPE_CODE &&
                is_array($productOptions) && array_key_exists('attributes_info', $productOptions)) {
                $extensionAttributes = $item->getExtensionAttributes();
                $extensionAttributes->setAttributesInfo($productOptions['attributes_info']);
                $item->setExtensionAttributes($extensionAttributes);
            }
        }
        return $result;
    }
}
