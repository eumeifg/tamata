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
namespace Magedelight\Vendor\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Description of Microsite
 *
 * @author Rocket Bazaar Core Team
 */
class Microsite extends AbstractExtensibleModel implements \Magedelight\Vendor\Api\Data\MicrositeInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'md_vendor_microsites';

    /**
     * @var string
     */
    protected $_cacheTag = 'md_vendor_microsites';

    protected $_vendorUrls = [];

    protected $_currentStore;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'md_vendor_microsites';

    protected $_storeManager;

    /**
     * Microsite constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Vendor\Model\ResourceModel\Microsite::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTitle()
    {
        return $this->getData(self::PAGE_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPageTitle($pagetitle)
    {
        return $this->setData(self::PAGE_TITLE, $pagetitle);
    }

    /**
     * {@inheritdoc}
     */
    public function getBanner()
    {
        return $this->getData(self::BANNER);
    }

    /**
     * {@inheritdoc}
     */
    public function setBanner($banner)
    {
        return $this->setData(self::BANNER, $banner);
    }

    /**
     * {@inheritdoc}
     */
    public function getVendorId()
    {
        return $this->getData(self::VENDOR_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(self::VENDOR_ID, $vendorId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setUrlKey($urlKey)
    {
        return $this->getData(self::URL_KEY, $urlKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaKeyword()
    {
        return $this->getData(self::META_KEYWORD);
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaKeyword($metaKeyword)
    {
        return $this->setData(self::META_KEYWORD, $metaKeyword);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function getGoogleAnalyticsNumber()
    {
        return $this->getData(self::GOOGLE_ANALYTIC_ACC);
    }

    /**
     * {@inheritdoc}
     */
    public function setGoogleAnalyticsNumber($googleAnalyticsNumber)
    {
        return $this->setData(self::GOOGLE_ANALYTIC_ACC, $googleAnalyticsNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setShortDescription($shortDescription)
    {
        return $this->setData(self::SHORT_DESCRIPTION, $shortDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerSupportTimeFrom()
    {
        return $this->getData(self::SUPPORT_FROM);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerSupportTimeFrom($supportFrom)
    {
        return $this->setData(self::SUPPORT_FROM, $supportFrom);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerSupportTimeTo()
    {
        return $this->getData(self::SUPPORT_TO);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerSupportTimeTo($supportTo)
    {
        return $this->setData(self::SUPPORT_TO, $supportTo);
    }

    /**
     * {@inheritdoc}
     */
    public function getTwitterPage()
    {
        return $this->getData(self::TWITTER_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTwitterPage($twitterPage)
    {
        return $this->setData(self::TWITTER_PAGE, $twitterPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getGooglePage()
    {
        return $this->getData(self::GOOGLE_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setGooglePage($googlePage)
    {
        return $this->setData(self::GOOGLE_PAGE, $googlePage);
    }

    /**
     * {@inheritdoc}
     */
    public function getFacebookPage()
    {
        return $this->getData(self::FACEBOOK_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFacebookPage($facebookPage)
    {
        return $this->setData(self::FACEBOOK_PAGE, $facebookPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getTumblerPage()
    {
        return $this->getData(self::TUMBLR_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTumblerPage($tumblrPage)
    {
        return $this->setData(self::TUMBLR_PAGE, $tumblrPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstagramPage()
    {
        return $this->getData(self::INSTAGRAM_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setInstagramPage($instagramPage)
    {
        return $this->setData(self::INSTAGRAM_PAGE, $instagramPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryPolicy()
    {
        return $this->getData(self::DELIVERY_POLICY);
    }

    /**
     * {@inheritdoc}
     */
    public function setDeliveryPolicy($deliveryPolicy)
    {
        return $this->setData(self::DELIVERY_POLICY, $deliveryPolicy);
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnPolicy()
    {
        return $this->getData(self::RETURN_POLICY);
    }

    /**
     * {@inheritdoc}
     */
    public function setReturnPolicy($returnPolicy)
    {
        return $this->setData(self::RETURN_POLICY, $returnPolicy);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * get Delete Banner
     * @return bool|null
     */
    public function getDeleteBanner()
    {
        return $this->getData(self::DELETE_BANNER);
    }

    /**
     * set Delete Banner
     * @param bool|null $deleteBanner
     * @return $this
     */
    public function setDeleteBanner($deleteBanner)
    {
        return $this->setData(self::DELETE_BANNER, $deleteBanner);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @param integer $vendorId
     * @return array|bool|null
     */
    public function getVendorMicrositeUrl($vendorId)
    {
        if (!$vendorId) {
            return false;
        }
        return $this->getVendorMicroSiteUrls($vendorId);
    }

    /**
     * @param integer $vendorId
     * @return array|null
     */
    protected function getVendorMicroSiteUrls($vendorId)
    {
        $this->_vendorUrls = null;
        if (empty($this->_vendorUrls)) {
            $storeId = $this->getCurrentStore();
            $micrositeCollection = $this->getCollection()->addFieldToSelect(['url_key','vendor_id'])
                    ->addFieldToFilter('main_table.vendor_id', $vendorId)
                    ->addFieldToFilter('main_table.store_id', $storeId)
                    ->getFirstItem();
            if ($micrositeCollection->getId()) {
                $this->_vendorUrls = $micrositeCollection->getUrlKey();
            }
        }
        return $this->_vendorUrls;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCurrentStore()
    {
        if (!$this->_currentStore) {
            $this->_currentStore = $this->_storeManager->getStore()->getStoreId();
        }
        return $this->_currentStore;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\MicrositeExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }
}
