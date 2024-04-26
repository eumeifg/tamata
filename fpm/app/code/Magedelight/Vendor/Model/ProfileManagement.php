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
use Magedelight\Vendor\Api\ProfileManagementInterface;
use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magedelight\Vendor\Model\Source\RequestStatuses;
use Magedelight\Vendor\Model\Source\RequestTypes;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magedelight\Vendor\Model\Vendor\Image as ImageModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

class ProfileManagement implements ProfileManagementInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface
     */
    protected $vendorWebsiteRepository;

    /**
     * @var \Magedelight\Vendor\Model\VendorWebsiteFactory
     */
    protected $vendorWebsiteFactory;

    /**
     * uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var ProfileValidation
     */
    protected $profileValidator;

    /**
     * @var Request
     */
    protected $requestStatus;

    /**
     * @var \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory
     */
    protected $customMessageInterface;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var ImageModel
     */
    protected $imageModel;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Directory\Model\Region
     */
    protected $_region;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     *
     * @param StoreManagerInterface $storeManager
     * @param \Magedelight\Vendor\Model\ProfileValidation $profileValidator
     * @param \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository
     * @param VendorRepositoryInterface $vendorRepository
     * @param \Magedelight\Vendor\Model\Request $requestStatus
     * @param \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param UploaderFactory $uploaderFactory
     * @param ImageModel $imageModel
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Directory\Model\Region $region
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Model\ProfileValidation $profileValidator,
        \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository,
        VendorRepositoryInterface $vendorRepository,
        \Magedelight\Vendor\Model\Request $requestStatus,
        \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        UploaderFactory $uploaderFactory,
        ImageModel $imageModel,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Directory\Model\Region $region,
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->storeManager = $storeManager;
        $this->profileValidator = $profileValidator;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        $this->vendorRepository = $vendorRepository;
        $this->requestStatus = $requestStatus;
        $this->customMessageInterface = $customMessageInterface;
        $this->vendorHelper = $vendorHelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->imageModel = $imageModel;
        $this->request = $request;
        $this->_region = $region;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function vendorStatusChangeRequest(
        \Magedelight\Vendor\Api\Data\StatusInterface $vendor,
        $statusRequestType,
        $isAdmin = false
    ) {
        try {
            if ($vendor->getStoreId()) {
                $store = $this->storeManager->getStore($vendor->getStoreId());
            } else {
                $store = $this->storeManager->getStore();
            }
            $statusFlag = true;
            if ($statusRequestType != RequestTypes::VENDOR_REQUEST_TYPE_CLOSE) {
                $validStatus = $this->profileValidator->validateStatus($vendor, $statusRequestType, $isAdmin);
                if (!empty($validStatus)) {
                    $statusFlag = false;
                    return $validStatus;
                }
            }

            if ($statusRequestType == RequestTypes::VENDOR_REQUEST_TYPE_CLOSE || $statusFlag) {
                $vendorWebsiteData = $this->vendorWebsiteRepository->getVendorWebsiteData(
                    $vendor->getId(),
                    $store->getWebsiteId()
                );
                $savedWebsiteStatus = $this->saveDataInWebsite(
                    $vendorWebsiteData,
                    $vendor,
                    $statusRequestType,
                    'statusupdate'
                );
                if ($savedWebsiteStatus) {
                    $this->vendorRepository->cleanCache();
                    $vendor = $this->vendorRepository->getById($vendorWebsiteData->getVendorId());
                    $this->requestStatus->saveVendorStatusRequest($vendor, $statusRequestType);
                    $customMessage = $this->customMessageInterface->create();
                    $customMessage->setMessage(__("Your status change request has been submitted successfully."));
                    $customMessage->setStatus(true);
                    $customMsg[] = $customMessage->getData();
                } else {
                    $customMessage = $this->customMessageInterface->create();
                    $customMessage->setMessage(__("Unable to submit status change request."));
                    $customMessage->setStatus(false);
                    $customMsg[] = $customMessage->getData();
                }
            } else {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Unable to submit status change request."));
                $customMessage->setStatus(false);
                $customMsg[] = $customMessage->getData();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $customMsg;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function vendorShippingInfoUpdate(
        ShippingDataInterface $vendor,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    ) {
        $validationErrors = $this->profileValidator->validateShippingInfo(
            $vendor,
            $isAdmin,
            $returnFirstError,
            $isVendorEdit
        );
        if (!empty($validationErrors)) {
            return $validationErrors;
        }

        try {
            $store = $this->storeManager->getStore();
            /* Fetch region name - starts here  */
            $regionName = $this->getRegionName($vendor->getPickupRegionId());
            $vendor->setRegion($regionName);
            /* Fetch region name - ends here */
            $vendorWebsiteData = $this->vendorWebsiteRepository->getVendorWebsiteData(
                $vendor->getId(),
                $store->getWebsiteId()
            );
            $savePersonalData = $this->saveDataInWebsite($vendorWebsiteData, $vendor, null, null);
            if ($savePersonalData) {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Your information has been saved successfully."));
                $customMessage->setStatus(true);
                $customMsg[] = $customMessage->getData();
            } else {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Something went wrong while saving data, please try again."));
                $customMessage->setStatus(false);
                $customMsg[] = $customMessage->getData();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $customMsg;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function vendorBankingInfoUpdate(
        BankDataInterface $vendor,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    ) {
        $vendorRepo = $this->vendorRepository->getById($vendor->getId());
        $vendorAllowedStatus = [VendorStatus::VENDOR_STATUS_PENDING,VendorStatus::VENDOR_STATUS_DISAPPROVED];

        if (!$isAdmin && !in_array($vendorRepo->getStatus(), $vendorAllowedStatus)) {
            throw new LocalizedException(__('As per your profile status, You are not allowed to update the bank info'));
        }

        $validationErrors = $this->profileValidator->validateBankInfo(
            $vendor,
            $isAdmin,
            $returnFirstError,
            $isVendorEdit
        );

        if (!empty($validationErrors)) {
            return $validationErrors;
        }

        try {
            $store = $this->storeManager->getStore();
            $vendorWebsiteData = $this->vendorWebsiteRepository->getVendorWebsiteData(
                $vendor->getId(),
                $store->getWebsiteId()
            );
            $savePersonalData = $this->saveDataInWebsite($vendorWebsiteData, $vendor, null, null);
            if ($savePersonalData) {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Your information has been saved successfully."));
                $customMessage->setStatus(true);
                $customMsg[] = $customMessage->getData();
            } else {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Something went wrong while saving data, please try again."));
                $customMessage->setStatus(false);
                $customMsg[] = $customMessage->getData();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $customMsg;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function vendorPersonalInfoUpdate(
        PersonalDataInterface $vendor,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    ) {
        $validationErrors = $this->profileValidator->validatePersonalInfo(
            $vendor,
            $isAdmin,
            $returnFirstError,
            $isVendorEdit
        );
        if (!empty($validationErrors)) {
            return $validationErrors;
        }

        try {
            $store = $this->storeManager->getStore();
            /* Fetch region name - starts here  */
            $regionName = $this->getRegionName($vendor->getRegionId());
            $vendor->setRegion($regionName);
            /* Fetch region name - ends here */
            $vendorWebsiteData = $this->vendorWebsiteRepository->getVendorWebsiteData(
                $vendor->getId(),
                $store->getWebsiteId()
            );
            $savePersonalData = $this->saveDataInWebsite($vendorWebsiteData, $vendor, null, null);
            if ($savePersonalData) {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Your information has been saved successfully."));
                $customMessage->setStatus(true);
                $customMsg[] = $customMessage->getData();
            } else {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Something went wrong while saving data, please try again."));
                $customMessage->setStatus(false);
                $customMsg[] = $customMessage->getData();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $customMsg;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function vendorBusinessInfoUpdate(
        BusinessDataInterface $vendor,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    ) {
        $vendorRepo = $this->vendorRepository->getById($vendor->getId());
        $validationErrors = $this->profileValidator->validateBusinessDetails(
            $vendor,
            $isAdmin,
            false,
            $isVendorEdit,
            $vendorRepo
        );

        if (!empty($validationErrors)) {
            return $validationErrors;
        }
        try {
            $store = $this->storeManager->getStore();
            /* Save Other Marketplace Profile if changed - starts here */
            if ($vendorRepo->getOtherMarketPlaceUrl() != $vendor->getOtherMarketplaceProfile()) {
                $vendorRepo->setData('other_marketplace_profile', $vendor->getOtherMarketplaceProfile());
                $vendorRepo->save($vendorRepo);
            }
            /* Save Other Marketplace Profile if changed - ends here */
            $vendorWebsiteData = $this->vendorWebsiteRepository->getVendorWebsiteData(
                $vendor->getId(),
                $store->getWebsiteId()
            );

            if (!($vendorWebsiteData->getVatDoc() === null)) {
                /* Remove existing file before uploading new one. */
                $this->vendorHelper->deleteFile($vendorWebsiteData->getVatDoc(), 'vendor/vat_doc');
                /* Remove existing file before uploading new one. */
            }
            $vendorFiles = $this->request->getFiles('vendor');
            $fileId = $vendorFiles['vat_doc'];

            $vat_doc = ($fileId) ? $this->uploadFile(
                $fileId,
                $this->imageModel->getBaseDir('vendor/vat_doc')
            ) : null;
            $vendor->setVatDoc($vat_doc);
            $saveBusinessData = $this->saveDataInWebsite($vendorWebsiteData, $vendor, null, null);

            if ($saveBusinessData) {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Your information has been saved successfully."));
                $customMessage->setStatus(true);
                $customMsg[] = $customMessage->getData();
            } else {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Something went wrong while saving data, please try again."));
                $customMessage->setStatus(false);
                $customMsg[] = $customMessage->getData();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $customMsg;
    }

    /**
     * @param $fileId
     * @param $destinationFolder
     * @return mixed
     * @throws LocalizedException
     */
    protected function uploadFile($fileId, $destinationFolder)
    {
        try {
            $allowedFileTypes = ['jpg', 'jpeg', 'png'];
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions($allowedFileTypes);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($destinationFolder);
            return $result['file'];
        } catch (\Exception $ex) {
            throw new LocalizedException(__('File was not uploaded please try again'));
        }
    }

    /**
     *
     * {@inheritdoc}
     */
    public function vendorLogoUpdate(
        $vendorId = null,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    ) {
        if ($isAdmin && $vendorId == null) {
            throw new LocalizedException(__('Vendor Id is required'));
        }

        $sessionVendorId = $this->userContext->getUserId();
        $vendorLogo = $this->request->getFiles('logo');

        $validationErrors = $this->profileValidator->validateLogo($vendorLogo, $returnFirstError);

        if (!empty($validationErrors)) {
            return $validationErrors;
        }
        try {
            $store = $this->storeManager->getStore();

            $vendor = $this->vendorRepository->getById($sessionVendorId);
            if (!($vendor->getLogo() === null)) {
                $this->vendorHelper->deleteFile($vendor->getLogo(), 'vendor/logo');
            }
            $vLogo = $this->uploadFile('logo', $this->imageModel->getBaseDir('vendor/logo'));
            $vendor->setLogo($vLogo);
            $vendorWebsiteData = $this->vendorWebsiteRepository->getVendorWebsiteData(
                $vendor->getId(),
                $store->getWebsiteId()
            );
            $savelogoData = $this->saveDataInWebsite($vendorWebsiteData, $vendor, null, null);
            if ($savelogoData) {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Your Logo has been saved successfully."));
                $customMessage->setStatus(true);
                $customMsg[] = $customMessage->getData();
            } else {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__("Something went wrong while saving data, please try again."));
                $customMessage->setStatus(false);
                $customMsg[] = $customMessage->getData();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $customMsg;
    }

    /**
     * @param $regionId
     * @return string
     * @throws LocalizedException
     */
    protected function getRegionName($regionId)
    {
        try {
            $region   = $this->_region->load($regionId);
            $regionName = !($region->getName() === null) ? $region->getName() : $region->getDefaultName();
            return $regionName;
        } catch (\Exception $ex) {
            throw new LocalizedException(__('Region Does not exists'));
        }
    }

    /**
     * @param $vendorWebsite
     * @param $vendor
     * @param null $statusRequestType
     * @param null $type
     * @return bool
     */
    protected function saveDataInWebsite($vendorWebsite, $vendor, $statusRequestType = null, $type = null)
    {
        try {
            if ($vendorWebsite->getVendorId()) {
                foreach ($vendor->getData() as $key => $vendorData) {
                    if ($statusRequestType == RequestTypes::VENDOR_REQUEST_TYPE_VACATION) {
                        $vendorWebsite->setData('vacation_from_date', $vendor->getVacationFromDate());
                        $vendorWebsite->setData('vacation_to_date', $vendor->getVacationToDate());
                    }
                    if ($type === 'statusupdate') {
                        $vendorWebsite->setData(
                            'vacation_request_status',
                            RequestStatuses::VENDOR_REQUEST_STATUS_PENDING
                        );
                    }
                    $vendorWebsite->setData($key, $vendorData);
                }

                $this->vendorWebsiteRepository->save($vendorWebsite);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
        return true;
    }
}
