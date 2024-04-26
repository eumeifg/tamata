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
namespace MDC\Catalog\Plugin;

class VendorProductAttributes
{
    /**
     * @var \MDC\Catalog\Api\Data\ProductAttributesInterfaceFactory
     */
    protected $productAttributesInterfaceFactory;

    /**
     * @var \MDC\Catalog\Helper\Listing\Data
     */
    protected $listingHelper;

    /**
     * VendorProductAttributes constructor.
     * @param \MDC\Catalog\Api\Data\ProductAttributesInterfaceFactory $productAttributesInterfaceFactory
     * @param \MDC\Catalog\Helper\Listing\Data $listingHelper
     */
    public function __construct(
        \MDC\Catalog\Api\Data\ProductAttributesInterfaceFactory $productAttributesInterfaceFactory,
        \MDC\Catalog\Helper\Listing\Data $listingHelper
    ) {
        $this->productAttributesInterfaceFactory = $productAttributesInterfaceFactory;
        $this->listingHelper = $listingHelper;
    }

    /**
     * Get Items
     *
     * @return \Magedelight\Catalog\Api\Data\VendorProductInterface[]
     */
    public function afterGetListingProducts
    (
        \Magedelight\Catalog\Api\VendorProductRepositoryInterface $subject,
        $result,
        $type,
        $storeId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        $searchterm = null,
        $outOfStockFilter = false
    ) {
        foreach ($result->getItems() as $entity){
            $extensionAttributes = $entity->getExtensionAttributes();
            $attributes = [];
            foreach ($this->listingHelper->getAttributesList() as $attributeCode)
            {
                $value = $entity->getProduct()->getResource()->getAttribute($attributeCode)->getFrontend()->getValue($entity);
                if($value){
                    $attrData = $this->productAttributesInterfaceFactory->create();
                    $attrData->setAttributeLabel(
                        $entity->getProduct()->getResource()->getAttribute($attributeCode)->getFrontend()->getLabel($entity)
                    );
                    $attrData->setAttributeValue($value);
                    $attributes[] = $attrData;
                }
            }
            $extensionAttributes->setCustomProductAttributes($attributes);
            $entity->setExtensionAttributes($extensionAttributes);
        }
        return $result;
    }
}
