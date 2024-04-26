<?php

namespace MDC\Catalog\Controller\Adminhtml\OffersImportExportChild\Offers;

use Magedelight\Catalog\Api\ProductStoreRepositoryInterface;
use Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;


class SaveRewrite extends \Magedelight\OffersImportExportChild\Controller\Adminhtml\Offers\Save
{
    /**
     * @param $data
     * @param $product
     * @param $vendorProductId
     * @param $productType
     * @param $vendorSku
     * @throws \Exception
     */
    protected function saveWebsiteData($data, $product, $vendorProductId, $productType, $vendorSku)
    {
        $websiteDataArray = [];
        $isAssociate = false;
        $categoryIds = $product->getCategoryIds();

        $availableWebsites = $data['website_id'];
        $data['category_id'] = 0;
        if (!empty($categoryIds)) {
            $data['category_id'] = $categoryIds[0];
        }
        if ($productType == self::CORE_PRODUCT_TYPE_ASSOCIATED) {
            $isAssociate = true;
        }
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $productType = Configurable::TYPE_CODE;
            $isAssociate = true;
        }

        $websiteCode =  $data['website_id'];
        $vendorProductWebsite = $this->vendorProductWebsiteFactory->create();

        $this->setWebsiteDetails($vendorProductWebsite, $data);
        $vendorProductWebsite->setData('vendor_product_id', $vendorProductId);
        $vendorProductWebsite->setData('vendor_id', $data['vendor_id']);
        $vendorProductWebsite->setData('website_id', $data['website_id']);
        $vendorProductWebsite->setData('status', $data['status']);
        $vendorProductWebsite->setData('cost_price_iqd', $data['cost_price_iqd']);
        $vendorProductWebsite->setData('cost_price_usd', $data['cost_price_usd']);

        try {
            $vendorProductWebsite->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
