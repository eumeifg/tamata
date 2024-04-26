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

use Magedelight\Catalog\Api\ProductRequestRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as CoreConfigurable;

class ProductRequestManagement implements \Magedelight\Catalog\Api\ProductRequestManagementInterface
{
    const STATUS_APPROVED_NON_LIVE = 0;
    const STATUS_APPROVED_LIVE = 1;

    /*
     * product type used for not sending mail of configure associated product
     */
    const CORE_PRODUCT_TYPE_ASSOCIATED = 'config-simple';
    const CORE_PRODUCT_TYPE_DEFAULT = 'simple';

    const CONFIGURABLE_PRODUCT_SKU = 'configurable-product-sku';
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;

    const XML_PATH_EXCLUDE_ATTRIBUTES = 'vendor_product/vital_info/attributes';

    const STATUS_PARAM_NAME = 'status';
    const DISAPPROVE_MESSAGE = 'disapprove_message';
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DISAPPROVED = 2;

    const CONDITION_USED = 0;
    const CONDITION_NEW = 1;
    const CONDITION_RENTAL = 2;

    const WARRANTY_MANUFACTURER = 1;
    const WARRANTY_SELLER = 2;

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\Type\Simple\Existing\Add
     */
    protected $simpleProductRequestOffer;

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\Type\Configurable\Add
     */
    protected $addConfigurableProductRequest;

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\Type\Simple\Add
     */
    protected $addSimpleProductRequest;

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\Type\Configurable\Update
     */
    protected $updateConfigurableProductRequest;

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\Type\Simple\Update
     */
    protected $updateSimpleProductRequest;

    /**
     *
     * @param ProductRequestRepositoryInterface $productRequestRepository
     * @param \Magedelight\Catalog\Model\Product\Request\Type\Simple\Existing\Add $simpleProductRequestOffer
     * @param \Magedelight\Catalog\Model\Product\Request\Type\Simple\Add $addSimpleProductRequest
     * @param \Magedelight\Catalog\Model\Product\Request\Type\Simple\Update $updateSimpleProductRequest
     * @param \Magedelight\Catalog\Model\Product\Request\Type\Configurable\Add $addConfigurableProductRequest
     * @param \Magedelight\Catalog\Model\Product\Request\Type\Configurable\Update $updateConfigurableProductRequest
     */
    public function __construct(
        ProductRequestRepositoryInterface $productRequestRepository,
        \Magedelight\Catalog\Model\Product\Request\Type\Simple\Existing\Add $simpleProductRequestOffer,
        \Magedelight\Catalog\Model\Product\Request\Type\Simple\Add $addSimpleProductRequest,
        \Magedelight\Catalog\Model\Product\Request\Type\Simple\Update $updateSimpleProductRequest,
        \Magedelight\Catalog\Model\Product\Request\Type\Configurable\Add $addConfigurableProductRequest,
        \Magedelight\Catalog\Model\Product\Request\Type\Configurable\Update $updateConfigurableProductRequest
    ) {
        $this->productRequestRepository = $productRequestRepository;
        $this->simpleProductRequestOffer = $simpleProductRequestOffer;
        $this->updateSimpleProductRequest = $updateSimpleProductRequest;
        $this->updateConfigurableProductRequest = $updateConfigurableProductRequest;
        $this->addSimpleProductRequest = $addSimpleProductRequest;
        $this->addConfigurableProductRequest = $addConfigurableProductRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductRequest($vendorId, $requestData, $isNew = true, $is_offered = 0)
    {
        $pid = $requestData['offer']['marketplace_product_id'];
        $prid = (array_key_exists('product_request_id', $requestData['offer'])) ?
            $requestData['offer']['product_request_id'] : '';
        $hasVariants = (array_key_exists('has_variants', $requestData)) ? $requestData['has_variants'] : false;
        if (array_key_exists('product', $requestData)) {
            foreach ($requestData['product']['images'] as $imageKey => $imageValue) {
                foreach ($imageValue as $key => $value) {
                    $requestData['product']['images'][$imageKey][$key] = trim(str_replace('.tmp', '', $value));
                }
            }
            $requestData['product']['swatch_image'] = trim(
                str_replace('.tmp', '', $requestData['product']['swatch_image'])
            );
            $requestData['product']['image']      = trim(str_replace('.tmp', '', $requestData['product']['image']));
            $requestData['product']['small_image']= trim(
                str_replace('.tmp', '', $requestData['product']['small_image'])
            );
            $requestData['product']['thumbnail']  = trim(str_replace('.tmp', '', $requestData['product']['thumbnail']));
        }

        if ((isset($requestData['store']) && $requestData['store'] != '' && $prid != '') || !$isNew) {
            $productRequest = $this->productRequestRepository->getById($prid);
            $productType = 'simple';
            $productGetData = $productRequest->getData();
            if ($productRequest->getId() && $productGetData['has_variants'] == 1) {
                $productType = CoreConfigurable::TYPE_CODE;
                $this->updateConfigurableProductRequest->execute($vendorId, $requestData, $productType);
            } else {
                $this->updateSimpleProductRequest->execute($vendorId, $requestData, $productType);
            }
        } else {
            try {
                if ($pid !== null && $pid !== '') {
                    if ($hasVariants) {
                        if ($is_offered != 1) {
                            $this->addConfigurableProductRequest->execute($vendorId, $requestData, $isNew);
                        }
                    } else {
                        if ($is_offered == 1) {
                            /* Add offer on existing simple product.*/
                            $this->simpleProductRequestOffer->execute($vendorId, $requestData);
                        } else {
                            $this->addSimpleProductRequest->execute($vendorId, $requestData, $isNew);
                        }
                    }
                } elseif (!$prid) {
                    if ($hasVariants) {
                        $this->addConfigurableProductRequest->execute($vendorId, $requestData, $isNew);
                    } else {
                        $this->addSimpleProductRequest->execute($vendorId, $requestData, $isNew);
                    }
                }
            } catch (\Zend_Db_Statement_Exception $e) {
                switch ($e->getCode()) {
                    case 23000:
                        throw new \Exception(__('Vendor Sku already exist.'));
                    default:
                        throw new \Exception(__($e->getMessage()));
                }
            } catch (\Exception $e) {
                throw new \Exception(__($e->getMessage()));
            }
        }
        return $this;
    }
}
