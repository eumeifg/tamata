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

use Magedelight\Vendor\Api\Data\BankDataInterface;
use Magedelight\Vendor\Api\Data\BusinessDataInterface;
use Magedelight\Vendor\Api\Data\PersonalDataInterface;
use Magedelight\Vendor\Api\Data\ShippingDataInterface;
use Magedelight\Vendor\Api\Data\StatusInterface;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magedelight\Vendor\Model\Vendor as VendorModel;
use Magento\Framework\Exception\LocalizedException;

class ProfileValidation
{

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory
     */
    protected $customMessageInterface;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var VendorWebsiteFactory
     */
    protected $vendorWebsite;

    /**
     * @var VendorStatus
     */
    protected $vendorStatus;

    /**
     * @var \Magento\Directory\Model\Country\Postcode\Validator
     */
    protected $postCodeValidator;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    /**
     *
     * @param \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magedelight\Vendor\Model\VendorWebsiteFactory $vendorWebsite
     * @param VendorStatus $vendorStatus
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Directory\Model\Country\Postcode\Validator $postCodeValidator
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Vendor\Model\VendorWebsiteFactory $vendorWebsite,
        VendorStatus $vendorStatus,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Directory\Model\Country\Postcode\Validator $postCodeValidator,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->customMessageInterface = $customMessageInterface;
        $this->vendorHelper = $vendorHelper;
        $this->authSession = $authSession;
        $this->userContext = $userContext;
        $this->vendorFactory = $vendorFactory;
        $this->vendorWebsite = $vendorWebsite;
        $this->vendorStatus = $vendorStatus;
        $this->directoryHelper = $directoryHelper;
        $this->postCodeValidator = $postCodeValidator;
        $this->request = $request;
        $this->file = $file;
    }

    /**
     * Validate business details
     * @param BusinessDataInterface $vendor
     * @param $isAdmin
     * @param boolean $returnFirstError
     * @param bool $isVendorEdit
     * @param $vendorRepo
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function validateBusinessDetails(
        BusinessDataInterface $vendor,
        $isAdmin,
        $returnFirstError = false,
        $isVendorEdit = false,
        $vendorRepo
    ) {
        $errors = [];
        if (!$isAdmin) {
            $validateUser = $this->validateByContextOrAuth($vendor->getId());
            if (!$validateUser) {
                throw new LocalizedException(__('Vendor is Invalid'));
            }
        }

        if (empty($vendorRepo->getStatus()) || (!empty($vendorRepo->getStatus()) &&
                !in_array(
                    $vendorRepo->getStatus(),
                    [VendorStatus::VENDOR_STATUS_ACTIVE, VendorStatus::VENDOR_STATUS_VACATION_MODE]
                ))) {
            if (!\Zend_Validate::is($vendor->getBusinessName(), 'NotEmpty')) {
                $errors[] = $this->customMessage('Business Name is required. Enter and try again.');
            } else {
                if (!$isVendorEdit) {
                    if (!\Zend_Validate::is(
                        $vendor->getBusinessName(),
                        'Regex',
                        ['pattern' => '/^.{1,150}$/']
                    )) {
                        $errors[] = $this->customMessage('Character limit is 150 for Business Name.');
                    }
                    $collection = $this->vendorFactory->create()->getCollection()->addFieldToSelect('vendor_id');
                    if ($this->authSession->isLoggedIn()) {
                        $collection->addFieldToFilter(
                            'vendor_id',
                            ['neq' => $this->authSession->getUser()->getVendorId()]
                        );
                    }

                    if ($vendor->getRbhash()) {
                        $collection->addFieldToFilter('email_verification_code', ['neq' => $vendor->getRbhash()]);
                    }

                    $collection->getSelect()->join(
                        ['rvwd' => 'md_vendor_website_data'],
                        "rvwd.vendor_id = main_table.vendor_id AND rvwd.website_id = "
                        . $this->storeManager->getStore()->getWebsiteId() . " AND business_name = '"
                        . $vendor->getBusinessName() . "'",
                        ['business_name']
                    );

                    if ($collection->getFirstItem()->getId()) {
                        $errors[] = $this->customMessage(
                            'This Business Name already exists. Please try another.'
                        );
                    }
                } else {
                    if (!\Zend_Validate::is(
                        $vendor->getBusinessName(),
                        'Regex',
                        ['pattern' => '/^.{1,150}$/']
                    )) {
                        $errors[] = $this->customMessage('Character limit is 150 for Business Name.');
                    }
                }
            }

            if (!$this->vendorHelper->isRemoved('vat', 'business')) {
                if (!\Zend_Validate::is($vendor->getVat(), 'NotEmpty')) {
                    $errors[] = $this->customMessage('VAT Number is required. Enter and try again.');
                } else {
                    if (!\Zend_Validate::is(
                        $vendor->getVat(),
                        'Regex',
                        ['pattern' => '/(^[a-zA-Z0-9]{10,20}$)/']
                    )) {
                        $msg = 'Allow only alpha numeric value without space, not allowed special ';
                        $msg .= 'character and minimum length 10 and maximum length 20 for VAT Number.';
                        $errors[] = $this->customMessage($msg);
                    }
                }
            }

            if (!$this->vendorHelper->isRemoved('vat_doc', 'business')) {
                $vendorFiles = $this->request->getFiles('vendor');
                $fileId = $vendorFiles['vat_doc'];

                if (!\Zend_Validate::is($fileId['name'], 'NotEmpty') && empty($vendor->getVatDoc())) {
                    $errors[] = $this->customMessage('VAT Document is required. Enter and try again.');
                } else {
                    $fileTypes = ['jpg','jpeg','png'];
                    if (array_key_exists('vat_doc', $vendorFiles) && $fileId['name']) {
                        $path_parts = $this->file->getPathInfo($fileId['name']);
                        if (isset($path_parts['extension'])) {
                            $extension = $path_parts['extension'];
                            if (!in_array($extension, $fileTypes)) {
                                $errors[] = $this->customMessage(
                                    'Please use following file types (JPG,JPEG,PNG) only for VAT Document.'
                                );
                            }
                        } else {
                            $errors[] = $this->customMessage(
                                'Please use following file types (JPG,JPEG,PNG) only for VAT Document.'
                            );
                        }
                    }
                    if ($fileId['size'] > VendorModel::DEFAULT_IMAGE_SIZE) {
                        $errors[] = $this->customMessage(
                            'File size should be less than mentioned size for VAT Document.'
                        );
                    }
                }
            }
        }

        if (!empty($vendor->getOtherMarketplaceProfile())) {
            if (!\Zend_Validate::is($vendor->getOtherMarketplaceProfile(), 'Regex', ['pattern' => '/^(?:(?:https?|ftp):\/\/|www\.)[-A-Za-z0-9+&@#\/%?=~_|!:,.;]*[-A-Za-z0-9+&@#\/%=~_|]{0,150}$/'])) {
                $errors[] = $this->customMessage(
                    'Please enter valid site URL only and character limit is 150 for Other Marketplace URL.'
                );
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     * process vacation mode
     * @param StatusInterface $vendor
     * @param $statusRequestType
     * @param $isAdmin
     * @return array
     * @throws LocalizedException
     */
    public function validateStatus(StatusInterface $vendor, $statusRequestType, $isAdmin)
    {
        if (!$isAdmin) {
            $validateUser = $this->validateByContextOrAuth($vendor->getId());
            if (!$validateUser) {
                throw new LocalizedException(__('Vendor is Invalid'));
            }
        }

        $requestValue = $this->vendorStatus->getOptionText($statusRequestType);

        if ($requestValue === null) {
            throw new LocalizedException(__('Vendor Status does not exists'));
        }

        $currentDate = date('m/d/Y');
        $fromDate = $vendor->getVacationFromDate();
        $toDate = $vendor->getVacationToDate();
        $message = $vendor->getVacationMessage();
        $error =  [];

        try {
            if ($this->isItValidDate($fromDate) == true &&
                $this->isItValidDate($toDate) == true &&
                (in_array($statusRequestType, [1, 4]))) {
                if (($fromDate > $currentDate || $fromDate == $currentDate) &&
                    ($toDate >= $fromDate || $toDate == $currentDate)) {
                    $error =  [];
                } else {
                    $error[] = $this->customMessage('Date Invalid. Please try again!');
                }
            } else {
                $error[] = $this->customMessage('Date Invalid. Please try again!');
            }
        } catch (\Exception $e) {
            $error[] = $this->customMessage('Unable to submit status change request.');
        }

        return $error;
    }

