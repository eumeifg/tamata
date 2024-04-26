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

use Magedelight\Vendor\Model\Data\VendorSecure;
use Magedelight\Vendor\Model\Data\VendorSecureFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Registry for \Magedelight\Vendor\Model\Vendor
 */
class VendorRegistry
{
    const REGISTRY_SEPARATOR = ':';

    /**
     * @var VendorFactory
     */
    private $vendorFactory;

    /**
     * @var VendorSecureFactory
     */
    private $vendorSecureFactory;

    /**
     * @var array
     */
    private $vendorRegistryById = [];

    /**
     * @var array
     */
    private $vendorRegistryByEmail = [];

    /**
     * @var array
     */
    private $vendorSecureRegistryById = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param VendorFactory $vendorFactory
     * @param VendorSecureFactory $vendorSecureFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        VendorFactory $vendorFactory,
        VendorSecureFactory $vendorSecureFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->vendorSecureFactory = $vendorSecureFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve Vendor Model from registry given an id
     *
     * @param string $vendorId
     * @return Vendor
     * @throws NoSuchEntityException
     */
    public function retrieve($vendorId)
    {
        if (isset($this->vendorRegistryById[$vendorId])) {
            return $this->vendorRegistryById[$vendorId];
        }
        /** @var Vendor $vendor */

        $vendor = $this->vendorFactory->create()->load($vendorId);

        if (!$vendor->getId()) {
            throw NoSuchEntityException::singleField('sellerId', $vendorId);
        } else {
            $emailKey = $this->getEmailKey($vendor->getEmail());
            $this->vendorRegistryById[$vendorId] = $vendor;
            $this->vendorRegistryByEmail[$emailKey] = $vendor;
            return $vendor;
        }
    }

    /**
     * Retrieve Vendor Model from registry given an email
     *
     * @param string $vendorEmail Vendors email address
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return Vendor
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function retrieveByEmail($vendorEmail, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $emailKey = $this->getEmailKey($vendorEmail);
        if (isset($this->vendorRegistryByEmail[$emailKey])) {
            return $this->vendorRegistryByEmail[$emailKey];
        }

        /** @var Vendor $vendor */
        $vendor = $this->vendorFactory->create();

        if (isset($websiteId)) {
            $vendor->setWebsiteId($websiteId);
        }

        $vendor->loadByEmail($vendorEmail);
        if (!$vendor->getEmail()) {
            /* vendor does not exist*/
            throw new NoSuchEntityException(
                __(
                    NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                    [
                        'fieldName' => 'email',
                        'fieldValue' => $vendorEmail,
                        'field2Name' => 'websiteId',
                        'field2Value' => $websiteId,
                    ]
                )
            );
        } else {
            $this->vendorRegistryById[$vendor->getId()] = $vendor;
            $this->vendorRegistryByEmail[$emailKey] = $vendor;
            return $vendor;
        }
    }

    /**
     * Retrieve VendorSecure Model from registry given an id
     *
     * @param int $vendorId
     * @return VendorSecure
     * @throws NoSuchEntityException
     */
    public function retrieveSecureData($vendorId)
    {
        if (isset($this->vendorSecureRegistryById[$vendorId])) {
            return $this->vendorSecureRegistryById[$vendorId];
        }
        /** @var Vendor $vendor */
        $vendor = $this->retrieve($vendorId);
        /** @var $vendorSecure VendorSecure*/
        $vendorSecure = $this->vendorSecureFactory->create();
        $vendorSecure->setPasswordHash($vendor->getPasswordHash());

        $vendorSecure->setRpToken($vendor->getRpToken());
        $vendorSecure->setRpTokenCreatedAt($vendor->getRpTokenCreatedAt());
        /*$vendorSecure->setDeleteable($vendor->isDeleteable());*/
        $this->vendorSecureRegistryById[$vendor->getId()] = $vendorSecure;

        return $vendorSecure;
    }

    /**
     * Remove instance of the Vendor Model from registry given an id
     *
     * @param int $vendorId
     * @return void
     */
    public function remove($vendorId)
    {
        if (isset($this->vendorRegistryById[$vendorId])) {
            /** @var Vendor $vendor */
            $vendor = $this->vendorRegistryById[$vendorId];
            $emailKey = $this->getEmailKey($vendor->getEmail());
            unset($this->vendorRegistryByEmail[$emailKey]);
            unset($this->vendorRegistryById[$vendorId]);
            unset($this->vendorSecureRegistryById[$vendorId]);
        }
    }

    /**
     * Remove instance of the Vendor Model from registry given an email
     *
     * @param string $vendorEmail Vendors email address
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return void
     */
    public function removeByEmail($vendorEmail, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $emailKey = $this->getEmailKey($vendorEmail);
        if ($emailKey) {
            /** @var Vendor $vendor */
            $vendor = $this->vendorRegistryByEmail[$emailKey];
            unset($this->vendorRegistryByEmail[$emailKey]);
            unset($this->vendorRegistryById[$vendor->getId()]);
            unset($this->vendorSecureRegistryById[$vendor->getId()]);
        }
    }

    /**
     * Create registry key
     *
     * @param string $vendorEmail
     * @param string $websiteId
     * @return string
     */
    protected function getEmailKey($vendorEmail)
    {
        return $vendorEmail;
    }

    /**
     * Replace existing vendor model with a new one.
     *
     * @param Vendor $vendor
     * @return $this
     */
    public function push(Vendor $vendor)
    {
        $this->vendorRegistryById[$vendor->getId()] = $vendor;
        $emailKey = $this->getEmailKey($vendor->getEmail());
        $this->vendorRegistryByEmail[$emailKey] = $vendor;
        return $this;
    }
}
