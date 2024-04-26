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

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magedelight\Vendor\Api\Data\AddressInterface;
use Magedelight\Vendor\Api\Data\VendorInterface;
use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magedelight\Vendor\Helper\View as VendorViewHelper;
use Magedelight\Vendor\Model\Config\Share as ConfigShare;
use Magedelight\Vendor\Model\Source\RequestStatuses;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magedelight\Vendor\Model\Vendor as VendorModel;
use Magedelight\Vendor\Model\Vendor\Image as ImageModel;
use Magento\Catalog\Model\Product\Type;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;

/**
 * Handle various vendor account actions
 * @author Rocket Bazaar Core Team
 * Created at 13 Feb, 2016 2:50:31 PM
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AccountManagement implements AccountManagementInterface
{

    /**
     * Configuration paths for email templates and identities
     */
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'vendor/create_account/email_template';

    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'vendor/create_account/email_no_password_template';

    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'vendor/create_account/email_identity';

    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'vendor/password/remind_email_template';

    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'vendor/password/forgot_email_template';

    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'vendor/password/forgot_email_identity';

    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'vendor/password/reset_password_template';

    const XML_PATH_IS_CONFIRM = 'vendor/create_account/confirm';

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'vendor/create_account/email_confirmation_template';

    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'vendor/create_account/email_confirmed_template';

    const XML_PATH_NOTIFICATION_EMAIL_IDENTITY = 'vendor/status_request/email_identity';

    const XML_PATH_NOTIFICATION_EMAIL_RECIPIENT = 'vendor/status_request/email_recipient';

    const XML_PATH_STATUS_NOTIFICATION_EMAIL_TEMPLATE = 'vendor/status_request/email_notification_template';

    const XML_PATH_STATUS_NOTIFICATION_REJECT_EMAIL_TEMPLATE = 'vendor/status_request/email_notification_reject_template';

    const XML_PATH_STATUS_NOTIFICATION_EMAIL_ADMIN_TEMPLATE = 'vendor/status_request/email_notification_admin_template';

    const XML_PATH_CONTACT_ADMIN_EMAIL_IDENTITY = 'vendor/contact_admin/email_identity';

    const XML_PATH_CONTACT_ADMIN_EMAIL_RECIPIENT = 'vendor/contact_admin/email_recipient';

    const XML_PATH_CONTACT_ADMIN_EMAIL_TEMPLATE = 'vendor/contact_admin/email_template';

    /*Constants for the type of new account email to be sent*/
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /* welcome email, when password setting is required*/
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /* welcome email, when confirmation is enabled*/
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /* confirmation email, when account is confirmed*/
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    const EMAIL_REMINDER = 'email_reminder';

    const EMAIL_RESET = 'email_reset';

    const MIN_PASSWORD_LENGTH = 6;

    /* Constants for the type of new account email to be sent*/
    const STATUS_CHANGE_REQUEST = 'status_request';

    /* Constants for the type of new account email to be sent*/
    const STATUS_CHANGE_REQUEST_REJECT = 'status_reject_request';

    const STATUS_CHANGE_ADMIN_REQUEST = 'status_admin_request';

    const CONTACT_ADMIN = 'contact_admin';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Random
     */
    protected $mathRandom;
    /**
     * @var PsrLogger
     */
    protected $logger;
    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;
    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var VendorViewHelper
     */
    protected $vendorViewHelper;
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var VendorModel
     */
    protected $vendorModel;
    /**
     * @var \Magedelight\Vendor\Model\Request
     */
    protected $vendorStatusRequest;
    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var Encryptor
     */
    protected $encryptor;
    /**
     * @var VendorRegistry
     */
    protected $vendorRegistry;
    /**
     * @var ConfigShare
     */
    protected $configShare;
    /**
     * @var StringHelper
     */
    protected $stringHelper;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Magento\Framework\Message\ManagerInterface;
     */
    protected $messageManager;

    /**
     * @var type array
     */
    protected $excludeFields;

    /**
     * @var \Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory
     */
    protected $calculatedSales = [];
    protected $productSold = [];
    protected $amountPaid = [];
    protected $_vendorData = null;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     */
    protected $vendorWebsite;

    /**
     * @var \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface
     */
    protected $vendorWebsiteRepository;

    /**
     * @var \Magedelight\Vendor\Model\VendorWebsiteFactory
     */
    protected $vendorWebsiteFactory;

    protected $_activeSection;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Directory\Model\Country\Postcode\Validator
     */
    protected $postCodeValidatior;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    protected $_filesystem;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var VendorInterface
     */
    protected $vendor;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $url;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var Registration
     */
    protected $registration;

    /**
     * @var Design
     */
    protected $design;

    /**
     * @var PageFactory
     */
    protected $PageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var Upload
     */
    protected $uploadModel;

    /**
     * @var ImageModel
     */
    protected $imageModel;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magento\Directory\Model\Country\Postcode\Validator
     */
    protected $postCodeValidator;

    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_file;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $vendorAcl;

    /**
     * @var \Magedelight\Sales\Api\Data\CustomMessageInterface
     */
    protected $customMessageInterface;
    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $driver;

    /**
     *
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param \Magedelight\Vendor\Model\VendorRegistry $vendorRegistry
     * @param PsrLogger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param VendorRepositoryInterface $vendorRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param Registry $registry
     * @param VendorViewHelper $vendorViewHelper
     * @param DateTime $dateTime
     * @param VendorModel $vendorModel
     * @param \Magedelight\Vendor\Model\RequestFactory $vendorStatusRequestFactory
     * @param RequestInterface $request
     * @param VendorInterface $vendor
     * @param MessageManagerInterface $messageManager
     * @param \Magedelight\Backend\Model\UrlInterface $url
     * @param ResponseFactory $responseFactory
     * @param \Magedelight\Vendor\Model\Registration $registration
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $pageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Vendor\Model\Upload $uploadModel
     * @param ImageModel $imageModel
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite
     * @param \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository
     * @param \Magedelight\Vendor\Model\VendorWebsiteFactory $vendorWebsiteFactory
     * @param DirectoryHelper $directoryHelper
     * @param \Magento\Directory\Model\Country\Postcode\Validator $postCodeValidator
     * @param \Magento\Framework\Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Model\AbstractModel $vendorAcl
     * @param \Magedelight\Sales\Api\Data\CustomMessageInterface $customMessageInterface
     * @param \Magento\Framework\Filesystem\DriverInterface $driver
     */
    public function __construct(
        VendorFactory $vendorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        VendorRegistry $vendorRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        VendorRepositoryInterface $vendorRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        VendorViewHelper $vendorViewHelper,
        DateTime $dateTime,
        VendorModel $vendorModel,
        RequestFactory $vendorStatusRequestFactory,
        RequestInterface $request,
        VendorInterface $vendor,
        MessageManagerInterface $messageManager,
        \Magedelight\Backend\Model\UrlInterface $url,
        ResponseFactory $responseFactory,
        Registration $registration,
        Design $design,
        PageFactory $pageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        Upload $uploadModel,
        ImageModel $imageModel,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite,
        \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository,
        \Magedelight\Vendor\Model\VendorWebsiteFactory $vendorWebsiteFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Directory\Model\Country\Postcode\Validator $postCodeValidator,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Model\AbstractModel $vendorAcl,
        \Magedelight\Sales\Api\Data\CustomMessageInterface $customMessageInterface
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->vendorRegistry = $vendorRegistry;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->vendorRepository = $vendorRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->vendorViewHelper = $vendorViewHelper;
        $this->dateTime = $dateTime;
        $this->vendorModel = $vendorModel;
        $this->vendorStatusRequest = $vendorStatusRequestFactory->create();
        $this->request = $request;
        $this->vendor = $vendor;
        $this->messageManager = $messageManager;
        $this->url = $url;
        $this->responseFactory = $responseFactory;
        $this->registration = $registration;
        $this->design = $design;
        $this->PageFactory = $pageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->uploadModel = $uploadModel;
        $this->imageModel = $imageModel;
        $this->excludeFields = ['status', 'email', 'mobile'];
        $this->vendorHelper = $vendorHelper;
        $this->vendorWebsite = $vendorWebsite;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        $this->vendorWebsiteFactory = $vendorWebsiteFactory;
        $this->directoryHelper = $directoryHelper;
        $this->authSession = $authSession;
        $this->postCodeValidator = $postCodeValidator;
        $this->_filesystem = $filesystem;
        $this->_directoryList = $directoryList;
        $this->_file = $file;
        $this->userContext = $userContext;
        $this->state = $state;
        $this->vendorAcl = $vendorAcl;
        $this->customMessageInterface = $customMessageInterface;
        /*$this->driver = $driver;*/
    }

    /**
     * {@inheritdoc}
     */
    public function resendConfirmation($email, $websiteId = null, $redirectUrl = '')
    {
        $vendor = $this->vendorRepository->get($email, $websiteId);
        if (!$vendor->getConfirmation()) {
            throw new InvalidTransitionException(__('No confirmation needed.'));
        }
        try {
            $this->sendNewAccountEmail(
                $vendor,
                self::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                $redirectUrl,
                $this->storeManager->getStore()->getId()
            );
        } catch (MailException $e) {
            /* If we are not able to send a new account email, this should be ignored*/
            $this->logger->critical($e);
        }
    }

    /**
     * Send email with new account related information
     * @param VendorInterface $vendor
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return $this
     * @throws LocalizedException
     */
    protected function sendNewAccountEmail(
        $vendor,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = '0',
        $sendemailStoreId = null
    ) {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new LocalizedException(__('Please correct the transactional account email type.'));
        }

        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($vendor, $sendemailStoreId);
        }

        $store = $this->storeManager->getStore($vendor->getStoreId());

        $vendorEmailData = $this->getFullVendorObject($vendor);

        $this->sendEmailTemplate(
            $vendor,
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['vendor' => $vendorEmailData, 'back_url' => $backUrl, 'store' => $store],
            $storeId
        );

        return $this;
    }

    /**
     * @return array
     */
    protected function getTemplateTypes()
    {
        /**
         * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
         *                                                  and password is not set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
         */
        $types = [
            self::NEW_ACCOUNT_EMAIL_REGISTERED             => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMED              => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMATION           => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
            self::STATUS_CHANGE_REQUEST                    => self::XML_PATH_STATUS_NOTIFICATION_EMAIL_TEMPLATE,
            self::STATUS_CHANGE_REQUEST_REJECT             => self::XML_PATH_STATUS_NOTIFICATION_REJECT_EMAIL_TEMPLATE,
            self::STATUS_CHANGE_ADMIN_REQUEST              => self::XML_PATH_STATUS_NOTIFICATION_EMAIL_ADMIN_TEMPLATE,
            self::CONTACT_ADMIN                            => self::XML_PATH_CONTACT_ADMIN_EMAIL_TEMPLATE
        ];
        return $types;
    }

    /**
     * Get either first store ID from a set website or the provided as default
     * @param VendorInterface $vendor
     * @return int
     * @throws LocalizedException
     */
    protected function getWebsiteStoreId($vendor)
    {
        if ($vendor->getWebsiteId() != 0 || ($vendor->getWebsiteId() === null)) {
            $storeIds = $this->storeManager->getWebsite($vendor->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Create an object with data merged from Vendor and VendorSecure
     * @param VendorInterface $vendor
     * @return Data\VendorSecure
     * @throws NoSuchEntityException
     */
    protected function getFullVendorObject($vendor)
    {
        /* No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        object passed for events */

        $mergedVendorData = $this->vendorRegistry->retrieveSecureData($vendor->getId());
        $vendorData = $this->dataProcessor
            ->buildOutputDataArray($vendor, \Magedelight\Vendor\Api\Data\VendorInterface::class);
        $mergedVendorData->addData($vendorData);
        $mergedVendorData->setData('name', $this->vendorViewHelper->getVendorName($vendor));
        return $mergedVendorData;
    }

    /**
     * Send corresponding email template
     * @param VendorInterface $vendor
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param null $email
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     */
    protected function sendEmailTemplate(
        $vendor,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
        if ($email === null) {
            $email = $vendor->getEmail();
        }
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                ['area' => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE, 'store' => $storeId]
            )->setTemplateVars($templateParams)->setFromByScope(
                $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId)
            )->setFromByScope('general')->addTo(
                $email,
                $this->vendorViewHelper->getVendorName($vendor)
            )->getTransport();

        $transport->sendMessage();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function activate($email, $confirmationKey)
    {
        $vendor = $this->vendorRepository->get($email);
        return $this->activateVendor($vendor, $confirmationKey);
    }

    /**
     * Activate a vendor account using a key that was sent in a confirmation email.
     * @param \Magedelight\Vendor\Api\Data\VendorInterface $vendor
     * @param string $confirmationKey
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function activateVendor($vendor, $confirmationKey)
    {
        /* check if vendor is inactive*/
        if (!$vendor->getConfirmation()) {
            throw new InvalidTransitionException(__('Account already active'));
        }

        if ($vendor->getConfirmation() !== $confirmationKey) {
            throw new InputMismatchException(__('Invalid confirmation token'));
        }

        $vendor->setConfirmation(null);
        $this->vendorRepository->save($vendor);
        $this->sendNewAccountEmail($vendor, 'confirmed', '', $this->storeManager->getStore()->getId());
        return $vendor;
    }

    /**
     * {@inheritdoc}
     */
    public function activateById($vendorId, $confirmationKey)
    {
        $vendor = $this->vendorRepository->getById($vendorId);
        return $this->activateVendor($vendor, $confirmationKey);
    }

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
        $hash = $this->vendorRegistry->retrieveSecureData($vendor->getId())->getPasswordHash();

        if ($hash) {
            if (!$this->encryptor->validateHash($password, $hash)) {
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

            $vendorModel = $this->vendorFactory->create()->updateData($vendor);

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
     * Check if accounts confirmation is required in config
     * @param VendorInterface $vendor
     * @return bool
     */
    protected function isConfirmationRequired($vendor)
    {
        if ($this->canSkipConfirmation($vendor)) {
            return false;
        }
        $storeId = $vendor->getStoreId() ? $vendor->getStoreId() : null;

        return (bool)$this->scopeConfig->getValue(self::XML_PATH_IS_CONFIRM, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address
     * @param VendorInterface $vendor
     * @return bool
     */
    protected function canSkipConfirmation($vendor)
    {
        /* skip confirmation if already verified. */
        if (!$vendor->getEmailVerified()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateResetPasswordLinkToken($vendorId, $resetPasswordLinkToken)
    {
        $this->validateResetPasswordToken($vendorId, $resetPasswordLinkToken);
        return true;
    }

    /**
     * Validate the Reset Password Token for a vendor.
     * @param int $vendorId
     * @param string $resetPasswordLinkToken
     * @return bool
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or vendor id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If vendor doesn't exist
     */
    private function validateResetPasswordToken($vendorId, $resetPasswordLinkToken)
    {
        if (empty($vendorId) || $vendorId < 0) {
            $params = ['value' => $vendorId, 'fieldName' => 'vendorId'];
            throw new InputException(__(InputException::INVALID_FIELD_VALUE, $params));
        }
        if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(__(InputException::REQUIRED_FIELD, $params));
        }

        $vendorSecureData = $this->vendorRegistry->retrieveSecureData($vendorId);
        $rpToken = $vendorSecureData->getRpToken();
        $rpTokenCreatedAt = $vendorSecureData->getRpTokenCreatedAt();

        if (!Security::compareStrings($rpToken, $resetPasswordLinkToken)) {
            throw new InputMismatchException(__('Reset password token mismatch.'));
        } elseif ($this->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
            throw new ExpiredException(__('Reset password token expired.'));
        }

        return true;
    }

    /**
     * Check if rpToken is expired
     * @param string $rpToken
     * @param string $rpTokenCreatedAt
     * @return bool
     * @throws \Exception
     */
    public function isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)
    {
        if (empty($rpToken) || empty($rpTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->vendorModel->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = (new \DateTime())->getTimestamp();
        $tokenTimestamp = (new \DateTime($rpTokenCreatedAt))->getTimestamp();
        if ($tokenTimestamp > $currentTimestamp) {
            return true;
        }

        $dayDifference = floor(($currentTimestamp - $tokenTimestamp) / (24 * 60 * 60));
        if ($dayDifference >= $expirationPeriod) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePasswordReset($email, $template, $websiteId = null)
    {
        $msg = '';
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        /* load vendor by email*/
        $vendor = $this->vendorRepository->get($email, $websiteId);

        if ($vendor->getStatus() == VendorStatus::VENDOR_REQUEST_STATUS_PENDING && !$vendor->getEmailVerified()) {
            $this->sendEmailConfirmation($vendor, '');
            $msg = "You can't request to change password. Please complete your registration process. ";
            $msg .= "You will got again an email to complete your registration.";
            throw new AuthenticationException(__($msg));
        }
        if ($vendor->getId() && !$vendor->getEmailVerified()) {
            throw new AuthenticationException(__('Please verify your account.'));
        }

        $newPasswordToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($vendor, $newPasswordToken);

        try {
            switch ($template) {
                case AccountManagement::EMAIL_REMINDER:
                    $this->sendPasswordReminderEmail($vendor);
                    break;
                case AccountManagement::EMAIL_RESET:
                    $this->sendPasswordResetConfirmationEmail($vendor);
                    break;
                default:
                    throw new InputException(
                        __(
                            InputException::INVALID_FIELD_VALUE,
                            ['value' => $template, 'fieldName' => 'email type']
                        )
                    );
            }
            return true;
        } catch (MailException $e) {
            /* If we are not able to send a reset password email, this should be ignored*/
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * Change reset password link token
     * Stores new reset password link token
     * @param VendorInterface $vendor
     * @param string $passwordLinkToken
     * @return bool
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function changeResetPasswordLinkToken($vendor, $passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new InputException(
                __(
                    InputException::INVALID_FIELD_VALUE,
                    ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        }
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            $vendorSecure = $this->vendorRegistry->retrieveSecureData($vendor->getId());
            $vendorSecure->setRpToken($passwordLinkToken);
            $vendorSecure->setRpTokenCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
            $this->vendorRepository->save($vendor);
        }
        return true;
    }

    /**
     * Send email with new vendor password
     * @param VendorInterface $vendor
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendPasswordReminderEmail($vendor)
    {
        $storeId = $vendor->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($vendor);
        }

        $vendorEmailData = $this->getFullVendorObject($vendor);

        $this->sendEmailTemplate(
            $vendor,
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['vendor' => $vendorEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * Send email with reset password confirmation link
     * @param VendorInterface $vendor
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendPasswordResetConfirmationEmail($vendor)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($vendor);
        }

        $vendorEmailData = $this->getFullVendorObject($vendor);

        $this->sendEmailTemplate(
            $vendor,
            self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['vendor' => $vendorEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPassword($email, $resetToken, $newPassword)
    {
        $vendor = $this->vendorRepository->get($email);
        /*Validate Token and new password strength*/
        $this->validateResetPasswordToken($vendor->getId(), $resetToken);
        $this->checkPasswordStrength($newPassword);
        /*Update secure data*/
        $vendorSecure = $this->vendorRegistry->retrieveSecureData($vendor->getId());
        $vendorSecure->setRpToken(null);
        $vendorSecure->setRpTokenCreatedAt(null);
        $vendorSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->vendorRepository->save($vendor);
        return true;
    }

    /**
     * Create a hash for the given password
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationStatus($vendorId)
    {
        /* load vendor by id*/
        $vendor = $this->vendorRepository->getById($vendorId);
        if (!$vendor->getConfirmation()) {
            return self::ACCOUNT_CONFIRMED;
        }
        if ($this->isConfirmationRequired($vendor)) {
            return self::ACCOUNT_CONFIRMATION_REQUIRED;
        }
        return self::ACCOUNT_CONFIRMATION_NOT_REQUIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function createAccount(
        VendorInterface $vendor,
        $postData = [],
        $password = null,
        $redirectUrl = '',
        $createdBy = 'vednor'
    ) {
        if ($password !== null) {
            $this->checkPasswordStrength($password);
            $hash = $this->createPasswordHash($password);
        } else {
            $hash = null;
        }
        return $this->createAccountWithPasswordHash($vendor, $postData, $hash, $redirectUrl, $createdBy);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function createAccountWithPasswordHash(
        VendorInterface $vendor,
        $postData = [],
        $hash,
        $redirectUrl,
        $createdBy
    ) {
        /* This logic allows an existing vendor to be added to a different store.  No new account is created.
         The plan is to move this logic into a new method called something like 'registerAccountWithStore' */
        if ($vendor->getId()) {
            $vendor = $this->vendorRepository->get($vendor->getEmail());
            $websiteId = $vendor->getWebsiteId();

            if ($this->isVendorInStore($websiteId, $vendor->getStoreId())) {
                throw new InputException(__('This vendor already exists in this store.'));
            }
            /* Existing password hash will be used from secured vendor data registry when saving vendor*/
        }
        /* Make sure we have a storeId to associate this vendor with.*/
        if (!$vendor->getStoreId()) {
            if ($vendor->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($vendor->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $storeId = $this->storeManager->getStore()->getId();
            }

            $vendor->setStoreId($storeId);
        }

        try {
            /* If vendor exists existing hash will be used by Repository*/
            if (!$this->isConfirmationRequired($vendor)) {
                $this->vendorWebsite->setData('email_verified', 1);
            }

            $vendor = $this->vendorRepository->save($vendor, $hash);
            $vendorId = $vendor->getId();

            if ($vendorId) {
                $this->vendorWebsite->setVendorId($vendorId);
                $this->vendorWebsite->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
                $this->vendorWebsite->setStoreId($this->storeManager->getStore()->getStoreId());
                $this->vendorWebsite->setBusinessName($postData['business_name']);
                $this->vendorWebsite->setName($postData['name']);
                $this->vendorWebsite->setEmailVerificationCode(
                    substr($this->mathRandom->getUniqueHash(), 0, 20)
                );
                $this->vendorWebsite->setMobileVerificationCode(
                    substr($this->mathRandom->getUniqueHash(), 0, 20)
                );
                $this->vendorWebsiteRepository->save($this->vendorWebsite);
            }
            if ($this->vendorHelper->useWizard()) {
                $eventParams = ['request' => $postData,'vendor' => $vendor];
                $this->eventManager->dispatch('vendor_register_success', $eventParams);
            }
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A vendor with the same email already exists in an associated website.')
            );
        } catch (LocalizedException $e) {
            throw $e;
        }

        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($vendor, $newLinkToken);
        if ($this->isConfirmationRequired($vendor)) {
            $this->sendEmailConfirmation($vendor, $redirectUrl);
        }
        return $vendor;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function createVendorAccountFromAdmin($postData = [])
    {
        try {
            $vendorWebsite = $this->vendorFactory->create()->getCollection()
                    ->addFieldToFilter('email', trim($postData['email']))
                    ->addFieldToFilter('website_id', $postData['website_id'])->getFirstItem();
            if (!$vendorWebsite->getId()) {
                $vendorFactory = $this->vendorFactory->create();
                $vendorFactory->addData(
                    [
                        'email' => trim($postData['email']),
                        'website_id'=> $postData['website_id'],
                        'mobile'=> '+' . $postData['country_code'] . $postData['mobile']
                    ]
                );
                $vendorWebsite = $this->vendorWebsiteFactory->create();
                $imageError = $this->processFilesAdmin($postData, $vendorWebsite);
                if (!empty($imageError['error'])) {
                    throw new LocalizedException(__($imageError['error']));
                }
                $vendor = $vendorFactory->save();
                if ($vendor->getId()) {
                    $eventParams = ['vendor' => $vendor];
                    $this->eventManager->dispatch('vendor_register_success_admin', $eventParams);
                    $postData['vendor_id'] = $vendor->getId();
                    $postData['store_id'] = $this->getWebsiteStoreId($vendor);
                    foreach ($postData as $key => $vendorDataItem) {
                        if (is_array($vendorDataItem)) {
                            continue;
                        }
                        if ($key == 'status' && $vendorDataItem == RequestStatuses::VENDOR_REQUEST_STATUS_APPROVED) {
                            $vendorWebsite->setData('email_verified', 1);
                        }
                        $vendorWebsite->setData($key, $vendorDataItem);
                    }
                    $vendorWebsite->setEmailVerificationCode(
                        substr($this->mathRandom->getUniqueHash(), 0, 20)
                    );
                    $vendorWebsite->setMobileVerificationCode(
                        substr($this->mathRandom->getUniqueHash(), 0, 20)
                    );
                    $vendorWebsite->save();
                    $newLinkToken = $this->mathRandom->getUniqueHash();
                    if (array_key_exists('categories_ids', $postData)) {
                        $vendor->setData('categories_ids', $postData['categories_ids']);
                    }
                    $this->changeResetPasswordLinkToken($vendor, $newLinkToken);
                }
                return $vendor;
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isVendorInStore($vendorWebsiteId, $storeId)
    {
        $ids = [];
        if ((bool)$this->configShare->isWebsiteScope()) {
            $ids = $this->storeManager->getWebsite($vendorWebsiteId)->getStoreIds();
        } else {
            foreach ($this->storeManager->getStores() as $store) {
                $ids[] = $store->getId();
            }
        }

        return in_array($storeId, $ids);
    }

    /**
     * Send either confirmation or welcome email after an account creation
     * @param VendorInterface $vendor
     * @param string $redirectUrl
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendEmailConfirmation(VendorInterface $vendor, $redirectUrl)
    {
        try {
            $hash = $this->vendorRegistry->retrieveSecureData($vendor->getId())->getPasswordHash();
            /*$templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED;
            if ($this->isConfirmationRequired($vendor)) {*/
            $templateType = self::NEW_ACCOUNT_EMAIL_CONFIRMATION;

            $vendorWebisteData = $this->vendorWebsiteRepository->getVendorWebsiteData(
                $vendor->getId(),
                $this->storeManager->getStore()->getWebsiteId()
            );
            if ($vendorWebisteData && $vendorWebisteData->getId()) {
                $vendor->setEmailVerificationCode($vendorWebisteData->getEmailVerificationCode());
            }

            $this->sendNewAccountEmail($vendor, $templateType, $redirectUrl);
        } catch (MailException $e) {
            /* If we are not able to send a new account email, this should be ignored*/
            $this->logger->critical($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBillingAddress($vendorId)
    {
        $vendor = $this->vendorRepository->getById($vendorId);
        return $this->getAddressById($vendor, $vendor->getDefaultBilling());
    }

    /**
     * Get address by id
     * @param VendorInterface $vendor
     * @param int $addressId
     * @return AddressInterface|null
     */
    protected function getAddressById(VendorInterface $vendor, $addressId)
    {
        foreach ($vendor->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function changePassword($email, $currentPassword, $newPassword)
    {
        try {
            $vendor = $this->vendorRepository->get($email);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForVendor($vendor, $currentPassword, $newPassword);
    }

    /**
     * Change vendor password.
     * @param VendorModel $vendor
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function changePasswordForVendor($vendor, $currentPassword, $newPassword)
    {
        $vendorSecure = $this->vendorRegistry->retrieveSecureData($vendor->getId());
        $hash = $vendorSecure->getPasswordHash();
        if (!$this->encryptor->validateHash($currentPassword, $hash)) {
            throw new InvalidEmailOrPasswordException(__('The current password doesn\'t match this account.'));
        }
        $vendorSecure->setRpToken(null);
        $vendorSecure->setRpTokenCreatedAt(null);
        $this->checkPasswordStrength($newPassword);
        $vendorSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->vendorRepository->save($vendor);
        /* FIXME: Are we using the proper template here?*/
        try {
            $this->sendPasswordResetNotificationEmail($vendor);
        } catch (MailException $e) {
            $this->logger->critical($e);
        }

        return true;
    }

    /**
     * Send email to vendor when his password is reset
     * @param VendorInterface $vendor
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    protected function sendPasswordResetNotificationEmail($vendor)
    {
        $storeId = $vendor->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($vendor);
        }

        $vendorEmailData = $this->getFullVendorObject($vendor);

        $this->sendEmailTemplate(
            $vendor,
            self::XML_PATH_RESET_PASSWORD_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['vendor' => $vendorEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function changePasswordById($vendorId, $currentPassword, $newPassword)
    {
        try {
            $vendor = $this->vendorRepository->getById($vendorId);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForVendor($vendor, $currentPassword, $newPassword);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailAvailable($vendorEmail, $websiteId = null)
    {
        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $this->vendorRepository->get($vendorEmail, $websiteId);
            return false;
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }

    /**
     * Check if vendor can be deleted.
     * @param int $vendorId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws LocalizedException
     */
    public function isReadonly($vendorId)
    {
        $vendor = $this->vendorRegistry->retrieveSecureData($vendorId);
        return !$vendor->getDeleteable();
    }

    /**
     * Return hashed password, which can be directly saved to database.
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return $this->encryptor->getHash($password);
    }

    /**
     * set vendor profile data for particular section
     * @param array
     */
    public function processVendorFields($vendorFields, $vendorWebsite)
    {
        $excludeFields = ['status', 'email', 'mobile'];
        $postData = $this->request->getPost();

        foreach ($postData as $key => $vendorDataItem) {
            if (in_array($key, $excludeFields)) {
                continue;
            }
            if (is_array($vendorDataItem)) {
                continue;
            }
            $vendorWebsite->setData($key, $vendorDataItem);
        }
    }

    /**
     *
     * {@inheritdoc}
     */
    public function sendVendorStatusRequestApi($id)
    {
        $statusRequestType = 1;

        $vendorFields = ['vendor_id','vacation_from_date', 'vacation_to_date', 'vacation_message','request_type'];
        $this->processVendorFields($vendorFields);

        $vendor = $this->vendorRepository->getById($id);
        return $this->sendVendorStatusRequest($this->vendor, $statusRequestType);
    }

    /**
     * {@inheritdoc}
     */
    public function sendVendorStatusRequest(VendorInterface $vendor, $statusRequestType)
    {
        $this->vendorStatusRequest->setData('vendor_id', $vendor->getId());
        $this->vendorStatusRequest->setData('request_type', $statusRequestType);
        $this->vendorStatusRequest->setData('reason', $vendor->getVacationMessage());
        $this->vendorStatusRequest->setData('vacation_from_date', $vendor->getVacationFromDate());
        $this->vendorStatusRequest->setData('vacation_to_date', $vendor->getVacationToDate());
        $this->vendorStatusRequest->save();
        $this->sendVendorStatusRequestEmail($vendor, $statusRequestType);
    }

    /**
     * submit vendor query email to admin
     * @param VendorInterface $vendor
     * @param $data
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function submitVendorQuery(VendorInterface $vendor, $data)
    {
        $types = $this->getTemplateTypes();

        $type = self::CONTACT_ADMIN;

        if (!isset($types[$type])) {
            throw new LocalizedException(__('Please correct the transactional account email type.'));
        }

        $storeId = 0;

        $store = $this->storeManager->getStore($storeId);

        $vendorEmailData = $this->vendorRepository->getById($vendor->getId());

        $vendorEmailData->setData('query', $data['query']);
        $vendorEmailData->setData('message', $data['message']);

        $this->sendEmailAdminTemplate(
            $vendorEmailData,
            $types[$type],
            self::XML_PATH_CONTACT_ADMIN_EMAIL_IDENTITY,
            self::XML_PATH_CONTACT_ADMIN_EMAIL_RECIPIENT,
            ['vendor' => $vendorEmailData, 'store' => $store],
            $storeId
        );
        $this->customMessageInterface->setMessage(__(
            "Thanks for contacting us with your comments and questions. We'll respond to you very soon."
        ));
        $this->customMessageInterface->setStatus(true);
        return $this->customMessageInterface;
    }

    /**
     * Send corresponding email template
     * @param VendorInterface $vendor
     * @param string $template configuration path of email template
     * @param $sender
     * @param string $receiver configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    protected function sendEmailAdminTemplate(
        $vendor,
        $template,
        $sender,
        $receiver,
        $templateParams = [],
        $storeId = null
    ) {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area'  => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($templateParams)
            ->setFromByScope($this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId))
            ->addTo($this->scopeConfig->getValue($receiver, ScopeInterface::SCOPE_STORE, $storeId))
            ->setReplyTo($vendor->getEmail())
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }

    /**
     * Send notificationemail after change the status request
     * @param VendorInterface $vendor
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function sendEmailStatusRequestNotification(VendorInterface $vendor, $data = [])
    {
        try {
            $templateType = '';
            if ($data['request_status'] == "Approved") {
                $templateType = self::STATUS_CHANGE_REQUEST;
            } elseif ($data['request_status'] == "Rejected") {
                $templateType = self::STATUS_CHANGE_REQUEST_REJECT;
            }

            $redirectUrl = '';
            if ($templateType) {
                $this->sendRequestStatusChangeEmail($vendor, $data, $templateType, $redirectUrl, 0);
            }
        } catch (MailException $e) {
            /* If we are not able to send a new account email, this should be ignored*/
            $this->logger->critical($e);
        }
    }

    /**
     * Send email with status request of vendor profile approved/closed.
     * @param VendorInterface $vendor
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return $this
     * @throws LocalizedException
     */
    protected function sendRequestStatusChangeEmail(
        $vendor,
        $data,
        $type = '',
        $backUrl = '',
        $storeId = '0',
        $sendemailStoreId = null
    ) {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new LocalizedException(__('Please correct the transactional account email type.'));
        }

        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($vendor, $sendemailStoreId);
        }

        $store = $this->storeManager->getStore($vendor->getStoreId());

        $vendorEmailData = $this->getFullVendorObject($vendor);

        $vendorEmailData['request_status'] = $data['request_status'];
        $vendorEmailData['vendor_status'] = $data['vendor_status'];
        $vendorEmailData['request_type'] = $data['request_type'];

        $this->sendEmailTemplate(
            $vendor,
            $types[$type],
            self::XML_PATH_NOTIFICATION_EMAIL_IDENTITY,
            [
                'vendor'   => $vendorEmailData,
                'back_url' => $backUrl,
                'store'    => $store
            ],
            $storeId
        );

        return $this;
    }

    /**
     * Send notificationemail after change the status request
     * @param VendorInterface $vendor
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function sendEmailStatusRequestRejectNotification(VendorInterface $vendor, $data = [])
    {
        try {
            $templateType = self::STATUS_CHANGE_REQUEST_REJECT;
            $redirectUrl = '';
            $this->sendRequestStatusChangeEmail($vendor, $data, $templateType, $redirectUrl, 0);
        } catch (MailException $e) {
            /* If we are not able to send a new account email, this should be ignored*/
            $this->logger->critical($e);
        }
    }

    /**
     * send vendor status change request email to admin
     * @param VendorInterface $vendor
     * @param string $statusRequestType
     * @return AccountManagement
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function sendVendorStatusRequestEmail(VendorInterface $vendor, $statusRequestType = '')
    {
        $types = $this->getTemplateTypes();

        $type = self::STATUS_CHANGE_ADMIN_REQUEST;

        if (!isset($types[$type])) {
            throw new LocalizedException(__('Please correct the transactional account email type.'));
        }

        $storeId = 0;

        $store = $this->storeManager->getStore($storeId);

        $vendorEmailData = $this->getFullVendorObject($vendor);
        if ($statusRequestType == 2) {
            $data = ['vendor' => $vendorEmailData, 'store' => $store, 'statusRequestTypeClosed'=> true];
        } else {
            $data = ['vendor' => $vendorEmailData, 'store' => $store, 'statusRequestTypeVacation'=> true];
        }

        $this->sendEmailAdminTemplate(
            $vendor,
            $types[$type],
            self::XML_PATH_NOTIFICATION_EMAIL_IDENTITY,
            self::XML_PATH_NOTIFICATION_EMAIL_RECIPIENT,
            $data,
            $storeId
        );

        return $this;
    }
    /**
     * Send vendor Verification email to admin
     */
    public function sendVerificationMail()
    {
        $post = $this->request->getPost();

        $msg = '';
        $result = [];
        if (!$post) {
            $result['redirect_url'] = $this->url->getUrl('rbvendor');
            return $this->jsonHelper->jsonEncode($result);
        }
        $vendor = $this->initVendor();

        /*check Email and phone number pattern validation from server side */
        $regex = "/^(?=.*\d).{1,10}$/";
        if (!preg_match($regex, $post['mobile'])) {
            $result['redirect_url'] = $this->url->getUrl('rbvendor');
            $result['message']['type'] = 'notice';
            $result['message']['data'] = __('Please enter valid phone number, use 10 or less than 10 digits only.');
            return $this->jsonHelper->jsonEncode($result);
        }

        $regex = "/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,100}$/i";
        if (!preg_match($regex, $post['email'])) {
            $result['redirect_url'] = $this->url->getUrl('rbvendor');
            $result['message']['type'] = 'notice';
            $result['message']['data'] = __('Please enter valid email address. Character limit 100 only.');
            return $this->jsonHelper->jsonEncode($result);
        }

        if ($vendor->isEmailExist($post['email'])) {
            $vendor = $this->vendorRepository->get($post['email']);
            if ($vendor->getEmailVerified()) {
                if ($vendor->getBusinessName() == null || $vendor->getBusinessName() == '') {
                    $url = $this->url->getUrl(
                        '*/*/register',
                        ['rbhash'=> $vendor->getEmailVerificationCode(), '_secure' => true]
                    );
                    $result['status'] = true;
                    $result['redirect_url'] = $url;
                    $result['message']['type'] = 'notice';
                    $result['message']['data'] =__(
                        'You have already registered as vendor, Please proceed with further steps of registration.'
                    );
                    return $this->jsonHelper->jsonEncode($result);
                }
            }
            $result['redirect_url'] = $this->url->getUrl('rbvendor');
            $result['url'] = 'rbvendor';
            $result['message']['type'] = 'notice';
            $result['message']['data'] =__('You have already registered as vendor, Login to continue..');
            return $this->jsonHelper->jsonEncode($result);
        }

        try {
            $errors = [];

            if (!\Zend_Validate::is(trim($post['mobile']), 'NotEmpty')) {
                $errors[] = __('Enter a valid phone number.');
            }
            if (!\Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                $errors[] = __('Enter a valid email address.');
            }

            if (!empty($errors)) {
                $result['redirect_url'] = $this->url->getUrl('rbvendor/index/index');
                $result['message']['type'] = 'error';
                $result['message']['error'] = $errors;
                return $this->jsonHelper->jsonEncode($result);
            }

            if (empty($errors)) {
                $vendor->setEmail($post['email']);
                $vendor->setMobile('+' . $post['country_code'] . $post['mobile']);
                if ($this->vendorHelper->useWizard()) {
                    $vendor->setPassword($post['password']);
                }
                $vendor->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
                $vendor->setStoreId($this->storeManager->getStore()->getStoreId());
                $this->createAccount($vendor, $post);
            }
            if ($vendor->getEmailVerified()) {
                $result['redirect_url'] = $this->url->getUrl(
                    'rbvendor/account/register',
                    ['rbhash' => $vendor->getEmailVerificationCode()]
                );
                return $this->jsonHelper->jsonEncode($result);
            }
            $eventParams = ['vendor' => $vendor];
            $this->eventManager->dispatch('vendor_register_first_step', $eventParams);
            $result['redirect_url'] = $this->url->getUrl('rbvendor/index/index');
            $result['message']['type'] = 'success';
            $msg = 'Thank you for registering, We have sent you an email with instructions ';
            $msg .= 'and the next steps for you to complete your registration.';
            $result['message']['data'] = __($msg);
            return $this->jsonHelper->jsonEncode($result);
        } catch (\Exception $e) {
            $result['redirect_url'] = $this->url->getUrl('rbvendor/index/index');
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
            return $this->jsonHelper->jsonEncode($result);
        }
    }

    /**
     * @return VendorInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function initVendor()
    {
        $postData = $this->request->getPost();
        /** @var \Magedelight\Vendor\Api\Data\VendorInterface $vendor */
        $vendor = $this->vendorRepository->getById($postData['id']);
        $this->registry->register('md_vendor', $vendor);
        return $vendor;
    }

    /**
     * @return array|bool|float|int|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function registerVendor()
    {

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */

        /*$resultRedirect = $this->resultRedirectFactory->create();*/
        $postData = $this->request->getParams();
        $result = [];
        $rbhash = isset($postData['rbhash']) ? $postData['rbhash'] : '';
        if (empty($rbhash) || $this->authSession->isLoggedIn()) {
            $result['redirect_url'] = 'rbvendor/account/dashboard';
            return $this->jsonHelper->jsonEncode($result);
        }

        /**
         * \Magedelight\Vendor\Api\Data\VendorInterface $vendor
         */
        $vendor = $this->getVendorByEmailVerificationCode();
        if (!$this->getVendorByCode()) {
            $result['redirect_url'] = 'rbvendor';
            $result['message']['type'] = 'error';
            $result['message']['data'] =__('Please register using Email address');
            return $this->jsonHelper->jsonEncode($result);
        }
        if ($vendor->getEmailVerified() && $vendor->getEmailVerified() == 1) {
            $result['redirect_url'] = 'rbvendor';
            $result['message']['type'] = 'notice';
            $result['message']['data'] =__('You have already registered as vendor, Login to continue.');
            return $this->jsonHelper->jsonEncode($result);
        }
        $this->design->applyVendorDesign();
        return true;
    }

    /**
     * @param string $rbhash
     * @return VendorInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getVendorByEmailVerificationCode($rbhash = '')
    {
        $postData = $this->request->getParams();
        $token = ($rbhash) ? $rbhash : $postData['rbhash'];
        /** @var \Magedelight\Vendor\Api\Data\VendorInterface $vendor */
        $vendor = $this->vendorRepository->getByEmailVerificationCode($token);
        $this->registry->register('md_vendor', $vendor);
        return $vendor;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getVendorByCode()
    {
        $postData = $this->request->getParams();
        $rbhash = $postData['rbhash'];
        $vendor = $this->vendorRepository->getByEmailVerificationCode($rbhash);
        if ($vendor && $vendor->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @param $email
     * @return bool
     */
    protected function getEmailVarify($email)
    {
        $collection = $this->vendorFactory->create()->getCollection();
        $collection->_addWebsiteData(['email_verified']);
        $collection->addFieldToFilter('email', ['eq'=>$email]);
        $collection->addFieldToFilter('email_verified', ['eq'=> 1 ]);
        if ($collection->count()) {
            return true;
        }
        return false;
    }

    /**
     * @param boolean $returnFirstError
     * @return void|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    public function registerPostVendor($returnFirstError = false)
    {
        $postData = $this->request->getParams();

        if ($this->authSession->isLoggedIn() || !$this->registration->isAllowed() || $this->request->isPost() != 1) {
            $result['redirect_url'] = $this->url->getUrl('rbvendor');
            return $this->jsonHelper->jsonEncode($result);
        }

        /* Validate vendor on registration. */
        $validationResult = $this->validateVendorOnRegistration($postData, $returnFirstError);
        if ($validationResult) {
            return $validationResult;
        }
        /* Validate vendor on registration. */

        $rbhash = isset($postData['rbhash']) ? $postData['rbhash'] : '';
        $url = $this->url->getUrl('*/*/register/rbhash/' . $postData['rbhash']);

        $vendor = $this->getVendorByEmailVerificationCode();

        if (!$vendor->getId()) {
            $url = $this->url->getUrl('rbvendor', ['_secure' => true]);
            $result['redirect_url'] = $url;
            $result['message']['type'] = 'error';
            $result['message']['data'] =__('Please register using Email address');
            return $this->jsonHelper->jsonEncode($result);
        }

        $vendor->setData('password', $postData['password']);
        $vendor->setData('cpassword', $postData['cpassword']);
        $vendor->setData('other_marketplace_profile', $postData['other_marketplace_profile']);

        if (isset($postData['category'])) {
            $vendor->setCategoriesIds($postData['category']);
        }

        $exceptionUrl = $this->url->getUrl('*/*/register', ['rbhash' => $rbhash, '_secure' => true]);
        try {
            $vendorWebsiteDataValidate = true;
            $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData($vendor->getId());
            if ($vendorWebsite->getId()) {
                $excludeFields[] = ['status'];
                foreach ($postData as $key => $vendorDataItem) {
                    if (in_array($key, $excludeFields)) {
                        continue;
                    }
                    $vendorWebsite->setData($key, $vendorDataItem);
                }
                $vendorWebsite->setData('email_verified', 1);
                $vendorWebsiteDataValidate = $vendorWebsite->validate();
            }

            $imageError = $this->processFiles($postData, $vendorWebsite);
            if (!empty($imageError['error'])) {
                $result['redirect_url'] = $url;
                $result['message']['type'] = 'error';
                $result['message']['data'] = $imageError;
                return $this->jsonHelper->jsonEncode($result);
            }

            if (is_array($vendorWebsiteDataValidate)) {
                $result['redirect_url'] = $url;
                $result['message']['type'] = 'error';
                $result['message']['data'] = implode(',', $vendorWebsiteDataValidate);
                return $this->jsonHelper->jsonEncode($result);
            }
            $this->eventManager->dispatch('vendor_register_before_save', ['request' => $postData, 'vendor' => $vendor]);
            $vendorModel = $this->vendorRepository->save($vendor);
            $vendorWebsite->save();

            /* This event gives possibility to launch something after vendor saved*/
            $eventParams = ['request' => $postData,'vendor' => $vendorModel];
            $this->eventManager->dispatch('vendor_register_success', $eventParams);
            $this->eventManager->dispatch('vendor_register_success_after', $eventParams);

            $result['message']['type'] = 'success';
            $result['message']['data'] =__(
                'Your account has been setup and under review. In the meantime, you can login to update your profile.'
            );

            $result['redirect_url'] = $this->url->getUrl('rbvendor');
            return $this->jsonHelper->jsonEncode($result);
        } catch (\Magento\Framework\Exception\State\InputMismatchException $e) {
            $result['redirect_url'] = $exceptionUrl;
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
            return $this->jsonHelper->jsonEncode($result);
        } catch (\RuntimeException $e) {
            $result['redirect_url'] = $exceptionUrl;
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
            return $this->jsonHelper->jsonEncode($result);
        } catch (\Magento\Framework\Model\Exception $e) {
            $result['redirect_url'] = $exceptionUrl;
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
            return $this->jsonHelper->jsonEncode($result);
        } catch (\Exception $e) {
            $result['redirect_url'] = $exceptionUrl;
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Something went wrong while saving the vendor.');
            return $this->jsonHelper->jsonEncode($result);
        }
    }

    /**
     * Validate vendor on registration.
     * @param array $postData
     * @param boolean $returnFirstError
     * @return array|null
     * @throws NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    protected function validateVendorOnRegistration($postData = [], $returnFirstError = false)
    {
        $result = [];
        $url = $this->url->getUrl('*/*/register/rbhash/' . $postData['rbhash']);

        $errors = $this->validateVendorInfo($postData, $returnFirstError);
        if (count($errors) > 0) {
            $result['message']['type'] = 'warning';
            $result['message']['data'] = implode(', ', $errors);
            $result['redirect_url'] = $url;
            return $this->jsonHelper->jsonEncode($result);
        } else {
            $errors = $this->validateSellingCategoriesInfo($postData, $returnFirstError);
            if (count($errors) > 0) {
                $result['message']['type'] = 'warning';
                $result['message']['data'] = implode(', ', $errors);
                $result['redirect_url'] = $url;
                return $this->jsonHelper->jsonEncode($result);
            } else {
                $errors = $this->validateBusinessInfo($postData, $returnFirstError);
                if (count($errors) > 0) {
                    $result['message']['type'] = 'warning';
                    $result['message']['data'] = implode(', ', $errors);
                    $result['redirect_url'] = $url;
                    return $this->jsonHelper->jsonEncode($result);
                }
            }
        }
    }

    /**
     *
     * @param array|object $postData
     * @param boolean $returnFirstError
     * @param bool $isVendorEdit
     * @return array
     * @throws \Zend_Validate_Exception
     */
    public function validateVendorInfo($postData, $returnFirstError = false, $isVendorEdit = false)
    {
        $errors = [];

        if (is_array($postData)) {
            $vendor = new \Magento\Framework\DataObject();
            $vendor->setData($postData);
        } else {
            $vendor = $postData;
        }

        if (empty($postData) || count((array)$postData) < 5 && !$isVendorEdit) {
            $errors[] =__('Please fill up all registration details.');
        }

        if ($vendor->getEmail() && !$isVendorEdit) {
            if ($this->getEmailVarify($vendor->getEmail())) {
                $errors[] =__('Email is already in used please use another one.');
            }
        }

        if (!\Zend_Validate::is($vendor->getName(), 'NotEmpty')) {
            $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'Name']);
        } else {
            if (!\Zend_Validate::is($vendor->getName(), 'Regex', ['pattern'=>'/^[a-zA-Z ]{1,150}$/'])) {
                $errors[] = __(
                    'Allow alphabatic and space value, character limit 150, not allowed special character for %fieldName.',
                    ['fieldName' => 'Name']
                );
            }
        }

        if (!$isVendorEdit) {
            if (!\Zend_Validate::is($vendor->getPassword(), 'NotEmpty')) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'Password']);
            } elseif (!\Zend_Validate::is(
                $vendor->getPassword(),
                'Regex',
                ['pattern'=>'/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=]).{6,30}$/']
            )) {
                $errors[] = __(
                    '%fieldName must be at least 6 characters and no more than 30 characters, also it must include alphanumeric lower and upper case letters with at least one special character.',
                    ['fieldName' => 'Password']
                );
            } elseif (!\Zend_Validate::is($vendor->getCpassword(), 'NotEmpty')) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'Confirm password']);
            } else {
                if ($vendor->getPassword() != $vendor->getCpassword()) {
                    $errors[] = __('Password and the confirm password field does not the have same value.');
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('address1', 'personal')) {
            /* Using $vendor->getData('address1') instead of
             $vendor->getAddress1() as it is returning null due to number suffix.*/
            if (!\Zend_Validate::is($vendor->getData('address1'), 'NotEmpty')) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'Address Line 1']);
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getData('address1'),
                    'Regex',
                    ['pattern'=>'/^.{1,150}$/']
                )) {
                    $errors[] = __(
                        'Please enter less than or equal to 150 characters in %fieldName.',
                        ['fieldName' => 'Address Line 1']
                    );
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('address2', 'personal')) {
            /* Using $vendor->getData('address2') instead of
             $vendor->getAddress2() as it is returning null due to number suffix.*/
            if (!empty($vendor->getData('address2'))) {
                if (!\Zend_Validate::is(
                    $vendor->getData('address2'),
                    'Regex',
                    ['pattern'=>'/^.{1,150}$/']
                )) {
                    $errors[] = __(
                        'Please enter less than or equal to 150 characters in %fieldName.',
                        ['fieldName' => 'Address Line 2']
                    );
                }
            }
        }

        if (!\Zend_Validate::is($vendor->getCountryId(), 'NotEmpty')) {
            if (!$this->vendorHelper->isRemoved('country_id', 'personal')) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'Country']);
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
                        $errors[] = __('Please select states if available or enter state name');
                    }
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('city', 'personal')) {
            if (!\Zend_Validate::is($vendor->getCity(), 'NotEmpty')) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'City']);
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getCity(),
                    'Regex',
                    ['pattern'=>'/^[a-zA-Z ]{1,50}$/']
                )) {
                    $errors[] = __(
                        'Please use less than 50 characters in the %fieldName field, only alphabets and space are allowed.',
                        ['fieldName' => 'City']
                    );
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('pincode', 'personal')) {
            if (!\Zend_Validate::is($vendor->getPincode(), 'NotEmpty')) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'Pincode']);
            } else {
                $postCodeValidateStatus = $this->postCodeValidator->validate(
                    $vendor->getPincode(),
                    $vendor->getCountryId()
                );
                if (!$postCodeValidateStatus) {
                    $errors[] = __('Invalid Post Code');
                }
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     *
     * @param array $postData
     * @param boolean $returnFirstError
     * @return array
     */
    public function validateSellingCategoriesInfo($postData = [], $returnFirstError = false)
    {
        $errors = [];
        $vendor = new \Magento\Framework\DataObject();
        $vendor->setData($postData);

        if (empty($vendor->getCategory())) {
            $errors[] = __('Please select any category from list.');
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     *
     * @param array|object $postData
     * @param array $errors
     * @param boolean $returnFirstError
     * @param boolean $isVendorEdit
     * @return array|null
     * @throws NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    public function validateBusinessDetails($postData, $errors = [], $returnFirstError = false, $isVendorEdit = false)
    {
        if (is_array($postData)) {
            $vendor = new \Magento\Framework\DataObject();
            $vendor->setData($postData);
        } else {
            $vendor = $postData;
        }

        if (empty($vendor->getStatus()) || (!empty($vendor->getStatus()) &&
                !in_array(
                    $vendor->getStatus(),
                    [VendorStatus::VENDOR_STATUS_ACTIVE, VendorStatus::VENDOR_STATUS_VACATION_MODE]
                ))) {
            if (!\Zend_Validate::is($vendor->getBusinessName(), 'NotEmpty')) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'Business Name']);
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getBusinessName(),
                    'Regex',
                    ['pattern' => '/^.{1,150}$/']
                )) {
                    $errors[] = __('Character limit is 150 for %fieldName.', ['fieldName' => 'Business Name']);
                } else {
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
                        $errors[] = __('This Business Name already exists. Please try another.');
                    }
                }
            }

            if (!$this->vendorHelper->isRemoved('vat', 'business')) {
                if (!\Zend_Validate::is($vendor->getVat(), 'NotEmpty')) {
                    $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'VAT Number']);
                } else {
                    if (!\Zend_Validate::is(
                        $vendor->getVat(),
                        'Regex',
                        ['pattern' => '/(^[a-zA-Z0-9]{10,20}$)/']
                    )) {
                        $errors[] = __(
                            'Allow only alpha numeric value without space, not allowed special character and minimum length 10 and maximum length 20 for %fieldName.',
                            ['fieldName' => 'VAT Number']
                        );
                    }
                }
            }

            $_filesParams = $this->request->getFiles()->toArray();
            if (!$this->vendorHelper->isRemoved('vat_doc', 'business')) {
                if (!\Zend_Validate::is(
                    $_filesParams['vat_doc']['name'],
                    'NotEmpty'
                ) && empty($vendor->getVatDoc())) {
                    $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'VAT Document']);
                } else {
                    $fileTypes = ['jpg','jpeg','png'];
                    if (array_key_exists('vat_doc', $_filesParams) && $_filesParams['vat_doc']['name']) {
                        $path_parts = $this->_file->getPathInfo($_filesParams['vat_doc']['name']);
                        $extension = $path_parts['extension'];
                        if (!in_array($extension, $fileTypes)) {
                            $errors[] = __(
                                'Please use following file types (JPG,JPEG,PNG) only for %fieldName.',
                                ['fieldName' => 'VAT Document']
                            );
                        }
                    }
                    if ($_filesParams['vat_doc']['size'] > VendorModel::DEFAULT_IMAGE_SIZE) {
                        $errors[] = __(
                            'File size should be less than mentioned size for %fieldName.',
                            ['fieldName' => 'VAT Document']
                        );
                    }
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('vat_doc', 'business')) {
            if (!empty($vendor->getOtherMarketplaceProfile())) {
                if (!\Zend_Validate::is(
                    $vendor->getOtherMarketplaceProfile(),
                    'Regex',
                    ['pattern' => '/^(?:(?:https?|ftp):\/\/|www\.)[-A-Za-z0-9+&@#\/%?=~_|!:,.;]*[-A-Za-z0-9+&@#\/%=~_|]{0,150}$/']
                )) {
                    $errors[] = __(
                        'Please enter valid site URL only and character limit is 150 for %fieldName.',
                        ['fieldName' => 'Other Marketplace URL']
                    );
                }
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     *
     * @param array $postData
     * @param array $errors
     * @param boolean $returnFirstError
     * @param boolean $isVendorEdit
     * @param boolean $isAdminArea
     * @return array|null
     * @throws \Zend_Validate_Exception
     */
    public function validateBankingDetails(
        $postData = [],
        $errors = [],
        $returnFirstError = false,
        $isVendorEdit = false,
        $isAdminArea = false
    ) {
        if (!$this->vendorHelper->getConfigValue(VendorModel::IS_ENABLED_BANKING_DETAILS_XML_PATH)) {
            return $errors;
        }
        $isBankInfoOptional = $this->vendorHelper->getConfigValue(VendorModel::IS_BANK_DETAILS_OPTIONAL_XML_PATH);

        if (is_array($postData)) {
            $vendor = new \Magento\Framework\DataObject();
            $vendor->setData($postData);
        } else {
            $vendor = $postData;
        }

        if (!\Zend_Validate::is($vendor->getBankAccountName(), 'NotEmpty')) {
            if (!$isBankInfoOptional) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'Bank Account Name']);
            }
        } else {
            if (!\Zend_Validate::is(
                $vendor->getBankAccountName(),
                'Regex',
                ['pattern' => '/^.{1,150}$/']
            )) {
                $errors[] = __('Character limit is 150 for %fieldName.', ['fieldName' => 'Bank Account Name']);
            }
        }

        if (!\Zend_Validate::is($vendor->getBankAccountNumber(), 'NotEmpty')) {
            if (!$isBankInfoOptional) {
                $errors[] = __(
                    '%fieldName is required. Enter and try again.',
                    ['fieldName' => 'Bank Account Number']
                );
            }
        } else {
            if (!\Zend_Validate::is(
                $vendor->getBankAccountNumber(),
                'Regex',
                ['pattern' => '/(^\d{10,20}$)/']
            )) {
                $errors[] = __(
                    'Only allow numeric value, not allowed special character and space, character limit  min. input 10 and max 20 for %fieldName.',
                    ['fieldName' => 'Bank Account Number']
                );
            }
        }

        if (!$isAdminArea) {
            /* Skip validating in admin area. */
            if (!\Zend_Validate::is($vendor->getCbankAccountNumber(), 'NotEmpty')) {
                if (!$isBankInfoOptional) {
                    $errors[] = __(
                        '%fieldName is required. Enter and try again.',
                        ['fieldName' => 'Confirm Bank Account Number']
                    );
                }
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getCbankAccountNumber(),
                    'Regex',
                    ['pattern' => '/(^\d{10,20}$)/']
                )) {
                    $errors[] = __(
                        'Only allow numeric value, not allowed special character and space, character limit  min. input 10 and max 20 for %fieldName.',
                        ['fieldName' => 'Bank Account Number']
                    );
                } elseif ($vendor->getBankAccountNumber() != $vendor->getCbankAccountNumber()) {
                    $errors[] = __('Account Number and the Re-type Account Number field does not the have same value.');
                }
            }
        }

        if (!\Zend_Validate::is($vendor->getBankName(), 'NotEmpty')) {
            if (!$isBankInfoOptional) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'Bank Name']);
            }
        } else {
            if (!\Zend_Validate::is($vendor->getBankName(), 'Regex', ['pattern' => '/^.{1,150}$/'])) {
                $errors[] = __('Character limit is 150 for %fieldName.', ['fieldName' => 'Bank Name']);
            }
        }

        if (!\Zend_Validate::is($vendor->getIfsc(), 'NotEmpty')) {
            if (!$isBankInfoOptional) {
                $errors[] = __('%fieldName is required. Enter and try again.', ['fieldName' => 'IFSC code']);
            }
        } else {
            if (!\Zend_Validate::is(
                $vendor->getIfsc(),
                'Regex',
                ['pattern' => '/^[A-Za-z]{4}[0-9]{7}$/']
            )) {
                $errors[] = __('%fieldName is Invalid', ['fieldName' => 'IFSC code']);
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     *
     * @param array|object $postData
     * @param array $errors
     * @param boolean $returnFirstError
     * @param boolean $isVendorEdit
     * @return array|null
     * @throws \Zend_Validate_Exception
     */
    public function validateShippingDetails($postData, $errors = [], $returnFirstError = false, $isVendorEdit = false)
    {
        if (is_array($postData)) {
            $vendor = new \Magento\Framework\DataObject();
            $vendor->setData($postData);
        } else {
            $vendor = $postData;
        }

        if (!$this->vendorHelper->isRemoved('pickup_address1', 'shipping')) {
            /* Using $vendor->getData('pickup_address1') instead of
             $vendor->getPickupAddress1() as it is returning null due to number suffix.*/
            if (!\Zend_Validate::is($vendor->getData('pickup_address1'), 'NotEmpty')) {
                $errors[] = __(
                    '%fieldName is required. Enter and try again.',
                    ['fieldName' => 'Address line 1 of Pickup and Shipping Information']
                );
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getData('pickup_address1'),
                    'Regex',
                    ['pattern' => '/^.{1,150}$/']
                )) {
                    $errors[] = __(
                        'Character limit is 150 for %fieldName.',
                        ['fieldName' => 'Address line 1 of Pickup and Shipping Information']
                    );
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('pickup_address2', 'shipping')) {
            /* Using $vendor->getData('pickup_address2') instead of
             $vendor->getPickupAddress2() as it is returning null due to number suffix.*/
            if (!empty($vendor->getData('pickup_address2'))) {
                if (!\Zend_Validate::is(
                    $vendor->getData('pickup_address2'),
                    'Regex',
                    ['pattern' => '/^.{1,150}$/']
                )) {
                    $errors[] = __(
                        'Character limit is 150 for %fieldName.',
                        ['fieldName' => 'Address line 2 of Pickup and Shipping Information']
                    );
                }
            }
        }

        if (!\Zend_Validate::is($vendor->getPickupCountryId(), 'NotEmpty')) {
            if (!$this->vendorHelper->isRemoved('pickup_country_id', 'shipping')) {
                $errors[] = __(
                    '"%fieldName" is required. Enter and try again.',
                    ['fieldName' => 'Country of Pickup and Shipping Information']
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
                        $errors[] = __(
                            '%fieldName is required. Enter and try again.',
                            ['fieldName' => 'Region of Pickup and Shipping Information']
                        );
                    }
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('pickup_city', 'shipping')) {
            if (!\Zend_Validate::is($vendor->getPickupCity(), 'NotEmpty')) {
                $errors[] = __(
                    '%fieldName is required. Enter and try again.',
                    ['fieldName' => 'City of Pickup and Shipping Information']
                );
            } else {
                if (!\Zend_Validate::is(
                    $vendor->getPickupCity(),
                    'Regex',
                    ['pattern' => '/^[a-zA-Z ]{1,50}$/']
                )) {
                    $errors[] = __(
                        'Please use less than 50 characters and only alphabets and space are allowed for %fieldName.',
                        ['fieldName' => 'City of Pickup and Shipping Information']
                    );
                }
            }
        }

        if (!$this->vendorHelper->isRemoved('pickup_pincode', 'shipping')) {
            if (!\Zend_Validate::is($vendor->getPickupPincode(), 'NotEmpty')) {
                $errors[] = __(
                    '%fieldName is required. Enter and try again.',
                    ['fieldName' => 'Pincode of Pickup and Shipping Information']
                );
            } else {
                $postCodeValidateStatus = $this->postCodeValidator->validate(
                    $vendor->getPickupPincode(),
                    $vendor->getPickupCountryId()
                );
                if (!$postCodeValidateStatus) {
                    $errors[] = __('Invalid Pickup and Shipping Post Code');
                }
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     *
     * @param array $postData
     * @param boolean $returnFirstError
     * @return array|null
     * @throws NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    public function validateBusinessInfo($postData = [], $returnFirstError = false)
    {
        $errors = [];
        $errors = $this->validateBusinessDetails($postData, $errors, $returnFirstError);
        $errors = $this->validateBankingDetails($postData, $errors, $returnFirstError);
        $errors = $this->validateShippingDetails($postData, $errors, $returnFirstError);

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }

    /**
     * Whole business logic of Edit Vendor Account As per service contract.
     **/
    public function editVendor()
    {
        $this->_initPostCheck();

        $postData = $this->request->getParams();
        $section = $postData['section'];
        $this->vendor = $this->authSession->getUser();

        try {
            $vendorWebsiteDataValidate = true;
            $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData($this->vendor->getVendorId());
            $error = $this->_processSection($section, $vendorWebsite, $this->vendor);
            if ($error) {
                return $error;
            }

            $vendorWebsiteDataValidate = $this->validateEdit($section, $vendorWebsite, $this->vendor);
            if (is_array($vendorWebsiteDataValidate) && count($vendorWebsiteDataValidate) > 0) {
                $result['redirect_url'] = $this->url->getUrl('rbvendor/account');
                $result['message']['type'] = 'error';
                $result['message']['data'] = implode(' ', $vendorWebsiteDataValidate);
                return $this->jsonHelper->jsonEncode($result);
            }

            if ($this->vendor->getStatus() === VendorStatus::VENDOR_STATUS_DISAPPROVED) {
                $vendorWebsite->setStatus(VendorStatus::VENDOR_STATUS_PENDING);
            }
            $eventParams = ['account_controller' => null, 'vendor' => $this->vendor,'request' => $postData];
            $this->eventManager->dispatch('vendor_update_before', $eventParams);
            $this->vendorRepository->save($this->vendor);
            $vendorWebsite->save();
            $eventParams['vendor'] = $vendorWebsite;
            $this->eventManager->dispatch('vendor_update_success', $eventParams);
            $result['message']['type'] = 'success';
            $result['message']['data'] =__('Information has been updated successfully.');
            $url = $this->url->getUrl('*/*/index', ['section' => $this->_activeSection, '_secure' => true]);
            $result['redirect_url'] = $url;
            /*return $this->jsonHelper->jsonEncode($result);*/
        } catch (\Magento\Framework\Model\Exception $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
        } catch (\RuntimeException $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
        } catch (LocalizedException $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
        } catch (\Exception $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Something went wrong while updating the profile.');
        }
        if ($this->_activeSection == "login-info") {
            $url = $this->url->getUrl('rbvendor/account/logout');
        } else {
            $url = $this->url->getUrl('*/*/index', ['section' => $this->_activeSection, '_secure' => true]);
        }
        $result['redirect_url'] = $url;
        return $this->jsonHelper->jsonEncode($result);
    }

    /**
     * @param $activePart
     * @param $vendorWebsite
     * @param $vendor
     * @return array|null
     * @throws NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    public function validateEdit($activePart, $vendorWebsite, $vendor)
    {
        if ($activePart && $activePart == 'vendorinfo') {
            return $this->validateVendorInfo($vendorWebsite, false, true);
        }

        if ($activePart && $activePart == 'shippinginfo') {
            return $this->validateShippingDetails($vendorWebsite, [], false, true);
        }

        if ($activePart && $activePart == 'businessinfo') {
            /* Just for validation. */
            $vendorWebsite->setOtherMarketplaceProfile($vendor->getOtherMarketplaceProfile());
            /* Just for validation. */
            return $this->validateBusinessDetails($vendorWebsite, [], false, true);
        }

        if ($activePart && $activePart == 'bankinfo') {
            return $this->validateBankingDetails($vendorWebsite, [], false, true);
        }
    }

    /**
     * @return boolean
     */
    private function _initPostCheck()
    {
        if (!$this->authSession->isLoggedIn()) {
            $result['redirect_url'] = $this->url->getUrl('rbvendor');
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Session has been timeout, Please login');
            return $this->jsonHelper->jsonEncode($result);
        }
        if (!$this->request->getParams()) {
            $result['redirect_url'] = $this->url->getUrl('rbvendor/account');
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Please fill up all data');
            return $this->jsonHelper->jsonEncode($result);
        }
        return false;
    }

    /**
     * @param $section
     * @return bool
     */
    protected function _processSection($section, $vendorWebsite, $vendor)
    {
        switch ($section) {
            case 'vendorinfo':
                $this->processVendorInfo($vendorWebsite);
                break;
            case 'businessinfo':
                return $this->processBusinessInfo($vendorWebsite, $vendor);
            case 'logininfo':
                return $this->processLoginInfo();
            case 'statusinfo':
                return $this->processStatusInfo($vendorWebsite);
            case 'shippinginfo':
                $this->processShippingInfo($vendorWebsite);
                break;
            case 'bankinfo':
                $this->processBankInfo($vendorWebsite);
                break;
            case 'businesslogo':
                    $this->processBusinessLogo($vendorWebsite);
                break;
        }
        return false;
    }

    /**
     * process vendor information
     * @param $vendorWebsite
     */
    public function processVendorInfo($vendorWebsite)
    {
        $vendorFields = ['name','address1', 'address2', 'country_id', 'region', 'region_id', 'city', 'pincode'];
        $this->_activeSection = "vendor-info";
        $this->processVendorFieldsForEdit($vendorFields, $vendorWebsite);
    }

    /**
     * set vendor profile data for particular section
     * @param array
     */
    public function processVendorFieldsForEdit($vendorFields, $vendorWebsite)
    {
        $postData = $this->request->getParams();
        foreach ($postData as $key => $vendorDataItem) {
            if (in_array($key, $this->excludeFields) || !in_array($key, $vendorFields)) {
                continue;
            }
            $vendorWebsite->setData($key, $vendorDataItem);
        }
    }

    /**
     * process business information
     */
    public function processBusinessInfo($vendorWebsite, $vendor)
    {
        $imageError = $this->processFilesEdit($vendorWebsite);
        if (!empty($imageError['error'])) {
            $url = $this->url->getUrl('*/*/index', ['section' => $this->_activeSection, '_secure' => true]);
            $result['redirect_url'] = $url;
            $result['message']['type'] = 'error';
            $result['message']['data'] = $imageError['error'];
            return $this->jsonHelper->jsonEncode($result);
        }
        $this->_activeSection = "business-info";
        $vendorFields = ['other_marketplace_profile'];
        $vendorWebsiteFields = ['business_name', 'logo','vat'];

        $postData = $this->request->getParams();
        foreach ($vendorFields as $field) {
            if (array_key_exists($field, $postData)) {
                $vendor->setData($field, $postData[$field]);
            }
        }

        $this->processVendorFieldsForEdit($vendorWebsiteFields, $vendorWebsite);
        return false;
    }

    /**
     * @param $vendorWebsite
     * @return array
     */
    protected function processFilesEdit($vendorWebsite)
    {
        $fileTypes = ['pdf','doc','docx'];
        try {
            $postData = $this->request->getParams();

            $_filesParams = $this->request->getFiles()->toArray();
            if (array_key_exists('vat_doc', $_filesParams) && !empty($_filesParams['vat_doc']['name'])) {
                if ($_filesParams['vat_doc']['size'] > VendorModel::DEFAULT_IMAGE_SIZE) {
                    throw new \Exception(__('File size should be less than mentioned size for Vat Document.'));
                }
                /* Remove existing file before uploading new one. */
                $this->deleteFile($vendorWebsite->getVatDoc(), 'vendor/vat_doc');
                /* Remove existing file before uploading new one. */
                $vat_doc = $this->uploadModel->uploadFileAndGetName(
                    'vat_doc',
                    $this->imageModel->getBaseDir('vendor/vat_doc'),
                    $postData
                );
                $vendorWebsite->setVatDoc($vat_doc);
            } else {
                if (isset($postData['vendor']['vat_doc']['value'])) {
                    $vendorWebsite->setVatDoc($postData['vendor']['vat_doc']['value']);
                }
            }

            return $vendorWebsite;
        } catch (\Exception $ex) {
            return ['error' => $ex->getMessage()];
        }
    }

    /**
     * process login information
     */
    protected function processLoginInfo()
    {
        $this->_activeSection = "login-info";
        try {
            $error = $this->changeVendorPassword();
            if ($error) {
                $result = $this->jsonHelper->jsonDecode($error);
                if (is_array($result)) {
                    $result['redirect_url'] = $this->url->getUrl(
                        '*/*/index',
                        ['section' => $this->_activeSection, '_secure' => true]
                    );
                    return $this->jsonHelper->jsonEncode($result);
                } else {
                    $result = [];
                    $result['section'] = 'login-info';
                    $result['message']['type'] = 'success';
                    $result['message']['data'] = __(
                        'Your password has been successfully changed. You may login with your new password.'
                    );
                    $result['redirect_url'] = $this->url->getUrl('rbvendor/account/logout');
                    return $this->jsonHelper->jsonEncode($result);
                }
            }
        } catch (\Exception $e) {
            $result = [];
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
            return $this->jsonHelper->jsonEncode($result);
        }
    }

    /**
     * Change vendor password
     * @return $this|string
     */
    protected function changeVendorPassword()
    {
        $postData = $this->request->getParams();
        $currPass = $postData['current_password'];
        $newPass = $postData['password'];
        $confPass = $postData['cpassword'];
        $email = $this->vendor->getEmail();
        $result = [];

        if (!strlen($currPass)) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Please enter current password.');
            return $this->jsonHelper->jsonEncode($result);
        }

        if (!strlen($newPass)) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Please enter new password.');
            return $this->jsonHelper->jsonEncode($result);
        }

        if (!strlen($confPass)) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Please enter confirm password.');
            return $this->jsonHelper->jsonEncode($result);
        }

        if ($newPass !== $confPass) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Please your new password.');
            return $this->jsonHelper->jsonEncode($result);
        }

        try {
            return $this->changePassword($email, $currPass, $newPass);
        } catch (InvalidEmailOrPasswordException $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
            return $this->jsonHelper->jsonEncode($result);
        } catch (AuthenticationException $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = $e->getMessage();
            return $this->jsonHelper->jsonEncode($result);
        } catch (\Exception $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Something went wrong while changing the password.');
            return $this->jsonHelper->jsonEncode($result);
        }
        return true;
    }

    /**
     * process vacation mode
     * @param $vendorWebsite
     * @return string
     */
    protected function processStatusInfo($vendorWebsite)
    {
        $this->_activeSection = "status-info";
        $postData = $this->request->getParams();

        $url = $this->url->getUrl('*/*/index', ['section' => $this->_activeSection, '_secure' => true]);
        $currentDate = date('m/d/y');
        $fromDate = $postData['vacation_from_date'];
        $toDate = $postData['vacation_to_date'];
        $reqType = $postData['request_type'];

        try {
            if ($this->isItValidDate($fromDate) == true && $this->isItValidDate($toDate) == true && $reqType == '1') {
                if (($fromDate > $currentDate || $fromDate == $currentDate) &&
                    ($toDate >= $fromDate || $toDate == $currentDate)) {
                    $vendorFields = ['vacation_from_date', 'vacation_to_date', 'vacation_message','request_type'];
                    $this->processVendorFields($vendorFields, $vendorWebsite);
                    $vendorWebsite->setData('vacation_request_status', RequestStatuses::VENDOR_REQUEST_STATUS_PENDING);
                    $vendorWebsite->save();
                    $this->vendorRepository->cleanCache();
                    $vendor = $this->vendorRepository->getById($vendorWebsite->getVendorId());
                    $this->sendVendorStatusRequest($vendor, $postData['request_type']);
                    $result['message']['type'] = 'success';
                    $result['message']['data'] = __('Your status change request submitted successfully.');
                    $result['redirect_url'] = $url;
                } else {
                    $result['message']['type'] = 'error';
                    $result['message']['data'] = __('Date Invalid. Please try again!');
                    $result['redirect_url'] = $url;
                }
            } elseif ($reqType == '2') {
                $vendorFields = ['vacation_from_date', 'vacation_to_date', 'vacation_message','request_type'];
                $this->processVendorFields($vendorFields, $vendorWebsite);
                $vendorWebsite->setData('vacation_request_status', RequestStatuses::VENDOR_REQUEST_STATUS_PENDING);
                $vendorWebsite->save();
                $this->vendorRepository->cleanCache();
                $vendor = $this->vendorRepository->getById($vendorWebsite->getVendorId());
                $this->sendVendorStatusRequest($vendor, $postData['request_type']);
                $result['message']['type'] = 'success';
                $result['message']['data'] = __('Your status change request submitted successfully.');
                $result['redirect_url'] = $url;
            } else {
                $result['message']['type'] = 'error';
                $result['message']['data'] = __('Date Invalid. Please try again!');
                $result['redirect_url'] = $url;
            }
        } catch (\Exception $e) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = __('Unable to submit status change request.');
            $result['redirect_url'] = $url;
        }
        return $this->jsonHelper->jsonEncode($result);
    }

    /**
     * @param $date
     * @return bool
     */
    public function isItValidDate($date)
    {
        if (preg_match("/^(\d{2})\/(\d{2})\/(\d{4})$/", $date, $matches)) {
            if (checkdate($matches[1], $matches[2], $matches[3])) {
                return true;
            }
        }
    }

    /**
     * process selling categories
     */
    protected function processCategoryInfo()
    {
        $vendorFields = ['category', 'other_marketplace_profile'];
        $this->_activeSection = "category-info";
        $this->processVendorFields($vendorFields);
        $this->vendor->setCategoriesIds($this->vendor->getCategory());
    }

    /**
     * process shipping information
     * @param $vendorWebsite
     */
    protected function processShippingInfo($vendorWebsite)
    {
        $vendorFields = [
            'pickup_address1',
            'pickup_address2',
            'pickup_country_id',
            'pickup_region',
            'pickup_region_id',
            'pickup_city',
            'pickup_pincode'
        ];
        $this->_activeSection = "shipping-info";
        $this->processVendorFields($vendorFields, $vendorWebsite);
    }

    /**
     * process bank information
     */
    protected function processBankInfo($vendorWebsite)
    {
        $imageError = '';
        if ($imageError != '') {
            $this->messageManager->addError(__($imageError));
            $result['message']['type'] = 'error';
            $result['message']['data'] = $imageError;
            $url = $this->urlModel->getUrl('*/*/index', ['section' => $this->_activeSection, '_secure' => true]);
            $result['redirect_url'] = $url;
            return $this->jsonHelper->jsonEncode($result);
        }
        $vendorFields = ['bank_account_name', 'bank_account_number', 'bank_name', 'ifsc'];
        $this->_activeSection = "bank-info";
        $this->processVendorFields($vendorFields, $vendorWebsite);
    }

    /**
     * @param $vendorWebsite
     * @return mixed
     */
    protected function processBusinessLogo($vendorWebsite)
    {
        try {
            $postData = $this->request->getParams();
            if (!(isset($postData['logo']) && $postData['logo'] != '')) {
                if (isset($postData['vendor']['logo']['value'])) {
                    $vendorWebsite->setLogo($postData['vendor']['logo']['value']);
                }
            }
            return $vendorWebsite;
        } catch (\Exception $ex) {
            $result['message']['type'] = 'error';
            $result['message']['data'] = $ex->getMessage();
            return $result;
        }
    }

    /**
     * @return \Magento\User\Model\User|null
     */
    public function dashboardVendor()
    {
        if ($this->authSession->isLoggedIn()) {
            $this->_vendorData = $this->authSession->getUser();
        }
        return $this->_vendorData;
    }

    /**
     * @param $section
     * @return $this
     */
    public function setActiveSection($section)
    {
        $this->_activeSection = $section;
        return $this;
    }

    /**
     * @param $postData
     * @param $vendorWebsite
     * @return array
     */
    protected function processFiles($postData, $vendorWebsite)
    {
        $fileTypes = [];
        $_filesParams = $this->request->getFiles()->toArray();
        try {
            if (array_key_exists('logo', $_filesParams) && !empty($_filesParams['logo']['name'])) {
                if ($_filesParams['logo']['size'] > VendorModel::DEFAULT_IMAGE_SIZE) {
                    throw new \Exception(__('File size should be less than mentioned size for Company\'s Logo.'));
                }

                $logo = $this->uploadModel->uploadFileAndGetName(
                    'logo',
                    $this->imageModel->getBaseDir('vendor/logo'),
                    $postData
                );
                $vendorWebsite->setLogo($logo);
            } elseif (array_key_exists('logo', $_filesParams) && empty($_filesParams['logo']['name'])) {
                /* Generate Avatar Place Holder and store as logo.*/
                $name = $postData['name'];

                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                $imageName =  "/" . substr(str_shuffle($permitted_chars), 0, 10) . ".png";
                $imagePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                        ->getAbsolutePath() . "vendor/logo/" . $imageName;

                $filePath = "/vendor/logo/";
                $verifyPath = $this->_directoryList->getPath('media') . $filePath;
                if (!is_dir($verifyPath)) {
                    $ioAdapter = $this->_file;
                    $ioAdapter->mkdir($verifyPath, 0775);
                }

                $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
                $image = $avatar->size(200)->name($name)->rounded()->generate();
                $image->save($imagePath);
                $vendorWebsite->setLogo($imageName);
            }
            if (array_key_exists('vat_doc', $_filesParams) && !empty($_filesParams['vat_doc']['name'])) {
                if ($_filesParams['vat_doc']['size'] > VendorModel::DEFAULT_IMAGE_SIZE) {
                    throw new \Exception(__('File size should be less than mentioned size for Vat Document.'));
                }
                $vat_doc = $this->uploadModel->uploadFileAndGetName(
                    'vat_doc',
                    $this->imageModel->getBaseDir('vendor/vat_doc'),
                    $postData,
                    $fileTypes
                );
                $vendorWebsite->setVatDoc($vat_doc);
            }

            return $vendorWebsite;
        } catch (\Exception $ex) {
            return ['error' => $ex->getMessage()];
        }
    }

    /**
     * @param $postData
     * @param $vendorWebsite
     * @return array
     */
    protected function processFilesAdmin($postData, $vendorWebsite)
    {
        $fileTypes = [];
        $_filesParams = $this->request->getFiles()->toArray();
        try {
            if (!empty($_filesParams['logo']) && !empty($_filesParams['logo']['name'])) {
                if (!file_exists($_filesParams['logo']['tmp_name'])) {
                    /* Same file when used in loop, tmp file gets removed and throws exception.*/
                    ($this->registry->registry('tmp_logo')) ?
                        $vendorWebsite->setLogo($this->registry->registry('tmp_logo')) : '';
                } else {
                    if ($_filesParams['logo']['size'] > VendorModel::DEFAULT_IMAGE_SIZE) {
                        throw new \Exception(__('File size should be less than mentioned size for Company\'s Logo.'));
                    }

                    $logo = $this->uploadModel->uploadFileAndGetName(
                        'logo',
                        $this->imageModel->getBaseDir('vendor/logo'),
                        $postData
                    );
                    $vendorWebsite->setLogo($logo);
                    $this->registry->register('tmp_logo', $logo);
                }
            } elseif ((!empty($_filesParams['logo'])) && (empty($_filesParams['logo']['name']))) {
                $name = $postData['name'];
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                $imageName =  "/" . substr(str_shuffle($permitted_chars), 0, 10) . ".png";
                $imagePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                        ->getAbsolutePath() . "vendor/logo/" . $imageName;
                $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
                $image = $avatar->name($name)->rounded()->generate();
                $image->save($imagePath);
                $vendorWebsite->setLogo($imageName);
            }

            if (array_key_exists('vat_doc', $_filesParams) && !empty($_filesParams['vat_doc']['name'])) {
                if (!file_exists($_filesParams['vat_doc']['tmp_name'])) {
                    /* Same file when used in loop, tmp file gets removed and throws exception.*/
                    ($this->registry->registry('tmp_vat_doc')) ?
                        $vendorWebsite->setVatDoc($this->registry->registry('tmp_vat_doc')) : '';
                } else {
                    if ($_filesParams['vat_doc']['size'] > VendorModel::DEFAULT_IMAGE_SIZE) {
                        throw new \Exception(__('File size should be less than mentioned size for Vat Document.'));
                    }
                    $vat_doc = $this->uploadModel->uploadFileAndGetName(
                        'vat_doc',
                        $this->imageModel->getBaseDir('vendor/vat_doc'),
                        $postData,
                        $fileTypes
                    );
                    $vendorWebsite->setVatDoc($vat_doc);
                    $this->registry->register('tmp_vat_doc', $vat_doc);
                }
            }

            return $vendorWebsite;
        } catch (\Exception $ex) {
            return ['error' => $ex->getMessage()];
        }
    }

    /**
     *
     */
    protected function getVendorDashboardMenu()
    {
        if ($this->authSession->getUser() == null) {
            $vendorId = $this->userContext->getUserId();
        } else {
            $vendorId = $this->authSession->getUser()->getId();
        }

        $roleId = $this->vendorAcl->getLoggedVendorRoleId($vendorId);
        if (!is_int($roleId)) {
            $roleId = $roleId->getRoleId();
        }
        $allowedResource = $this->vendorAcl->getAllowedResourcesByRole($roleId);
        /*if (is_array($allowedResource)) {}*/
    }

    /**
     * Delete file.
     *
     * @param string $fileName
     * @param string $subDir
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteFile($fileName = '', $subDir = '')
    {
        $this->vendorHelper->deleteFile($fileName, $subDir);
    }

    /**
     * @return boolean
     * @throws LocalizedException
     */
    public function confirmVendorAccount()
    {
        $vendor  = $this->getVendorByEmailVerificationCode();
        if ($vendor && $vendor->getId()) {
            $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData($vendor->getId());
            if ($vendorWebsite->getId()) {
                $vendorWebsite->setData('email_verified', 1)->save();
                return true;
            }
        }
        return false;
    }
}