    /**
     * Validate vendor personal info
     * @param PersonalDataInterface $vendor
     * @param bool $isAdmin
     * @param bool $returnFirstError
     * @param bool $isVendorEdit
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function validatePersonalInfo(
        PersonalDataInterface $vendor,
        $isAdmin,
        $returnFirstError = false,
        $isVendorEdit = false
    ) {
        $msg = '';
        if (!$isAdmin) {
            $validateUser = $this->validateByContextOrAuth($vendor->getId());
            if (!$validateUser) {
                throw new LocalizedException(__('Vendor is Invalid'));
            }
        }

        $errors = [];
        if (empty($vendor->getData())) {
            $errors[] = $this->customMessage('Please fill up all details.');
        }

        if ($vendor->getEmail() && !$isVendorEdit) {
            if ($this->verifyEmail($vendor->getEmail())) {
                $errors[] = $this->customMessage('Email is already in use, Please use another one.');
            }
        }

        if (!\Zend_Validate::is($vendor->getName(), 'NotEmpty')) {
            $errors[] = $this->customMessage('Name is required. Enter and try again.');
        } else {
            if (!\Zend_Validate::is($vendor->getName(), 'Regex', ['pattern' => '/^[a-zA-Z ]{1,150}$/'])) {
                $msg = 'Allow alphabatic and space value, character limit 150, not allowed special character for Name.';
                $errors[] = $this->customMessage($msg);
            }
        }

        if (!$isVendorEdit) {
            if (!\Zend_Validate::is($vendor->getPassword(), 'NotEmpty')) {
                $errors[] = $this->customMessage('Password is required. Enter and try again.');
            } elseif (!\Zend_Validate::is(
                $vendor->getPassword(),
                'Regex',
                ['pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=]).{6,30}$/']
            )) {
                $msg = 'Password must be at least 6 characters and no more than 30 characters, also it must ';
                $msg .= 'include alphanumeric lower and upper case letters with at least one special character.';
                $errors[] = $this->customMessage($msg);
            } elseif (!\Zend_Validate::is($vendor->getCpassword(), 'NotEmpty')) {
                $errors[] = $this->customMessage('Confirm password is required. Enter and try again.');
            } else {
                if ($vendor->getPassword() != $vendor->getCpassword()) {
                    $errors[] = $this->customMessage(
                        'Password and the confirm password field does not the have same value.'
                    );
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('address1', 'personal')) {
            /* Using $vendor->getData('address1') instead of
             $vendor->getAddress1() as it is returning null due to number suffix. */
            if (!\Zend_Validate::is($vendor->getData('address1'), 'NotEmpty')) {
                $errors[] = $this->customMessage('Address Line 1 is required. Enter and try again.');
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getData('address1'),
                    'Regex',
                    ['pattern' => '/^.{1,150}$/']
                )) {
                    $errors[] = $this->customMessage(
                        'Please enter less than or equal to 150 characters in Address Line 1.'
                    );
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('address2', 'personal')) {
            /* Using $vendor->getData('address2') instead of $vendor->getAddress2()
             as it is returning null due to number suffix. */
            if (!empty($vendor->getData('address2'))) {
                if (!\Zend_Validate::is(
                    $vendor->getData('address2'),
                    'Regex',
                    ['pattern' => '/^.{1,150}$/']
                )) {
                    $errors[] = $this->customMessage(
                        'Please enter less than or equal to 150 characters in Address Line 2.'
                    );
                }
            }
        }

        if (!\Zend_Validate::is($vendor->getCountryId(), 'NotEmpty')) {
            if (!$this->vendorHelper->isRemoved('country_id', 'personal')) {
                $errors[] = $this->customMessage('Country is required. Enter and try again.');
            }
        } else {
            if (!$this->vendorHelper->isRemoved('region', 'personal')) {
                if ($this->directoryHelper->isRegionRequired($vendor->getCountryId())) {
                    $regionExists = true;
                    if (empty($vendor->getRegionId())) {
                        $regionExists = false;
                    }
                    if (!$regionExists && empty($vendor->getRegion())) {
                        $regionExists = false;
                    } else {
                        $regionExists = true;
                    }

                    if (!$regionExists) {
                        $errors[] = $this->customMessage(
                            'Please select states if available or enter state name'
                        );
                    }
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('city', 'personal')) {
            if (!\Zend_Validate::is($vendor->getCity(), 'NotEmpty')) {
                $errors[] = $this->customMessage('City is required. Enter and try again.');
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getCity(),
                    'Regex',
                    ['pattern' => '/^[a-zA-Z ]{1,50}$/']
                )) {
                    $msg = 'Please use less than 50 characters in the City ';
                    $msg .= 'field, only alphabets and space are allowed.';
                    $errors[] = $this->customMessage($msg);
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('pincode', 'personal')) {
            if (!\Zend_Validate::is($vendor->getPincode(), 'NotEmpty')) {
                $errors[] = $this->customMessage('Pincode is required. Enter and try again.');
            } else {
                $postCodeValidateStatus = $this->postCodeValidator->validate(
                    $vendor->getPincode(),
                    $vendor->getCountryId()
                );
                if (!$postCodeValidateStatus) {
                    $errors[] = $this->customMessage('Invalid Post Code');
                }
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     * Validate vendor Shipping info
     * @param ShippingDataInterface $vendor
     * @param bool $isAdmin
     * @param bool $returnFirstError
     * @param bool $isVendorEdit
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function validateShippingInfo(
        ShippingDataInterface $vendor,
        $isAdmin,
        $returnFirstError = false,
        $isVendorEdit = false
    ) {
        $msg = '';
        if (!$isAdmin) {
            $validateUser = $this->validateByContextOrAuth($vendor->getId());
            if (!$validateUser) {
                throw new LocalizedException(__('Vendor is Invalid'));
            }
        }

        $errors = [];
        if (empty($vendor->getData())) {
            $errors[] = $this->customMessage('Please fill up all details.');
        }

        if (!$this->vendorHelper->isRemoved('pickup_address1', 'shipping')) {
            /* Using $vendor->getData('pickup_address1') instead of $vendor->getAddress1()
             as it is returning null due to number suffix. */
            if (!\Zend_Validate::is($vendor->getData('pickup_address1'), 'NotEmpty')) {
                $errors[] = $this->customMessage(
                    'Address line 1 of Pickup and Shipping Information is required. Enter and try again.'
                );
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getData('pickup_address1'),
                    'Regex',
                    ['pattern' => '/^.{1,150}$/']
                )) {
                    $errors[] = $this->customMessage(
                        'Character limit is 150 for Address line 1 of Pickup and Shipping Information.'
                    );
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('pickup_address2', 'shipping')) {
            /* Using $vendor->getData('pickup_address2') instead of $vendor->getAddress2()
             as it is returning null due to number suffix. */
            if (!empty($vendor->getData('pickup_address2'))) {
                if (!\Zend_Validate::is(
                    $vendor->getData('pickup_address2'),
                    'Regex',
                    ['pattern' => '/^.{1,150}$/']
                )) {
                    $errors[] = $this->customMessage(
                        'Character limit is 150 for Address line 2 of Pickup and Shipping Information.'
                    );
                }
            }
        }

        if (!\Zend_Validate::is($vendor->getPickupCountryId(), 'NotEmpty')) {
            if (!$this->vendorHelper->isRemoved('pickup_country_id', 'shipping')) {
                $errors[] = $this->customMessage(
                    'Country of Pickup and Shipping Information is required. Enter and try again.',
                    false
                );
            }
        } else {
            if (!$this->vendorHelper->isRemoved('pickup_region', 'shipping')) {
                if ($this->directoryHelper->isRegionRequired($vendor->getPickupCountryId())) {
                    $pickupRegionExists = true;
                    if (empty($vendor->getPickupRegionId())) {
                        $pickupRegionExists = false;
                    }
                    if (!$pickupRegionExists && empty($vendor->getPickupRegion())) {
                        $pickupRegionExists = false;
                    } else {
                        $pickupRegionExists = true;
                    }

                    if (!$pickupRegionExists) {
                        $errors[] = $this->customMessage(
                            'Region of Pickup and Shipping Information is required. Enter and try again.'
                        );
                    }
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('pickup_city', 'shipping')) {
            if (!\Zend_Validate::is($vendor->getPickupCity(), 'NotEmpty')) {
                $errors[] = $this->customMessage(
                    'City of Pickup and Shipping Information is required. Enter and try again.'
                );
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getPickupCity(),
                    'Regex',
                    ['pattern' => '/^[a-zA-Z ]{1,50}$/']
                )) {
                    $msg = 'Please use less than 50 characters in the City of Pickup and ';
                    $msg .= 'Shipping Information field, only alphabets and space are allowed.';
                    $errors[] = $this->customMessage($msg);
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('pickup_pincode', 'shipping')) {
            if (!\Zend_Validate::is($vendor->getPickupPincode(), 'NotEmpty')) {
                $errors[] = $this->customMessage(
                    'Pincode of Pickup and Shipping Information is required. Enter and try again.'
                );
            } else {
                $postCodeValidateStatus = $this->postCodeValidator->validate(
                    $vendor->getPickupPincode(),
                    $vendor->getPickupCountryId()
                );
                if (!$postCodeValidateStatus) {
                    $errors[] = $this->customMessage('Invalid Pickup and Shipping Post Code');
                }
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     * Validate vendor Banking info
     * @param BankDataInterface $vendor
     * @param bool $isAdmin
     * @param bool $returnFirstError
     * @param bool $isVendorEdit
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function validateBankInfo(
        BankDataInterface $vendor,
        $isAdmin,
        $returnFirstError = false,
        $isVendorEdit = false
    ) {
        $msg = '';
        if (!$isAdmin) {
            $validateUser = $this->validateByContextOrAuth($vendor->getId());
            if (!$validateUser) {
                throw new LocalizedException(__('Vendor is Invalid'));
            }
        }

        $errors = [];
        if (empty($vendor->getData())) {
            $errors[] = $this->customMessage('Please fill up all details.');
        }

        if (!$this->vendorHelper->getConfigValue(VendorModel::IS_ENABLED_BANKING_DETAILS_XML_PATH)) {
            return $errors;
        }

        $isBankInfoOptional = $this->vendorHelper->getConfigValue(VendorModel::IS_BANK_DETAILS_OPTIONAL_XML_PATH);

        if (!\Zend_Validate::is($vendor->getBankAccountName(), 'NotEmpty')) {
            if (!$isBankInfoOptional) {
                $errors[] = $this->customMessage('Bank Account Name is required. Enter and try again.');
            }
        } else {
            if (!\Zend_Validate::is(
                $vendor->getBankAccountName(),
                'Regex',
                ['pattern' => '/^.{1,150}$/']
            )) {
                $errors[] = $this->customMessage('Character limit is 150 for Bank Account Name.');
            }
        }

        if (!\Zend_Validate::is($vendor->getBankAccountNumber(), 'NotEmpty')) {
            if (!$isBankInfoOptional) {
                $errors[] = $this->customMessage('Bank Account Number is required. Enter and try again.');
            }
        } else {
            if (!\Zend_Validate::is(
                $vendor->getBankAccountNumber(),
                'Regex',
                ['pattern' => '/(^\d{10,20}$)/']
            )) {
                $msg = 'Only allow numeric value, not allowed special character and space, ';
                $msg .= 'character limit  min. input 10 and max 20 for Bank Account Number.';
                $errors[] = $this->customMessage($msg);
            }
        }

        if (!$isAdmin) {
            /* Skip validating in admin area. */
            if (!\Zend_Validate::is($vendor->getConfirmAccountNumber(), 'NotEmpty')) {
                if (!$isBankInfoOptional) {
                    $errors[] = $this->customMessage(
                        'Confirm Bank Account Number is required. Enter and try again.'
                    );
                }
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getConfirmAccountNumber(),
                    'Regex',
                    ['pattern' => '/(^\d{10,20}$)/']
                )) {
                    $msg = 'Only allow numeric value, not allowed special character and space, ';
                    $msg .= 'character limit  min. input 10 and max 20 for Bank Account Number.';
                    $errors[] = $this->customMessage($msg);
                } elseif ($vendor->getBankAccountNumber() != $vendor->getConfirmAccountNumber()) {
                    $errors[] = $this->customMessage(
                        'Account Number and the Re-type Account Number field does not the have same value.'
                    );
                }
            }
        }
        if (!\Zend_Validate::is($vendor->getBankName(), 'NotEmpty')) {
            if (!$isBankInfoOptional) {
                $errors[] = $this->customMessage('Bank Name is required. Enter and try again.');
            }
        } else {
            if (!\Zend_Validate::is($vendor->getBankName(), 'Regex', ['pattern' => '/^.{1,150}$/'])) {
                $errors[] = $this->customMessage('Character limit is 150 for Bank Name.');
            }
        }

        if (!\Zend_Validate::is($vendor->getIfsc(), 'NotEmpty')) {
            if (!$isBankInfoOptional) {
                $errors[] = $this->customMessage('IFSC code is required. Enter and try again.');
            }
        } else {
            if (!\Zend_Validate::is(
                $vendor->getIfsc(),
                'Regex',
                ['pattern' => '/^[A-Za-z]{4}[0-9]{7}$/']
            )) {
                $errors[] = $this->customMessage('IFSC code is Invalid');
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     * Validates whether session vendor and data passed for vendor are same
     * @return bool
     */
    public function validateByContextOrAuth($vendorId)
    {
        if ($this->authSession->getUser() == null) {
            $sessionVendorId = $this->userContext->getUserId();
        } else {
            $sessionVendorId = $this->authSession->getUser()->getVendorId();
        }
        if ($vendorId == $sessionVendorId) {
            return true;
        } else {
            return false;
        }
    }

    public function isItValidDate($date)
    {
        if (preg_match("/^(\d{2})\/(\d{2})\/(\d{4})$/", $date, $matches)) {
            if (checkdate($matches[1], $matches[2], $matches[3])) {
                return true;
            }
        }
    }

    protected function customMessage($errorText)
    {
        $customMsg = $this->customMessageInterface->create();
        $customMsg->setMessage(__($errorText));
        $customMsg->setStatus(false);

        return $customMsg;/*->getData();*/
    }

    /**
     * check if email exists
     * @param string $email
     * @return boolean
     */
    protected function verifyEmail($email)
    {
        $collection = $this->vendorFactory->create()->getCollection();
        $collection->_addWebsiteData(['email_verified']);
        $collection->addFieldToFilter('email', ['eq' => $email]);
        $collection->addFieldToFilter('email_verified', ['eq' => 1]);
        if ($collection->count()) {
            return true;
        }
        return false;
    }

    public function validateLogo($file, $returnFirstError = false)
    {
        $errors = [];
        $_filesParam = $this->request->getFiles()->toArray();
        if (!array_key_exists('logo', $_filesParam) && empty($file['name'])) {
            $errors[] = $this->customMessage('Company\'s Logo is required. Enter and try again.');
        } else {
            $fileTypes = ['jpg','jpeg','png'];
            if (!empty($file['name'])) {
                $path_parts = $this->file->getPathInfo($file['name']);
                if (isset($path_parts['extension'])) {
                    $extension = $path_parts['extension'];
                    if (!in_array($extension, $fileTypes)) {
                        $errors[] = $this->customMessage(
                            'Please use following file types (JPG,JPEG,PNG) only for Company\'s Logo.'
                        );
                    }
                } else {
                    $errors[] = $this->customMessage(
                        'Please use following file types (JPG,JPEG,PNG) only for Company\'s Logo.'
                    );
                }
            }
            if ($file['size'] > VendorModel::DEFAULT_IMAGE_SIZE) {
                $errors[] = $this->customMessage(
                    'File size should be less than mentioned size for Company\'s Logo.'
                );
            }
        }
        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }
}
