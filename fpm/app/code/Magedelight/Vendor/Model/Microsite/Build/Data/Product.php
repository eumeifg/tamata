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
namespace Magedelight\Vendor\Model\Microsite\Build\Data;

use Magedelight\Vendor\Api\Data\Microsite\ProductInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Product extends AbstractExtensibleModel implements ProductInterface
{

    /**
     * Product id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData('entity_id');
    }

    /**
     * Set product id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData('entity_id', $id);
    }

    /**
     * Product sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getData(ProductInterface::SKU);
    }

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(ProductInterface::SKU, $sku);
    }

    /**
     * Product name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getData(ProductInterface::NAME);
    }

    /**
     * Set product name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(ProductInterface::NAME, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getPrice()
    {
        return $this->getData(ProductInterface::PRICE);
    }

    /**
     * {@inheritDoc}
     */
    public function setPrice($price)
    {
        return $this->setData(ProductInterface::PRICE, $price);
    }

    /**
     * {@inheritDoc}
     */
    public function getVendorPrice()
    {
        return $this->getData(ProductInterface::VENDOR_PRICE);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorPrice($price)
    {
        return $this->setData(ProductInterface::VENDOR_PRICE, $price);
    }

    /**
     * {@inheritDoc}
     */
    public function getVendorId()
    {
        return $this->getData(ProductInterface::VENDOR_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(ProductInterface::VENDOR_ID, $vendorId);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorSpecialPrice($price)
    {
        return $this->setData(ProductInterface::VENDOR_SPECIAL_PRICE, $price);
    }

    /**
     * {@inheritDoc}
     */
    public function getVendorSpecialPrice()
    {
        return $this->getData(ProductInterface::VENDOR_SPECIAL_PRICE);
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeId()
    {
        return $this->getData(ProductInterface::TYPE_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setTypeId($typeId)
    {
        return $this->setData(ProductInterface::TYPE_ID, $typeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getMediaGalleryEntries()
    {
        return $this->getData(ProductInterface::MEDIA_GALLERY_ENTRIES);
    }

    /**
     * {@inheritDoc}
     */
    public function setMediaGalleryEntries(array $mediaGalleryEntries = null)
    {
        return $this->setData(ProductInterface::MEDIA_GALLERY_ENTRIES, $mediaGalleryEntries);
    }

    /**
     * {@inheritDoc}
     */
    public function getTierPrices()
    {
        return $this->getData(ProductInterface::TIER_PRICES);
    }

    /**
     * {@inheritDoc}
     */
    public function setTierPrices(array $tierPrices = null)
    {
        return $this->setData(ProductInterface::TIER_PRICES, $tierPrices);
    }

    /**
     * {@inheritDoc}
     */
    public function getReviewCount()
    {
        return $this->getData(ProductInterface::REVIEW_COUNT);
    }

    /**
     * {@inheritDoc}
     */
    public function setReviewCount($reviewCount)
    {
        return $this->setData(ProductInterface::REVIEW_COUNT, $reviewCount);
    }

    /**
     * {@inheritDoc}
     */
    public function getRatingSummary()
    {
        return $this->getData(ProductInterface::RATING_SUMMARY);
    }

    /**
     * {@inheritDoc}
     */
    public function getWishlistFlag()
    {
        return $this->getData(ProductInterface::WISHLIST_FLAG);
    }

    /**
     * {@inheritDoc}
     */
    public function setWishlistFlag($wishlistFlag)
    {
        return $this->setData(ProductInterface::WISHLIST_FLAG, $wishlistFlag);
    }

    /**
     * {@inheritDoc}
     */
    public function setMediaPath($path)
    {
        return $this->setData(ProductInterface::MEDIA_PATH, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function getMediaPath()
    {
        return $this->getData(ProductInterface::MEDIA_PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function setRatingSummary($ratingSummary)
    {
        return $this->setData(ProductInterface::RATING_SUMMARY, $ratingSummary);
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \Magedelight\Vendor\Api\Data\Microsite\ProductExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\Microsite\ProductExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
