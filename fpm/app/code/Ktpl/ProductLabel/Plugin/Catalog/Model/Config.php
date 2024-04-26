<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Plugin\Catalog\Model;

class Config
{
    private $usedInProductListing;

    private $eavConfig;

    private $attributeFactory;

    private $productLabelCollectionFactory;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory $productLabelCollectionFactory
    ) {
        $this->eavConfig                     = $eavConfig;
        $this->attributeFactory              = $attributeFactory;
        $this->productLabelCollectionFactory = $productLabelCollectionFactory;
    }

    public function afterGetAttributesUsedInProductListing(\Magento\Catalog\Model\Config $subject, $result)
    {
        if ($this->usedInProductListing === null) {
            $this->usedInProductListing = $result;
            $entityType                 = \Magento\Catalog\Model\Product::ENTITY;

            $productLabelsCollection = $this->productLabelCollectionFactory->create();
            // Here you have all the attribute ids that are used to build product label rules.
            $attributeIds = $productLabelsCollection->getAllAttributeIds();

            // Filter the collection on these attributes only.
            $attributesDataExtra = $this->attributeFactory->getCollection()
                ->addFieldToFilter('attribute_id', ['in' => $attributeIds])->getData();

            $this->eavConfig->importAttributesData($entityType, $attributesDataExtra);

            foreach ($attributesDataExtra as $attributeData) {
                $attributeCode                              = $attributeData['attribute_code'];
                $this->usedInProductListing[$attributeCode] = $this->eavConfig->getAttribute(
                    $entityType,
                    $attributeCode
                );
            }
        }

        return $this->usedInProductListing;
    }
}
