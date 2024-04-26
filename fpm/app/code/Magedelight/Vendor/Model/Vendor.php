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

use Magedelight\Backend\Model\Auth\Credential\StorageInterface;
use Magedelight\Vendor\Api\Data\VendorInterface;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

/**
 * @author Rocket Bazaar Core Team
 *  Created at 13 Feb, 2016 12:04:25 PM
 * @method array getCategoriesIds()
 * @method Vendor setCategoriesIds(array $categoryIds)
 * @method Vendor setIsChangedCategoryList(\bool $changed)
 * @method Vendor setAffectedCategoryIds(array $categoryIds)
 * @method Vendor getDeliveryZonesIds()
 * @method Vendor setDeliveryZonesIds(array $deliveryZoneIds)
 * @method Vendor setIsChangedDeliveryZoneList(\bool $changed)
 * @method Vendor setAffectedDeliveryZoneIds(array $deliveryZoneIds)
 */
class Vendor extends AbstractModel implements StorageInterface, VendorInterface
{

    /**
     * Configuration path to expiration period of reset password link
     */
    const XML_PATH_VENDOR_RESET_PASSWORD_LINK_EXPIRATION_PERIOD = 'vendor/password/reset_link_expiration_period';
    const ADMIN_VENDOR_EMAIL = 'admin@gmail.com';
    const DEFAULT_VENDOR_ATTRIBUTE = 'default_vendor';
    const MIN_PASSWORD_LENGTH = 6;
    const DEFAULT_IMAGE_SIZE = 524288;
    const IS_ENABLED_BANKING_DETAILS_XML_PATH = 'vendor/create_account/enable_bank_details';
    const IS_BANK_DETAILS_OPTIONAL_XML_PATH = 'vendor/create_account/is_bank_details_optional';

    /**
     * @var \Magedelight\Theme\Model\Source\Region
     */
    protected $region;

    /**
     * @var Encryptor
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magedelight\Vendor\Model\VendorRegistry
     */
    protected $vendorRegistry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $stringHelper;

    /**
     * @var string
     */
    protected $_eventPrefix = 'md_vendor';

    /**
     * @var string
     */
    protected $_eventObject = 'vendor';

