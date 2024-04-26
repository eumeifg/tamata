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
namespace Magedelight\Backend\App\Action;

use Magento\Framework\Controller\ResultFactory;

/**
 * Backend Controller context
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Context extends \Magento\Framework\App\Action\Context
{

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magedelight\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magedelight\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var bool
     */
    protected $_canUseBaseUrl;

    /**
     * @var \Magedelight\Vendor\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\App\ViewInterface $view
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param ResultFactory $resultFactory
     * @param \Magedelight\Vendor\Model\Session $vendorSession
     * @param \Magedelight\Backend\Model\Authorization $authorization
     * @param \Magedelight\Backend\Helper\Data $helper
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param type $canUseBaseUrl
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magedelight\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        ResultFactory $resultFactory,
        \Magedelight\Vendor\Model\Session $vendorSession,
        \Magedelight\Backend\Model\Authorization $authorization,
        \Magedelight\Backend\Model\Auth $auth,
        \Magedelight\Backend\Helper\Data $helper,
        \Magedelight\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $canUseBaseUrl = false
    ) {
        parent::__construct(
            $request,
            $response,
            $objectManager,
            $eventManager,
            $url,
            $redirect,
            $actionFlag,
            $view,
            $messageManager,
            $resultRedirectFactory,
            $resultFactory
        );

        $this->_vendorSession = $vendorSession;
        $this->_authorization = $authorization;
        $this->_auth = $auth;
        $this->_helper = $helper;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_localeResolver = $localeResolver;
        $this->_canUseBaseUrl = $canUseBaseUrl;
        $this->_urlInterface = $url;
        $this->_backendUrl = $backendUrl;
    }

    /**
     * @return \Magedelight\Backend\Model\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @return \Magedelight\Backend\Model\Authorization
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }

    /**
     * @return \Magedelight\Backend\Model\UrlInterface
     */
    public function getUrl()
    {
        return $this->_urlInterface;
    }

    /**
     * @return \Magedelight\Backend\Model\UrlInterface
     */
    public function getBackendUrl()
    {
        return $this->_backendUrl;
    }
    
    /**
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCanUseBaseUrl()
    {
      //  return $this->_canUseBaseUrl;
    }

    /**
     * @return \Magento\Framework\Data\Form\FormKey\Validator
     */
    public function getFormKeyValidator()
    {
        return $this->_formKeyValidator;
    }

    /**
     * @return \Magedelight\Backend\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @return \Magento\Framework\Locale\ResolverInterface
     */
    public function getLocaleResolver()
    {
        return $this->_localeResolver;
    }

    /**
     * @return \Magedelight\Vendor\Model\Session
     */
    public function getSession()
    {
        return $this->_vendorSession;
    }
}
