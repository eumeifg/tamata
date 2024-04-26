<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Model\Auth;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magedelight\Vendor\Model\Vendor;
use Magedelight\Vendor\Api\Data\VendorInterface as VendorData;

/**
 * Seller Panel Auth session model
 *
 * @method \Magento\User\Model\User|null getUser()
 * @method \Magedelight\Backend\Model\Auth\Session setUser(\Magedelight\Vendor\Model\Vendor $value)
 * @method \Magento\Framework\Acl|null getAcl()
 * @method \Magedelight\Backend\Model\Auth\Session setAcl(\Magento\Framework\Acl $value)
 * @method int getUpdatedAt()
 * @method \Magedelight\Backend\Model\Auth\Session setUpdatedAt(int $value)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @todo implement solution that keeps is_first_visit flag in session during redirects
 */
class Session extends \Magento\Framework\Session\SessionManager implements \Magedelight\Backend\Model\Auth\StorageInterface
{

    /**
     * Admin session lifetime config path
     */
    const XML_PATH_SESSION_LIFETIME = 'vendor/security/session_lifetime';

    /**
     * Whether it is the first page after successful login
     *
     * @var boolean
     */
    protected $_isFirstAfterLogin;

    /**
     * Access Control List builder
     *
     * @var \Magento\Framework\Acl\Builder
     */
    protected $_aclBuilder;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $_sellerPanelUrl;

    /**
     * @var \Magedelight\Backend\App\ConfigInterface
     */
    protected $_config;
    
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Acl\Builder $aclBuilder
     * @param \Magedelight\Backend\Model\UrlInterface $sellerPanelUrl
     * @param \Magedelight\Backend\App\ConfigInterface $config
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
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Acl\Builder $aclBuilder,
        \Magedelight\Backend\Model\UrlInterface $sellerPanelUrl,
        \Magedelight\Backend\App\ConfigInterface $config,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->_config = $config;
        $this->_aclBuilder = $aclBuilder;
        $this->_sellerPanelUrl = $sellerPanelUrl;
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
    }

    /**
     * Refresh ACL resources stored in session
     *
     * @param  \Magedelight\User\Model\User $user
     * @return \Magedelight\Backend\Model\Auth\Session
     */
    public function refreshAcl($user = null)
    {
        if ($user === null) {
            $user = $this->getUser();
        }
        if (!$user) {
            return $this;
        }
        if (!$this->getAcl() || $user->getReloadAclFlag()) {
            $this->setAcl($this->_aclBuilder->getAcl());
        }
        if ($user->getReloadAclFlag()) {
            $user->unsetData('password');
            $user->setReloadAclFlag('0')->save();
        }
        return $this;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function isAllowed($resource, $privilege = null)
    {
        $user = $this->getUser();
        $acl = $this->getAcl();

        if ($user && $acl) {
            try {
                return $acl->isAllowed($user->getAclRole(), $resource, $privilege);
            } catch (\Exception $e) {
                try {
                    if (!$acl->has($resource)) {
                        return $acl->isAllowed($user->getAclRole(), null, $privilege);
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->getUser() && $this->getUser()->getId();
    }

    /**
     * Set session UpdatedAt to current time
     *
     * @return void
     */
    public function prolong()
    {
        $cookieValue = $this->cookieManager->getCookie($this->getName());
        if ($cookieValue) {
            $this->setUpdatedAt(time());
            $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath($this->sessionConfig->getCookiePath())
                ->setDomain($this->sessionConfig->getCookieDomain())
                ->setSecure($this->sessionConfig->getCookieSecure())
                ->setHttpOnly($this->sessionConfig->getCookieHttpOnly());
            $this->cookieManager->setPublicCookie($this->getName(), $cookieValue, $cookieMetadata);
        }
    }

    /**
     * Check if it is the first page after successful login
     *
     * @return bool
     */
    public function isFirstPageAfterLogin()
    {
        if ($this->_isFirstAfterLogin === null) {
            /* Set false in getData() to resolve api issue in vendor panel. Not getting vendor_id in session storage stops api execution.*/
            $this->_isFirstAfterLogin = $this->getData('is_first_visit', false);
        }
        return $this->_isFirstAfterLogin;
    }

    /**
     * Setter whether the current/next page should be treated as first page after login
     *
     * @param bool $value
     * @return \Magedelight\Backend\Model\Auth\Session
     */
    public function setIsFirstPageAfterLogin($value)
    {
        $this->_isFirstAfterLogin = (bool)$value;
        return $this->setIsFirstVisit($this->_isFirstAfterLogin);
    }

    /**
     * Process of configuring of current auth storage when login was performed
     *
     * @return \Magedelight\Backend\Model\Auth\Session
     */
    public function processLogin()
    {
        if ($this->getUser()) {
            $this->setVendorDataAsLoggedIn($this->getUser());
            $this->regenerateId();

            if ($this->_sellerPanelUrl->useSecretKey()) {
                $this->_sellerPanelUrl->renewSecretUrls();
            }

            $this->setIsFirstPageAfterLogin(true);
            $this->setAcl($this->_aclBuilder->getAcl());
            $this->setUpdatedAt(time());
        }
        return $this;
    }
    
    /**
     * @param VendorData $vendor
     * @return $this
     */
    public function setVendorDataAsLoggedIn($vendor)
    {
        $this->_httpContext->setValue(\Magedelight\Vendor\Model\Context::CONTEXT_AUTH, true, false);
        $this->storage->setData('header_name', $vendor->getBusinessName());
        $this->setVendorData($vendor);
        return $this;
    }
    
    /**
     * Set vendor object and setting vendor id in session
     *
     * @param   VendorData $vendor
     * @return  $this
     */
    public function setVendorData(VendorData $vendor)
    {
        if ($vendor === null) {
            $this->setVendorId(null);
        } else {
            $this->setVendorId($vendor->getId());
        }
        return $this;
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
    
    public function getVendorId()
    {
        return $this->storage->getData('vendor_id');
    }

    /**
     * Process of configuring of current auth storage when logout was performed
     *
     * @return \Magedelight\Backend\Model\Auth\Session
     */
    public function processLogout()
    {
        $this->destroy();
        return $this;
    }

    /**
     * Skip path validation in backend area
     *
     * @param string $path
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function isValidForPath($path)
    {
        return true;
    }
}
