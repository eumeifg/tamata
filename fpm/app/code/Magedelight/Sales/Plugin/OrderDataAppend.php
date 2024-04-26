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
namespace Magedelight\Sales\Plugin;

class OrderDataAppend
{

    /**
     * OrderDataAppend constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magedelight\Sales\Api\Data\OrderDataInterfaceFactory $orderDataInterface
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Sales\Api\Data\OrderDataInterfaceFactory $orderDataInterface
    ) {
        $this->logger = $logger;
        $this->vendorRepository = $vendorRepository;
        $this->orderDataInterface = $orderDataInterface;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $entity
    ) {
        $items = $entity->getItems();

        foreach ($items as $item) {
            if ($item->getVendorId()) {
                try {
                    $vendorDetails =  $this->vendorRepository->getById($item->getVendorId());
                    $orderData = $this->orderDataInterface->create();
                    $orderData->setVendorId($vendorDetails->getVendorId());
                    $orderData->setVendorName($vendorDetails->getBusinessName());
                    $orderData->setVendorOrderId($item->getVendorOrderId());
                    $extensionAttributes = $item->getExtensionAttributes();
                    $extensionAttributes->setVendorData($orderData);
                    $item->setExtensionAttributes($extensionAttributes);
                } catch (\Exception $exception) {
                    return $entity;
                }
            }
        }
        return $entity;
    }

    /**
     *
     */
    protected function processMediaUrls()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $productImagePath = $mediaUrl . 'catalog/product';
        $vendorLogoPath = $mediaUrl . 'vendor/logo';
        $this->mdProductDataInterface->setMediaPath($productImagePath);
        $this->mdProductDataInterface->setVendorLogoPath($vendorLogoPath);
    }

    /**
     * @param $collection
     */
    protected function processVendorCollection($collection)
    {
        try {
            $vId = ($this->_request->getParam('v', false));

            $this->vendorProduct->_addVendorData($collection);
            $this->vendorProduct->_addRbVendorProductWebsiteData($collection);
            $this->vendorProduct->_addRbVendorProductStoreData($collection);
            $this->vendorProduct->_addStoreIds($collection);
            $this->vendorProduct->_addWebsiteIds($collection);
            $this->vendorProduct->addProductData($collection);
            $collection->addFilterToMap('product_name', 'cpev.value');
            $collection->addFilterToMap('vendor_id', 'main_table.vendor_id');
            $collection->addFilterToMap('store_id', 'rbvps.store_id');
            $collection->getSelect()->group('main_table.vendor_product_id');
            $collection->processCollectionForFrontend($collection);
            $vendorCollectionSize =  $collection->getSize();

            if ($vId) {
                if ($vendorCollectionSize > 1) {
                    $vendorIdWiseCollection = clone $collection;
                    $vendorIdWiseCollection->addFieldToFilter('rv.vendor_id', ['eq' => $vId]);
                    $defaultVendorData[] = $vendorIdWiseCollection->getFirstItem()->getData();
                } else {
                    $defaultVendorData[] = $collection->getFirstItem()->getData();
                }
            } else {
                $defaultVendorData[] = $collection->getFirstItem()->getData();
            }

            $this->mdProductDataInterface->setDefaultVendorData($defaultVendorData);

            if ($vendorCollectionSize > 1) {
                $otherVendorCollection = clone $collection;
                $otherVendorData = $otherVendorCollection->getData();

                if ($vId) {
                    $removeVendorId = $vId;
                } else {
                    $removeVendorId = $collection->getFirstItem()->getVendorId();
                }

                foreach ($otherVendorData as $key => $otherVendor) {
                    if ($otherVendor['vendor_id'] == $removeVendorId) {
                        unset($otherVendorData[$key]);
                    }
                }
                $this->mdProductDataInterface->setOtherVendorData($otherVendorData);
            }
        } catch (\Exception $e) {
            $this->logger->critical('Error message', ['exception' => $e]);
        }

    }

    /**
     * @param $entity
     * @param $attributes
     */
    protected function processAdditionalInformation($entity, $attributes)
    {
        $data = [];
        $attributes = $attributes;
        $attributeCounter = 0;
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $value = $attribute->getFrontend()->getValue($entity);

                if ($value instanceof Phrase) {
                    $value = (string)$value;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen(trim($value))) {
                    $this->mdProductAdditionalData->setLabel($attribute->getStoreLabel());
                    $this->mdProductAdditionalData->setValue($value);
                    $this->mdProductAdditionalData->setCode($attribute->getAttributeCode());
                    $data[$attributeCounter] = $this->mdProductAdditionalData->getData();

                    $attributeCounter++;
                }
            }
        }
        $this->mdProductDataInterface->setAdditionalInformation($data);
    }

    /**
     * @param $productId
     */
    protected function processWishlistFlag($productId)
    {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            //$pids = [];
            $collection = $this->wishlistCollectionFactory->create()
                ->addCustomerIdFilter($customerId)->addFieldToFilter('product_id', $productId);

            if ($collection->getSize() > 0) {
                $this->mdProductDataInterface->setWishlistFlag(true);
            } else {
                $this->mdProductDataInterface->setWishlistFlag(false);
            }
        } else {
            $this->mdProductDataInterface->setWishlistFlag(false);
        }
    }

    /**
     * @param $entity
     */
    protected function processReviews($entity)
    {
        $this->_reviewFactory->create()->getEntitySummary($entity, $this->storeManager->getStore()->getId());
        $reviewCount = $entity->getRatingSummary()->getReviewsCount();
        $ratingSummary = $entity->getRatingSummary()->getRatingSummary();
        if ($reviewCount == null) {
            $reviewCount = 0;
        }
        if ($ratingSummary == null) {
            $ratingSummary = 0;
        }
        $this->mdProductDataInterface->setRatingSummary($ratingSummary);
        $this->mdProductDataInterface->setReviewCount($reviewCount);
        return;
    }
}
