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
namespace Magedelight\Catalog\Model\Product\Request\Type\Configurable;

class Update extends \Magedelight\Catalog\Model\Product\Request\Type\Configurable
{

    /**
     *
     * @param integer $vendorId
     * @param array $requestData
     * @param string $productType
     * @throws \Exception
     */
    public function execute($vendorId, $requestData, $productType = 'simple')
    {
        $this->updateProductRequest($vendorId, $requestData, $productType);
        $productRequestId = $requestData['offer']['product_request_id'];
        $this->deleteExistingVariants($productRequestId);
        if (array_key_exists('has_variants', $requestData) && $requestData['has_variants'] == 1) {
            foreach ($requestData['variants_data'] as $variantData) {
                $variantAttributes = array_values($this->jsonDecoder->decode($requestData['usedAttributeIds']));
                $variantRequestData = $requestData;
                foreach ($variantData as $field => $value) {
                    $variantRequestData['offer'][$field] = $value;
                }
                $this->createRequestNewAssociateProduct(
                    $vendorId,
                    $variantRequestData,
                    $productRequestId,
                    $variantData
                );
            }
            /* Sell existing will not have has_variants flag. */
        }
    }

    /**
     *
     * @param integer $productRequestId
     */
    protected function deleteExistingVariants($productRequestId)
    {
        /* Delete child products if exists. */
        $collection = $this->productRequestFactory->create()->getCollection();
        $collection->getSelect()->join(
            ['mvprsl' => 'md_vendor_product_request_super_link'],
            'mvprsl.product_request_id = main_table.product_request_id AND mvprsl.parent_id = ' . $productRequestId,
            ['mvprsl.product_request_id']
        );
        if ($collection && $collection->getSize() > 0) {
            foreach ($collection as $request) {
                $request->delete();
            }
        }
        /* Delete child products if exists. */
    }
}
