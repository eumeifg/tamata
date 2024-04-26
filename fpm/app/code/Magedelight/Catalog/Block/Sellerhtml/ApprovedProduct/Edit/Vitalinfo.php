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
namespace Magedelight\Catalog\Block\Sellerhtml\ApprovedProduct\Edit;

class Vitalinfo extends \Magedelight\Catalog\Block\Sellerhtml\ApprovedProduct\Edit
{
    /**
     * @return string
     * @throws \Exception
     */
    public function getAttributesHtml()
    {
        $this->setHtmlIdPrefix('vital-');
        $this->setFieldNamePrefix('vital[');
        $this->setFieldNameSuffix(']');

        if ($this->getVitalAttributes()) {
            return $this->getHtml($this->getVitalAttributes());
        }
        return '';
    }

    /**
     * @return \Magedelight\Catalog\Model\ProductRequest|mixed|null
     */
    public function getCurrentRequest()
    {
        if (!$this->vendorProduct) {
            $this->vendorProduct = $this->coreRegistry->registry('vendor_current_product_core');
            if ($this->vendorProduct) {
                $attributes =  $this->getVitalAttributes();
                foreach ($attributes as $attribute) {
                    $this->vendorProduct->setData(
                        $attribute->getAttributeCode(),
                        $this->vendorProduct->getData($attribute->getAttributeCode())
                    );
                }
            } else {
                return null;
            }
        }
        return $this->vendorProduct;
    }
}
