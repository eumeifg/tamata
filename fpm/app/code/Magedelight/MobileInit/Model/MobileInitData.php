<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Magedelight\MobileInit\Model;

use Magedelight\MobileInit\Api\Data\MobileInitDataInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class MobileInitData extends AbstractExtensibleModel implements MobileInitDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function setCurrentLanguage(string $language)
    {
        return $this->setData('currentLanguage', $language);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setCurrentCurrency(string $currency)
    {
        return $this->setData('currentCurrency', $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentLanguage()
    {
        return $this->getData('currentLanguage');
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCurrency()
    {
        return $this->getData('currentCurrency');
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentCurrencySymbol(string $symbol = null)
    {
        return $this->setData('currentCurrencySymbol', $symbol);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->getData('currentCurrencySymbol');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAllowedGuestCheckout(bool $isAllowed)
    {
        return $this->setData('isAllowedGuestCheckout', $isAllowed);
    }

    /**
     * @return bool
     */
    public function getIsAllowedGuestCheckout()
    {
        return $this->getData('isAllowedGuestCheckout');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAllowedGuestReview(bool $isAllowed)
    {
        return $this->setData('isAllowedGuestReview', $isAllowed);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAllowedGuestReview()
    {
        return $this->getData('isAllowedGuestReview');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAllowedGuestReferral(bool $isAllowed)
    {
        return $this->setData('isAllowedGuestReferral', $isAllowed);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAllowedGuestReferral()
    {
        return $this->getData('isAllowedGuestReferral');
    }

    /**
     * {@inheritdoc}
     */
    public function setCartItems(int $cartItemCount)
    {
        return $this->setData('cartItems', $cartItemCount);
    }


    /**
     * {@inheritdoc}
     */
    public function setCartItemsQuantity(int $cartItemQty)
    {
        return $this->setData('cartItemsQty', $cartItemQty);
    }

    /**
     * {@inheritdoc}
     */
    public function getCartItems()
    {
        return $this->getData('cartItems');
    }

    /**
     * {@inheritdoc}
     */
    public function getCartItemsQuantity()
    {
        return $this->getData('cartItemsQty');
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigModules(array $configModules)
    {
        return $this->setData('config_module', $configModules);
    }

     /**
      * {@inheritdoc}
      */
    public function getConfigModules()
    {
        return $this->getData('config_module');
    }

     /**
      * {@inheritdoc}
      */
    public function setCategoryItems($categoryData)
    {
        return $this->setData('categories', $categoryData);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryItems()
    {
        return $this->getData('categories');
    }

    public function addCategoryItem($mobileCategoryData)
    {
        $this->setCategoryItems(array_filter(array_merge([$this->getCategoryItems()], $mobileCategoryData)));
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function setAvailableLanguages(array $availableLanguages)
    {
        return $this->setData('available_languages', $availableLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLanguages()
    {
        return $this->getData('available_languages');
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceFormat(array $priceFormat)
    {
        return $this->setData('priceformat', $priceFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceFormat()
    {
        return $this->getData('priceformat');
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData('website_id', $websiteId);
    }
    

    /**
     * {@inheritdoc}
     */
    public function getWebsiteId()
    {
        return $this->getData('website_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setDealZoneCatId($dealzoneCatId)
    {
        return $this->setData('deal_zone_id', $dealzoneCatId);
    }
    

    /**
     * {@inheritdoc}
     */
    public function getDealZoneCatId()
    {
        return $this->getData('deal_zone_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setDealZoneCatTitle($dealzoneCatTitle)
    {
        return $this->setData('deal_zone_title', $dealzoneCatTitle);
    }
    

    /**
     * {@inheritdoc}
     */
    public function getDealZoneCatTitle()
    {
        return $this->getData('deal_zone_title');
    }

    public function setMobileCategoryBanner($mobileCategoryBanner)
    {
        return $this->setData('mobile_category_banner', $mobileCategoryBanner);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMobileCategoryBanner()
    {
        return $this->getData('mobile_category_banner');
    }

    public function setMobileCategoryImage($mobileCategoryImage)
    {
        return $this->setData('mobile_category_image', $mobileCategoryImage);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMobileCategoryImage()
    {
        return $this->getData('mobile_category_image');
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
     * @param \Magedelight\MobileInit\Api\Data\MobileInitDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magedelight\MobileInit\Api\Data\MobileInitDataExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
