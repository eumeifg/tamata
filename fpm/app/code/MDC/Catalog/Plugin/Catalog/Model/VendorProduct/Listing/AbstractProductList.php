<?php

namespace MDC\Catalog\Plugin\Catalog\Model\VendorProduct\Listing;

use Magento\Eav\Api\AttributeRepositoryInterface;

/**
 * Class AbstractProductList
 * @package MDC\Catalog\Plugin\Catalog\Model\VendorProduct\Listing
 */
class AbstractProductList
{
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * AbstractProductList constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param \Magedelight\Catalog\Model\VendorProduct\Listing\AbstractProductList $subject
     * @param $result
     * @param $collection
     * @param $vendorId
     * @param $storeId
     * @param int $status
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterJoinExtraTablesForApprovedProducts(
        \Magedelight\Catalog\Model\VendorProduct\Listing\AbstractProductList $subject,
        $result, $collection, $vendorId, $storeId, $status = 0
    ) {
        $result->getSelect()->joinLeft(
            ['cpev_barcode' => 'catalog_product_entity_varchar'],
            'cpev_barcode.row_id = e.row_id AND cpev_barcode.attribute_id=' . $this->getAttributeIdOfBarcode(),
            ['barcode' => 'value']
        );
        $result->getSelect()->joinLeft(
            ['cpev_item_number' => 'catalog_product_entity_varchar'],
            'cpev_item_number.row_id = e.row_id AND cpev_item_number.attribute_id=' . $this->getAttributeIdOfItemNumber(),
            ['item_number' => 'value']
        );
        return $result;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAttributeIdOfBarcode() {
        return $this->attributeRepository->get('catalog_product', 'bar_code')->getId();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAttributeIdOfItemNumber() {
        return $this->attributeRepository->get('catalog_product', 'item_number')->getId();
    }
}