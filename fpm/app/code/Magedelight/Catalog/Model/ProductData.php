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

use Magedelight\Catalog\Api\Data\ProductDataInterface;

class ProductData extends \Magento\Framework\DataObject implements ProductDataInterface
{

   /**
    * {@inheritdoc}
    */
    public function getAdditionalInformation()
    {
        return $this->getData('additional_information');
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalInformation($additionalInformation)
    {
        return $this->setData('additional_information', $additionalInformation);
    }

    public function addAdditionalInformation($additionalInformation)
    {
        return $this->setAdditionalInformation(
            array_filter(array_merge([$this->getAdditionalInformation()], $additionalInformation))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaPath()
    {
        return $this->getData('media_path');
    }

    /**
     * {@inheritdoc}
     */
    public function setMediaPath(string $mediaPath)
    {
        return $this->setData('media_path', $mediaPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getVendorLogoPath()
    {
        return $this->getData('vendor_logo_path');
    }

    /**
     * {@inheritdoc}
     */
    public function setVendorLogoPath(string $vendorLogoPath)
    {
        return $this->setData('vendor_logo_path', $vendorLogoPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultVendorData()
    {
        return $this->getData('default_vendor_data');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultVendorData(array $defaultVendorData)
    {
        return $this->setData('default_vendor_data', $defaultVendorData);
    }

    /**
     * {@inheritdoc}
     */
    public function getOtherVendorData()
    {
        return $this->getData('other_vendor_data');
    }

    /**
     * {@inheritdoc}
     */
    public function setOtherVendorData(array $otherVendorData)
    {
        return $this->setData('other_vendor_data', $otherVendorData);
    }

    /**
     * {@inheritdoc}
     */
    public function getWishlistFlag()
    {
        return $this->getData('is_wishlisted');
    }

    /**
     * {@inheritdoc}
     */
    public function setWishlistFlag(bool $wishlistFlag)
    {
        return $this->setData('is_wishlisted', $wishlistFlag);
    }

    /**
     * {@inheritdoc}
     */
    public function getReviewCount()
    {
        return $this->getData('review_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setReviewCount(int $reviewCount)
    {
        return $this->setData('review_count', $reviewCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRatingSummary()
    {
        return $this->getData('rating_summary');
    }

    /**
     * {@inheritdoc}
     */
    public function setRatingSummary(int $ratingSummary)
    {
        return $this->setData('rating_summary', $ratingSummary);
    }

    /**
     * Only for enterprise edition
     * {@inheritdoc}
     */
    public function getRuleRelatedProducts()
    {
        return $this->getData('rule_related_products');
    }


    /**
     * Only for enterprise edition
     * {@inheritdoc}
     */
    public function setRuleRelatedProducts($relatedProducts)
    {
        return $this->setData('rule_related_products',$relatedProducts); 
    }
}
