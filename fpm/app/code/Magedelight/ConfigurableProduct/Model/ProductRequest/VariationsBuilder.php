<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Model\ProductRequest;

use Magedelight\ConfigurableProduct\Api\Data\ConfigurableAttributeDataInterfaceFactory;
use Magedelight\ConfigurableProduct\Api\Data\ConfigurableOptionDataInterfaceFactory;

class VariationsBuilder
{

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var ConfigurableOptionDataInterfaceFactory
     */
    protected $configurableOptionData;

    /**
     * @var ConfigurableAttributeDataInterfaceFactory
     */
    protected $configurableAttributeData;

    /**
     * VariationsBuilder constructor.
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $collectionFactory
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param ConfigurableOptionDataInterfaceFactory $configurableOptionData
     * @param ConfigurableAttributeDataInterfaceFactory $configurableAttributeData
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $collectionFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        ConfigurableOptionDataInterfaceFactory $configurableOptionData,
        ConfigurableAttributeDataInterfaceFactory $configurableAttributeData
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->serializer = $serializer;
        $this->configurableOptionData = $configurableOptionData;
        $this->configurableAttributeData = $configurableAttributeData;
    }

    public function create($item, $websiteId = 1, $storeId = 1)
    {
        $variantAttributes = [];
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->join(
            ['mvprw' => 'md_vendor_product_request_website'],
            'mvprw.product_request_id = main_table.product_request_id AND mvprw.website_id = ' . $websiteId,
            ['mvprw.product_request_id']
        );
        $collection->getSelect()->join(
            ['mvprs' => 'md_vendor_product_request_store'],
            'mvprs.product_request_id = main_table.product_request_id AND mvprs.store_id = ' . $storeId,
            ['attributes']
        );
        $collection->getSelect()->join(
            ['mvprsl' => 'md_vendor_product_request_super_link'],
            'mvprsl.product_request_id = main_table.product_request_id AND mvprsl.parent_id = '
            . $item->getProductRequestId(),
            ['parent_id']
        );
        $variantData = [];
        if ($collection && $collection->getSize() > 0) {
            foreach ($collection as $variant) {
                $options = $this->serializer->unserialize($variant->getAttributes());
                $variantColumns = $this->getUsedProductAttributeIds($item);

                if (is_array($variantColumns)) {
                    foreach ($variantColumns as $attributeId => $attributeCode) {
                        if (array_key_exists($attributeCode, $options)) {
                            $optionsData = $this->configurableOptionData->create();
                            $optionsData->setId($options[$attributeCode])
                                ->setLabel($this->renderField($attributeCode, $options[$attributeCode]));
                            $variantData[$attributeId][] = $optionsData;
                        }
                    }
                }
            }
        }

        foreach ($variantData as $attributeId => $options) {
            $configurableAttrData = $this->configurableAttributeData->create();

            $configurableAttrData->setId($attributeId)
                ->setOptions($options);
            $variantAttributes [] = $configurableAttrData;
        }
        return $variantAttributes;
    }

    /**
     *
     * @param string $field
     * @param string|array $value
     * @return string
     */
    public function renderField($field, $value)
    {
        $attribute = $this->attributeRepository->get('catalog_product', $field);
        if (is_array($value)) {
            $value = $value[0];
        }
        $value = $attribute->getSource()->getOptionText($value);
        return $value;
    }

    /**
     * @param type $item
     * @return string
     */
    public function getUsedProductAttributeIds($item)
    {
        $usedAttributeIds = $item->getUsedProductAttributeIds();
        if ($usedAttributeIds) {
            return $this->serializer->unserialize($usedAttributeIds);
        }
        return '';
    }

    /**
     * @param string $attribute
     * @return array
     */
    protected function getAttributeOptionsData($attribute)
    {
        $swatchData = [];
        $attribute = $this->attributeRepository->get('catalog_product', $attribute);
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption->getValue();
            $swatchDataArray = $this->swatchHelper->getSwatchesByOptionsId([$optionId]);

            if (!empty($swatchDataArray)) {
                $swatchData[$attribute->getAttributeId()][$optionId] = $swatchDataArray[$optionId]['value'];
            }
        }
        return $swatchData;
    }
}
