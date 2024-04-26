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
namespace Magedelight\Vendor\Model\Config;

class Share extends \Magento\Framework\App\Config\Value implements \Magento\Framework\Option\ArrayInterface
{
     /**
     * Xml config path to vendors sharing scope value
     *
     */
    const XML_PATH_VENDOR_ACCOUNT_SHARE = 'vendor/account_share/scope';

    /**
     * Possible vendor sharing scopes
     *
     */
    const SHARE_GLOBAL = 0;

    const SHARE_WEBSITE = 1;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor
     */
    protected $_vendorResource;

    /** @var  \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendor $vendorResource
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Model\ResourceModel\Vendor $vendorResource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_vendorResource = $vendorResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Check whether current vendors sharing scope is global
     *
     * @return bool
     */
    public function isGlobalScope()
    {
        return !$this->isWebsiteScope();
    }

    /**
     * Check whether current vendors sharing scope is website
     *
     * @return bool
     */
    public function isWebsiteScope()
    {
        return $this->_config->getValue(
            self::XML_PATH_VENDOR_ACCOUNT_SHARE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == self::SHARE_WEBSITE;
    }

    /**
     * Get possible sharing configuration options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [self::SHARE_GLOBAL => __('Global'), self::SHARE_WEBSITE => __('Per Website')];
    }

    /**
     * Check for email duplicates before saving vendors sharing options
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value == self::SHARE_GLOBAL) {
            if ($this->_vendorResource->findEmailDuplicates()) {
                //@codingStandardsIgnoreStart
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'We can\'t share vendor accounts globally when the accounts share identical email addresses on more than one website.'
                    )
                );
                //@codingStandardsIgnoreEnd
            }
        }
        return $this;
    }

    /**
     * Returns shared website Ids.
     *
     * @param int $websiteId the ID to use if website scope is on
     * @return int[]
     */
    public function getSharedWebsiteIds($websiteId)
    {
        $ids = [];
        if ($this->isWebsiteScope()) {
            $ids[] = $websiteId;
        } else {
            foreach ($this->_storeManager->getWebsites() as $website) {
                $ids[] = $website->getId();
            }
        }
        return $ids;
    }
}
