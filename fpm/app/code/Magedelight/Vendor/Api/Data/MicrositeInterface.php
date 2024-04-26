<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface MicrositeInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'microsite_id';
    const VENDOR_ID = 'vendor_id';
    const STORE_ID = 'store_id';
    const URL_KEY = 'url_key';
    const PAGE_TITLE = 'page_title';
    const BANNER = 'banner';
    const META_KEYWORD = 'meta_keyword';
    const META_DESCRIPTION ='meta_description';
    const GOOGLE_ANALYTIC_ACC = 'google_analytics_account_number';
    const SHORT_DESCRIPTION = 'short_description';
    const SUPPORT_FROM ='customer_support_time_from';
    const SUPPORT_TO = 'customer_support_time_to';
    const TWITTER_PAGE = 'twitter_page';
    const GOOGLE_PAGE = 'google_page';
    const FACEBOOK_PAGE = 'facebook_page';
    const TUMBLR_PAGE = 'tumbler_page';
    const INSTAGRAM_PAGE = 'instagram_page';
    const DELIVERY_POLICY ='delivery_policy';
    const RETURN_POLICY = 'return_policy';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETE_BANNER = 'delete_banner';

    /**
     * Get microsite id
     * @return int
     */
    public function getId();

    /**
     * Set microsite id
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Vendor Id
     * @return int
     */
    public function getVendorId();

    /**
     * Set microsite id
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId($vendorId);

    /**
     * Get Store Id
     * @return int
     */
    public function getStoreId();

    /**
     * Set Store Id
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get Url Key
     * @return string|null
     */
    public function getUrlKey();

    /**
     * Set Url Key
     * @param string|null $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * Get page title
     * @return string|null
     */
    public function getPageTitle();

    /**
     * Set page title
     * @param string|null $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle);

    /**
     * Get banner url
     * @return string|null
     */
    public function getBanner();

    /**
     * Set banner url
     * @param string|null $banner
     * @return $this
     */
    public function setBanner($banner);

    /**
     * Get Meta Keyword
     * @return string|null
     */
    public function getMetaKeyword();

    /**
     * Set Meta Keyword
     * @param string|null $metaKeyword
     * @return $this
     */
    public function setMetaKeyword($metaKeyword);

    /**
     * Get Meta Description
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * set Meta Description
     * @param string|null $metaDescription
     * @return $this
     */
    public function setMetaDescription($metaDescription);

    /**
     * get Google Analytics Number
     * @return string|null
     */
    public function getGoogleAnalyticsNumber();

    /**
     * set Google Analytics Number
     * @param string|null $googleAnalyticsNumber
     * @return $this
     */
    public function setGoogleAnalyticsNumber($googleAnalyticsNumber);

    /**
     * set Short Description
     * @return string|null
     */
    public function getShortDescription();

    /**
     * set Short Description
     * @param string|null $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription);

    /**
     * get Support From Time
     * @return string|null
     */
    public function getCustomerSupportTimeFrom();

    /**
     * set Support From Time
     * @param string $supportFrom
     * @return $this
     */
    public function setCustomerSupportTimeFrom($supportFrom);

    /**
     * get Support From Time
     * @return string|null
     */
    public function getCustomerSupportTimeTo();

    /**
     * set Support From Time
     * @param string $supportFrom
     * @return $this
     */
    public function setCustomerSupportTimeTo($supportTo);

    /**
     * get Twitter Page
     * @return string|null
     */
    public function getTwitterPage();

    /**
     * set Twitter Page
     * @param string|null $twitterPage
     * @return $this
     */
    public function setTwitterPage($twitterPage);

    /**
     * get Google Page
     * @return string|null
     */
    public function getGooglePage();

    /**
     * set Google Page
     * @param string|null $googlePage
     * @return $this
     */
    public function setGooglePage($googlePage);

    /**
     * get Facebook Page
     * @return string|null
     */
    public function getFacebookPage();

    /**
     * set Facebook Page
     * @param string|null $facebookPage
     * @return $this
     */
    public function setFacebookPage($facebookPage);

    /**
     * get Tumblr Page
     * @return string|null
     */
    public function getTumblerPage();

    /**
     * set Tumblr Page
     * @param string|null $tumblrPage
     * @return $this
     */
    public function setTumblerPage($tumblrPage);

    /**
     * get Instagram Page
     * @return string|null
     */
    public function getInstagramPage();

    /**
     * set Instagram Page
     * @param string|null $instagramPage
     * @return $this
     */
    public function setInstagramPage($instagramPage);

    /**
     * get Instagram Page
     * @return string|null
     */
    public function getDeliveryPolicy();

    /**
     * set Delivery Policy
     * @param string|null $deliveryPolicy
     * @return $this
     */
    public function setDeliveryPolicy($deliveryPolicy);

    /**
     * get Instagram Page
     * @return string|null
     */
    public function getReturnPolicy();

    /**
     * set Return Policy
     * @param string|null $returnPolicy
     * @return $this
     */
    public function setReturnPolicy($returnPolicy);

    /**
     * get Created At
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * get Created At
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * get Updated At
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * set Updated At
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * get Delete Banner
     * @return bool|null
     */
    public function getDeleteBanner();

    /**
     * set Delete Banner
     * @param bool|null $deleteBanner
     * @return $this
     */
    public function setDeleteBanner($deleteBanner);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\MicrositeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\MicrositeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\MicrositeExtensionInterface $extensionAttributes
    );
}
