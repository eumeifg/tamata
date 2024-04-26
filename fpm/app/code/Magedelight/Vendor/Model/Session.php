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

use Magedelight\Vendor\Api\Data\VendorInterface as VendorData;
use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magedelight\Vendor\Model\Config\Share;
use Magedelight\Vendor\Model\ResourceModel\Vendor as ResourceVendor;

/**
 * Vendor session model
 * @method string getNoReferer()
 * @method array getFormData() get form Data
 * @method $this setFormData(array $params) set form Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Session extends \Magento\Framework\Session\SessionManager
{

    /**
     * Vendor object
     *
     * @var VendorData
     */
    protected $_vendor;

    /**
     * @var ResourceVendor
     */
    protected $_vendorResource;

    /**
     * Vendor model
     *
     * @var Vendor
     */
    protected $_vendorModel;

    /**
     * Flag with vendor id validations result
     *
     * @var bool|null
     */
    protected $_isVendorIdChecked = null;

    /**
     * Vendor URL
     *
     * @var \Magedelight\Backend\Model\Url
     */
    protected $_vendorUrl;

    /**
     * Core url
     *
     * @var \Magento\Framework\Url\Helper\Data|null
     */
    protected $_coreUrl = null;

    /**
     * @var Share
     */
    protected $_configShare;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var  VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param Share $configShare
     * @param \Magento\Framework\Url\Helper\Data $coreUrl
     * @param \Magedelight\Backend\Model\Url $vendorUrl
     * @param ResourceVendor $vendorResource
     * @param VendorFactory $vendorFactory
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param VendorRepositoryInterface $vendorRepository
     * @param \Magento\Framework\App\Response\Http $response
     * @throws \Magento\Framework\Exception\SessionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        Config\Share $configShare,
        \Magento\Framework\Url\Helper\Data $coreUrl,
        \Magedelight\Backend\Model\Url $vendorUrl,
        ResourceVendor $vendorResource,
        VendorFactory $vendorFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Http\Context $httpContext,
        VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\App\Response\Http $response
    ) {
        $this->_coreUrl = $coreUrl;
        $this->_vendorUrl = $vendorUrl;
        $this->_configShare = $configShare;
        $this->_vendorResource = $vendorResource;
        $this->_vendorFactory = $vendorFactory;
        $this->_urlFactory = $urlFactory;
        $this->_session = $session;
        $this->vendorRepository = $vendorRepository;
        $this->_eventManager = $eventManager;
        $this->_httpContext = $httpContext;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
        $this->response = $response;
        $this->_eventManager->dispatch('vendor_session_init', ['vendor_session' => $this]);
    }

    /**
     * Retrieve vendor sharing configuration model
     *
     * @return Share
     */
    public function getVendorConfigShare()
    {
        return $this->_configShare;
    }

    /**
     * Set vendor object and setting vendor id in session
     *
     * @param   VendorData $vendor
     * @return  $this
     */
    public function setVendorData(VendorData $vendor)
    {
        $this->_vendor = $vendor;
        if ($vendor === null) {
            $this->setVendorId(null);
        } else {
            $this->setVendorId($vendor->getId());
        }
        return $this;
    }

    /**
     * Retrieve vendor model object
     *
     * @return VendorData
     */
    public function getVendorData()
    {
        if (!$this->_vendor instanceof VendorData && $this->getVendorId()) {
            $this->_vendor = $this->vendorRepository->getById($this->getVendorId());
        }

        return $this->_vendor;
    }

    /**
     * Returns Vendor data object with the vendor information
     *
     * @return VendorData
     */
    public function getVendorDataObject()
    {
        /* TODO refactor this after all usages of the setVendor is refactored */
        return $this->getVendor()->getDataModel();
    }

    /**
     * Set Vendor data object with the vendor information
     *
     * @param VendorData $vendorData
     * @return $this
     */
    public function setVendorDataObject(VendorData $vendorData)
    {
        $this->setId($vendorData->getId());
        $this->getVendor()->updateData($vendorData);
        return $this;
    }

    /**
     * Set vendor model and the vendor id in session
     *
     * @param   Vendor $vendorModel
     * @return  $this
     * use setVendorId() instead
     */
    public function setVendor(Vendor $vendorModel)
    {
        $this->_vendorModel = $vendorModel;
        $this->setVendorId($vendorModel->getId());
//        if (!$vendorModel->isConfirmationRequired() && $vendorModel->getConfirmation()) {
//            $vendorModel->setConfirmation(null)->save();
//        }

        /**
         * The next line is a workaround.
         * It is used to distinguish users that are logged in from user data set via methods similar to setVendorId()
         */
        $this->unsIsVendorEmulated();

        return $this;
    }

    /**
     * Retrieve vendor model object
     *
     * @return Vendor
     * use getVendorId() instead
     */
    public function getVendor()
    {
        if ($this->_vendorModel === null) {
            $this->_vendorModel = $this->_vendorFactory->create()->load($this->getVendorId());
        }

        return $this->_vendorModel;
    }

    /**
     * Set vendor id
     *
     * @param int|null $id
     * @return $this
     */
    public function setVendorId($id)
    {
        $this->storage->setData('vendor_id', $id);
        return $this;
    }

    /**
     * Retrieve vendor id from current session
     *
     * @api
     * @return int|null
     */
    public function getVendorId()
    {
        if ($this->storage->getData('vendor_id')) {
            $vendor = $this->_vendorFactory->create()->load($this->storage->getData('vendor_id'));

            if ($vendor->getIsUser()) {
                $user = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magedelight\User\Model\User::class);
                return $user->getUserParentId($vendor->getId());
            }

            return $this->storage->getData('vendor_id');
        }
        return null;
    }

    /**
     * Retrieve vendor id from current session
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getVendorId();
    }

    /**
     * Set vendor id
     *
     * @param int|null $vendorId
     * @return $this
     */
    public function setId($vendorId)
    {
        return $this->setVendorId($vendorId);
    }

    /**
     * Checking vendor login status
     *
     * @api
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool) $this->getVendorId() && $this->checkVendorId($this->getId()) && !$this->getIsVendorEmulated();
    }

    /**
     * Check exists vendor (light check)
     *
     * @param int $vendorId
     * @return bool
     */
    public function checkVendorId($vendorId)
    {
        if ($this->_isVendorIdChecked === $vendorId) {
            return true;
        }

        try {
            $this->vendorRepository->getById($vendorId);
            $this->_isVendorIdChecked = $vendorId;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Vendor $vendor
     * @return $this
     */
    public function setVendorAsLoggedIn($vendor)
    {
        $this->setVendor($vendor);
        $this->_eventManager->dispatch('vendor_login', ['vendor' => $vendor]);
        $this->_eventManager->dispatch('vendor_data_object_login', ['vendor' => $this->getVendorDataObject()]);
        $this->regenerateId();
        return $this;
    }

    /**
     * @param VendorData $vendor
     * @return $this
     */
    public function setVendorDataAsLoggedIn($vendor)
    {
        $this->_httpContext->setValue(Context::CONTEXT_AUTH, true, false);
        $this->storage->setData('header_name', $vendor->getBusinessName());
        $this->setVendorData($vendor);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVendorDataAsLoggedIn()
    {
        return $this->storage->getData('vendor');
    }

    /**
     * Authorization vendor by identifier
     *
     * @api
     * @param   int $vendorId
     * @return  bool
     */
    public function loginById($vendorId)
    {
        try {
            $vendor = $this->vendorRepository->getById($vendorId);
            $this->setVendorDataAsLoggedIn($vendor);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Logout vendor
     *
     * @api
     * @return $this
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->_eventManager->dispatch('vendor_logout', ['vendor' => $this->getVendor()]);
            $this->_logout();
        }
        $this->_httpContext->unsValue(Context::CONTEXT_AUTH);
        return $this;
    }

    /**
     * Authenticate controller action by login vendor
     *
     * @param   bool|null $loginUrl
     * @return  bool
     */
    public function authenticate($loginUrl = null)
    {
        if ($this->isLoggedIn()) {
            return true;
        }

        $this->setBeforeAuthUrl($this->_createUrl()->getUrl('*/*/*', ['_current' => true]));

        if (isset($loginUrl)) {
            $this->response->setRedirect($loginUrl);
        } else {
            $arguments = $this->_vendorUrl->getLoginUrlParams();
            if ($this->_session->getCookieShouldBeReceived() && $this->_createUrl()->getUseSession()) {
                $arguments += [
                    '_query' => [
                        $this->sidResolver->getSessionIdQueryParam($this->_session) => $this->_session->getSessionId(),
                    ]
                ];
            }
            $this->response->setRedirect(
                $this->_createUrl()->getUrl(\Magedelight\Backend\Model\Url::ROUTE_VENDOR_LOGIN, $arguments)
            );
        }

        return false;
    }

    /**
     * Set auth url
     *
     * @param string $key
     * @param string $url
     * @return $this
     */
    protected function _setAuthUrl($key, $url)
    {
        $url = $this->_coreUrl->removeRequestParam($url, $this->sidResolver->getSessionIdQueryParam($this));
        // Add correct session ID to URL if needed
        $url = $this->_createUrl()->getRebuiltUrl($url);
        return $this->storage->setData($key, $url);
    }

    /**
     * Logout without dispatching event
     *
     * @return $this
     */
    protected function _logout()
    {
        $this->_vendor = null;
        $this->_vendorModel = null;
        $this->setVendorId(null);
        $this->destroy(['clear_storage' => false]);
        return $this;
    }

    /**
     * Set Before auth url
     *
     * @param string $url
     * @return $this
     */
    public function setBeforeAuthUrl($url)
    {
        return $this->_setAuthUrl('before_auth_url', $url);
    }

    /**
     * Set After auth url
     *
     * @param string $url
     * @return $this
     */
    public function setAfterAuthUrl($url)
    {
        return $this->_setAuthUrl('after_auth_url', $url);
    }

    /**
     * Reset core session hosts after reseting session ID
     *
     * @return $this
     */
    public function regenerateId()
    {
        parent::regenerateId();
        $this->_cleanHosts();
        return $this;
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }

    /**
     * Retrieve vendor id from current session
     *
     * @api
     * @return int|null
     */
    public function getUserId()
    {
        if ($this->storage->getData('vendor_id')) {
            return $this->storage->getData('vendor_id');
        }
        return null;
    }

    /**
     * Clear vendor model data.
     *
     * @return void
     */
    public function clearVendorData()
    {
        $this->_vendorModel = null;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isValidForPath($path)
    {
        return true;
    }
}
