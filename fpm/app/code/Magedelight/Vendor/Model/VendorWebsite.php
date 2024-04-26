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

use Magento\Framework\Model\AbstractModel;

/**
 * @method \Magedelight\Vendor\Model\ResourceModel\VendorWebsite _getResource()
 * @method \Magedelight\Vendor\Model\ResourceModel\VendorWebsite getResource()
 */
class VendorWebsite extends AbstractModel implements \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
{
    const CACHE_TAG = 'md_vendor_website_data';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'md_vendor_website_data';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'vendor_website';

    /**
     * @var \Magento\Customer\Model\Vat
     */
    protected $_vatValidation;

    /**
     * @var \Magedelight\Theme\Model\Source\Frontend\Region
     */
    protected $region;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Encryptor $encryptor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Vat $vatValidation
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Theme\Model\Source\Frontend\Region $region,
        \Magento\Customer\Model\Vat $vatValidation,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_vatValidation = $vatValidation;
        $this->region = $region;
        parent::__construct($context, $registry);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Vendor\Model\ResourceModel\VendorWebsite::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Vendor Website id
     *
     * @return array
     */
    public function getVendorWebsiteId()
    {
        return $this->getData(\Magedelight\Vendor\Api\Data\ProductWebsiteInterface::VENDORWEBSITE_ID);
    }

    /**
     * set Vendor Website id
     *
     * @param int $vendorWebsiteId
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     */
    public function setVendorWebsiteId($ProductWebsiteId)
    {
        return $this->setData(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface::VENDORWEBSITE_ID, $vendorWebsiteId);
    }

    /**
     * set Vendor ID
     *
     * @param mixed $vendorId
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface::VENDOR_ID, $vendorId);
    }

    /**
     * get Vendor ID
     *
     * @return string
     */
    public function getVendorId()
    {
        return $this->getData(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface::VENDOR_ID);
    }

    /**
     * set Status
     *
     * @param mixed $status
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     */
    public function setStatus($status)
    {
        return $this->setData(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface::STATUS, $status);
    }

    /**
     * get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface::STATUS);
    }

    /**
     * set Website ID
     *
     * @param mixed $websiteId
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface::WEBSITE_ID, $websiteId);
    }

    /**
     * get Website ID
     *
     * @return string
     */
    public function getWebsiteId()
    {
        return $this->getData(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface::WEBSITE_ID);
    }

    public function validate()
    {
        $errors = [];

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Validate vendor registration fields
     *
     * @return bool|string[]
     */
    public function validateEdit($activePart = '')
    {
        $errors = [];

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}