    /**
     * Available resources flag
     *
     * @var bool
     */
    protected $_hasResources = true;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var VendorWebsiteRepository
     */
    protected $vendorWebsiteRepository;

    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Encryptor $encryptor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Encryptor $encryptor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Theme\Model\Source\Frontend\Region $region,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Vendor\Model\VendorWebsiteRepository $vendorWebsiteRepository,
        \Magedelight\Vendor\Model\VendorRegistry $vendorRegistry,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\StringUtils $stringHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_encryptor = $encryptor;
        $this->_scopeConfig = $scopeConfig;
        $this->region = $region;
        $this->vendorRepository = $vendorRepository;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        $this->vendorRegistry = $vendorRegistry;
        $this->eventManager = $eventManager;
        $this->stringHelper = $stringHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Process operation before object load
     *
     * @return Vendor
     * @throws NoSuchEntityException
     * @since 100.2.0
     */
    public function afterLoad()
    {
        $websiteVendor = $this->vendorWebsiteRepository->getVendorWebsiteData($this->getId(), $this->getWebsiteId());
        if ($websiteVendor && $websiteVendor->getVendorId()) {
            $counter = 0;
            foreach ($websiteVendor->getData() as $key => $value) {
                $this->setData($key, $value);
            }
        }
        return $this;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Vendor\Model\ResourceModel\Vendor::class);
    }

    /**
     * @return array|string[]
     */
    public function __sleep()
    {
        $properties = parent::__sleep();
        return array_diff(
            $properties,
            [
                '_scopeConfig',
                'region',
                'vendorRepository',
                'vendorWebsiteRepository',
                'vendorRegistry',
                'eventManager',
                'stringHelper'
            ]
        );
    }

    /**
     * Restoring required objects after serialization.
     *
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->region = $objectManager->get(\Magedelight\Theme\Model\Source\Frontend\Region::class);
        $this->vendorRepository = $objectManager->get(\Magedelight\Vendor\Api\VendorRepositoryInterface::class);
        $this->vendorWebsiteRepository = $objectManager->get(\Magedelight\Vendor\Model\VendorWebsiteRepository::class);
        $this->vendorRegistry = $objectManager->get(\Magedelight\Vendor\Model\VendorRegistry::class);
        $this->eventManager = $objectManager->get(\Magento\Framework\Event\ManagerInterface::class);
        $this->stringHelper = $objectManager->get(\Magento\Framework\Stdlib\StringUtils::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getVendorId()
    {
        if ($this->getIsUser() == 1) {
            $user = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magedelight\User\Model\User::class);
            return $user->getUserParentId($this->getData('vendor_id'));
        }
        return $this->getData('vendor_id');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritDoc}
     */
    public function getMobile()
    {
        return $this->getData(self::MOBILE);
    }

    /**
     * {@inheritDoc}
     */
    public function setMobile($mobile)
    {
        return $this->setData(self::MOBILE, $mobile);
    }

    /**
     * {@inheritDoc}
     */
    public function getRpToken()
    {
        return $this->getData(self::RP_TOKEN);
    }

    /**
     * {@inheritDoc}
     */
    public function setRpToken($token)
    {
        return $this->setData(self::RP_TOKEN, $token);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmailVerificationCode()
    {
        return $this->getData(self::EMAIL_VERIFICATION_CODE);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmailVerificationCode($token)
    {
        return $this->setData(self::EMAIL_VERIFICATION_CODE, $token);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmailVerified()
    {
        return $this->getData(self::EMAIL_VERIFIED);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmailVerified($var)
    {
        return $this->setData(self::EMAIL_VERIFIED, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getVat()
    {
        return $this->getData(self::VAT);
    }

    /**
     * {@inheritDoc}
     */
    public function setVat($vat)
    {
        return $this->setData(self::VAT, $vat);
    }

    /**
     * {@inheritDoc}
     */
    public function getVatDoc()
    {
        return $this->getData(self::VAT_DOC);
    }

    /**
     * {@inheritDoc}
     */
    public function setVatDoc($vat_doc)
    {
        return $this->setData(self::VAT_DOC, $vat_doc);
    }

    /**
     * {@inheritDoc}
     */
    public function getAddress1()
    {
        return $this->getData(self::ADDRESS1);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getAddress2()
    {
        return $this->getData(self::ADDRESS2);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getPincode()
    {
        return $this->getData(self::PINCODE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getPickupAddress1()
    {
        if ($this->getData(self::PICKUP_ADDRESS1)) {
            return $this->getData(self::PICKUP_ADDRESS1);
        } else {
            return $this->getData(self::ADDRESS1);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getPickupAddress2()
    {
        if ($this->getData(self::PICKUP_ADDRESS2)) {
            return $this->getData(self::PICKUP_ADDRESS2);
        } else {
            return $this->getData(self::ADDRESS2);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getPickupCity()
    {
        if ($this->getData(self::PICKUP_CITY)) {
            return $this->getData(self::PICKUP_CITY);
        } else {
            return $this->getData(self::CITY);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getPickupRegion()
    {
        if ($this->getData(self::PICKUP_REGION)) {
            return $this->getData(self::PICKUP_REGION);
        } else {
            return $this->getData(self::REGION);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getPickupRegionId()
    {
        if ($this->getData(self::PICKUP_REGION_ID)) {
            return $this->getData(self::PICKUP_REGION_ID);
        } else {
            return $this->getData(self::REGION_ID);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getPickupCountry()
    {
        if ($this->getData(self::PICKUP_COUNTRY)) {
            return $this->getData(self::PICKUP_COUNTRY);
        } else {
            return $this->getData(self::COUNTRY);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getPickupPincode()
    {
        if ($this->getData(self::PICKUP_PINCODE)) {
            return $this->getData(self::PICKUP_PINCODE);
        } else {
            return $this->getData(self::PINCODE);
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getBankAccountName()
    {
        return $this->getData(self::BANK_ACCOUNT_NAME);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getBankAccountNumber()
    {
        return $this->getData(self::BANK_ACCOUNT_NUMBER);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getBankName()
    {
        return $this->getData(self::BANK_NAME);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getBusinessName()
    {
        return $this->getData(self::BUSINESS_NAME);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getCategory()
    {
        return $this->getData(self::CATEGORY);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getIfsc()
    {
        return $this->getData(self::IFSC);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getOtherMarketPlaceUrl()
    {
        return $this->getData(self::OTHER_MARKETPLACE_PROFILE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getStatus()
    {
        return (int) $this->getData(self::STATUS);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getVacationFromDate()
    {
        return $this->getData(self::VACATION_FROM_DATE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getVacationToDate()
    {
        return $this->getData(self::VACATION_TO_DATE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getVacationMessage()
    {
        return $this->getData(self::VACATION_MESSAGE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getLogo()
    {
        return $this->getData(self::LOGO);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
        }
        return (array) $this->_getData('category_ids');
    }

    /**
     * @param $vendorIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorsCategoryDetails($vendorIds)
    {
        $result = [];
        foreach ($vendorIds as $vendorId) {
            $result[$vendorId] = $this->_getResource()->lookupCategoryIds($vendorId);
        }
        return $result;
    }

    /**
     * DeliveryZone module has been disabled
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    /*
      public function getDeliveryZoneIds()
      {
      if (!$this->hasData('delivery_zone_ids')) {
      $ids = $this->_getResource()->getDeliveryZoneIds($this);
      $this->setData('delivery_zone_ids', $ids);
      }
      return (array) $this->_getData('delivery_zone_ids');
      }
     */

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setAddress1($var)
    {
        return $this->setData(self::ADDRESS1, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setAddress2($var)
    {
        return $this->setData(self::ADDRESS2, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setCity($var)
    {
        return $this->setData(self::CITY, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setRegion($var)
    {
        return $this->setData(self::REGION, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setRegionId($var)
    {
        return $this->setData(self::REGION_ID, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setCountry($var)
    {
        return $this->setData(self::COUNTRY, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setPincode($var)
    {
        return $this->setData(self::PINCODE, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setPickupAddress1($var)
    {
        return $this->setData(self::PICKUP_ADDRESS1, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setPickupAddress2($var)
    {
        return $this->setData(self::PICKUP_ADDRESS2, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setPickupCity($var)
    {
        return $this->setData(self::PICKUP_CITY, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setPickupRegion($var)
    {
        return $this->setData(self::PICKUP_REGION, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setPickupRegionId($var)
    {
        return $this->setData(self::PICKUP_REGION_ID, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setPickupCountry($var)
    {
        return $this->setData(self::PICKUP_COUNTRY, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setPickupPincode($var)
    {
        return $this->setData(self::PICKUP_PINCODE, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setBankAccountName($var)
    {
        return $this->setData(self::BANK_ACCOUNT_NAME, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setBankAccountNumber($var)
    {
        return $this->setData(self::BANK_ACCOUNT_NUMBER, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setBankName($var)
    {
        return $this->setData(self::BANK_NAME);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setBusinessName($var)
    {
        return $this->setData(self::BUSINESS_NAME, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setCategory($var)
    {
        return $this->setData(self::CATEGORY, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setIfsc($var)
    {
        return $this->setData(self::IFSC, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setOtherMarketPlaceUrl($var)
    {
        return $this->setData(self::OTHER_MARKETPLACE_PROFILE, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }
    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setStatus($var)
    {
        return $this->setData(self::STATUS, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setVacationFromDate($var)
    {
        return $this->setData(self::VACATION_FROM_DATE, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setVacationToDate($var)
    {
        return $this->setData(self::VACATION_TO_DATE, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setVacationMessage($var)
    {
        return $this->setData(self::VACATION_MESSAGE, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setLogo($var)
    {
        return $this->setData(self::LOGO, $var);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setCategoryIds($var)
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
        }
        return (array) $this->_getData('category_ids');
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getAppLink()
    {
        return $this->getData(self::APP_LINK);
    }
    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function setAppLink($appLink)
    {
        return $this->setData(self::APP_LINK, $appLink);
    }

    /**
     * Retrieve vendor reset password link expiration period in days
     *
     * @return int
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int) $this->_scopeConfig->getValue(
            self::XML_PATH_VENDOR_RESET_PASSWORD_LINK_EXPIRATION_PERIOD,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @return AbstractModel
     */
    public function beforeSave()
    {
        $this->createPasswordHash();
        return parent::beforeSave();
    }

    /**
     *
     */
    private function createPasswordHash()
    {
        if ($this->getData(self::PASSWORD) !== null) {
            $pass_hash = $this->_encryptor->getHash($this->getData(self::PASSWORD), true);
            $this->setData(self::PASSWORD_HASH, $pass_hash);
        }
    }

    /**
     * @param string|null $email
     * @return bool
     */
    public function isEmailExist($email)
    {
        return ($this->getResource()->getIdByEmail($email)) ? true : false;
    }

    /**
     * Authenticate vendor
     *
     * @param  string $login
     * @param  string $password
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * Use \Magedelight\Vendor\Api\AccountManagementInterface::authenticate
     */
    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
        $this->checkPasswordStrength($password);
        try {
            $vendor = $this->vendorRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        if ($vendor->getId() &&
            $hash = $this->vendorRegistry->retrieveSecureData($vendor->getId())->getPasswordHash()) {
            if (!$this->_encryptor->validateHash($password, $hash)) {
                throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
            }

            if ($vendor->getConfirmation() && $this->isConfirmationRequired($vendor)) {
                throw new EmailNotConfirmedException(__('This account is not confirmed.'));
            }

            switch ($vendor->getStatus()) {
                case VendorStatus::VENDOR_STATUS_CLOSED:
                    throw new AuthenticationException(__('Your account has been closed.'));
                case VendorStatus::VENDOR_STATUS_INACTIVE:
                    throw new AuthenticationException(__('Your account is inactive.'));
            }

            $vendorModel = $this->updateData($vendor);

            $this->eventManager->dispatch(
                'vendor_vendor_authenticated',
                ['model' => $vendorModel, 'password' => $password]
            );

            $this->eventManager->dispatch('vendor_data_object_login', ['vendor' => $vendor]);

            return $vendor;
        } else {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
    }

    /**
     * Make sure that password complies with minimum security requirements.
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength($password)
    {
        $length = $this->stringHelper->strlen($password);
        if ($length < self::MIN_PASSWORD_LENGTH) {
            throw new InputException(
                __(
                    'Please enter a password with at least %1 characters.',
                    self::MIN_PASSWORD_LENGTH
                )
            );
        }
        if ($this->stringHelper->strlen(trim($password)) != $length) {
            throw new InputException(__('The password can\'t begin or end with a space.'));
        }
    }

    /**
     * Login user
     *
     * @param string $username
     * @param string $password
     * @return  $this
     * @throws AuthenticationException
     * @throws EmailNotConfirmedException
     * @throws InvalidEmailOrPasswordException
     */
    public function login($username, $password)
    {
        $this->authenticate($username, $password);
        return $this;
    }

    /**
     * Reload current user
     *
     * @return $this
     */
    public function reload()
    {
        $userId = $this->getId();
        $this->setId(null);
        $this->load($userId);
        return $this;
    }

    /**
     * Check if user has available resources
     *
     * @return bool
     */
    public function hasAvailableResources()
    {
        return $this->_hasResources;
    }

    /**
     * Set user has available resources
     *
     * @param bool $hasResources
     * @return $this
     */
    public function setHasAvailableResources($hasResources)
    {
        $this->_hasResources = $hasResources;
        return $this;
    }

    /**
     * Load vendor by email
     *
     * @param string $vendorEmail
     * @return  $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByEmail($vendorEmail)
    {
        $this->_getResource()->loadByEmail($this, $vendorEmail);
        return $this;
    }

    /**
     * Change vendor password
     *
     * @param string $newPassword
     * @return  $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePassword($newPassword)
    {
        $this->_getResource()->changePassword($this, $newPassword);
        return $this;
    }

    /**
     * Update vendor data
     *
     * @param \Magedelight\Vendor\Api\Data\VendorInterface $vendor
     * @return $this
     */
    public function updateData($vendor)
    {
        $vendorId = $vendor->getId();
        if ($vendorId) {
            $this->setId($vendorId);
        }

        return $this;
    }

    /**
     * @param null $websiteId
     * @param bool $skipWebsiteFilter
     * @return array
     */
    public function getVendorOptions($websiteId = null, $skipWebsiteFilter = false)
    {
        $collection = $this->getCollection()
            ->_addWebsiteData($columns = ['business_name','status'], $websiteId, $skipWebsiteFilter);
        $collection->addFieldToFilter('rvwd.email_verified', 1);
        $collection->setOrder('rvwd.business_name', 'ASC');
        $options = [];
        foreach ($collection as $vendor) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $VendorStatus = $objectManager->create(\Magedelight\Vendor\Model\Source\Status::class);
            $status = $VendorStatus->getOptionText($vendor->getStatus());
            $label = $vendor->getBusinessName() . ' (' . $status . ')';
            /*$label .= ($vendor->getBusinessName()) ? ' - '. $vendor->getBusinessName() : '';*/
            $options[$vendor->getId()] = __($label);
        }
        return $options;
    }

    /**
     * @param string $optionId
     * @return mixed
     */
    public function getOptionText($optionId = '')
    {
        return $this->_vendorStatuses->getOptionText($optionId);
    }

    /**
     *
     * @return boolean Describes whether vendor is system or not
     * @return boolean
     */
    public function isSystemVendor()
    {
        return $this->getData("is_system") === 1;
    }

    /**
     *
     * @param $vendorId
     * @return bool : Get admin user
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAdminVendor($vendorId)
    {
        if ($this->vendorRepository->get(self::ADMIN_VENDOR_EMAIL)->getId() == $vendorId) {
            return true;
        }
        return false;
    }

    /**
     * @param $vendor
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkEmailExist($vendor)
    {
        return $this->_getResource()->checkEmailExist($vendor);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusText($statusMsg)
    {
        return $this->setData('status_text', $statusMsg);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusText()
    {
        return $this->getData('status_text');
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

    /**
     * {@inheritdoc}
     */
    public function addCategoryItem($categoryData)
    {
        $this->setCategoryItems(array_filter(array_merge([$this->getCategoryItems()], $categoryData)));
    }

    /**
     * Return vendor sub users
     *
     * @param $parentVendorId
     * @return array
     */
    public function getVendorAllChildUsers($parentVendorId)
    {
        return $this->getResource()->getVendorAllChildUsers($parentVendorId);
    }
}