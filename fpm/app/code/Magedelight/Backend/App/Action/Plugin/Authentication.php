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
namespace Magedelight\Backend\App\Action\Plugin;

use Magento\Framework\Exception\AuthenticationException;

/**
 * Seller Panel Authentication
 *
 * @author Rocket Bazaar Core Team
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authentication
{
    /**
     * @var \Magedelight\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Magedelight\Backend\App\BackendAppList
     */
    protected $backendAppList;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     *
     * @param \Magedelight\Backend\Model\Auth $auth
     * @param \Magedelight\Backend\Model\UrlInterface $url
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magedelight\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magedelight\Backend\App\BackendAppList $backendAppList
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        \Magedelight\Backend\Model\Auth $auth,
        \Magedelight\Backend\Model\UrlInterface $url,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magedelight\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magedelight\Backend\App\BackendAppList $backendAppList,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        $this->_auth = $auth;
        $this->_url = $url;
        $this->_response = $response;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->backendUrl = $backendUrl;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->backendAppList = $backendAppList;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * @param \Magedelight\Backend\App\AbstractAction $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magedelight\Backend\App\AbstractAction $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $requestedActionName = $request->getActionName();
        if (in_array(strtolower($requestedActionName), $this->getOpenActions())) {
            $request->setDispatched(true);
        } else {
            if ($this->_auth->getUser()) {
                $this->_auth->getUser()->reload();
            }
            if (!$this->_auth->isLoggedIn()) {
                $this->_processNotLoggedInUser($request);
            } else {
                $this->_auth->getAuthStorage()->prolong();

                $backendApp = null;
                if ($request->getParam('app')) {
                    $backendApp = $this->backendAppList->getCurrentApp();
                }

                if ($backendApp) {
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $baseUrl = \Magento\Framework\App\Request\Http::getUrlNoScript($this->backendUrl->getBaseUrl());
                    $baseUrl = $baseUrl . $backendApp->getStartupPage();
                    return $resultRedirect->setUrl($baseUrl);
                }
            }
        }
        $this->_auth->getAuthStorage()->refreshAcl();
        return $proceed($request);
    }

    /**
     * Process not logged in user data
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    protected function _processNotLoggedInUser(\Magento\Framework\App\RequestInterface $request)
    {
        $isRedirectNeeded = false;
        if ($request->getPost('login')) {
            if ($this->formKeyValidator->validate($request)) {
                if ($this->_performLogin($request)) {
                    $isRedirectNeeded = $this->_redirectIfNeededAfterLogin($request);
                }
            } else {
                $this->_actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
                $this->_response->setRedirect($this->_url->getCurrentUrl());
                $this->messageManager->addError(__('Invalid Form Key. Please refresh the page.'));
                $isRedirectNeeded = true;
            }
        }
        if (!$isRedirectNeeded && !$request->isForwarded()) {
            if ($request->getParam('isIframe')) {
                $request->setForwarded(true)
                    ->setRouteName('sellerhtml')
                    ->setControllerName('auth')
                    ->setActionName('deniedIframe')
                    ->setDispatched(false);
            } elseif ($request->getParam('isAjax')) {
                $request->setForwarded(true)
                    ->setRouteName('sellerhtml')
                    ->setControllerName('auth')
                    ->setActionName('deniedJson')
                    ->setDispatched(false);
            } else {
                    $request->setForwarded(true)
                        ->setRouteName('rbvendor')
                        ->setControllerName('index')
                        ->setActionName('index')
                        ->setDispatched(false);
            }
        }
    }
    
    /**
     * Performs login, if user submitted login form
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    protected function _performLogin(\Magento\Framework\App\RequestInterface $request)
    {
        $outputValue = true;
        $postLogin = $request->getPost('login');
        $username = isset($postLogin['username']) ? $postLogin['username'] : '';
        $password = isset($postLogin['password']) ? $postLogin['password'] : '';
        $request->setPostValue('login', null);

        try {
            $this->_auth->login($username, $password);
        } catch (AuthenticationException $e) {
            if (!$request->getParam('messageSent')) {
                $this->messageManager->addError($e->getMessage());
                $request->setParam('messageSent', true);
                $outputValue = false;
            }
        }
        return $outputValue;
    }

    /**
     * Checks, whether Magento requires redirection after successful admin login, and redirects user, if needed
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    protected function _redirectIfNeededAfterLogin(\Magento\Framework\App\RequestInterface $request)
    {
        $requestUri = null;

        // Checks, whether secret key is required for admin access or request uri is explicitly set
        if ($this->_url->useSecretKey()) {
            $requestUri = $this->_url->getUrl('*/*/*', ['_current' => true]);
        } elseif ($request) {
            $requestUri = $request->getRequestUri();
        }

        if (!$requestUri) {
            return false;
        }

        $this->_response->setRedirect($requestUri);
        $this->_actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
        return true;
    }
    
    /**
     *
     * @return array
     */
    public function getOpenActions()
    {
        return [
            'registerpost',
            'register',
            'forgotpassword',
            'forgotpasswordpost',
            'sendverification',
            'createpassword',
            'resetpasswordpost',
            'checkbusinessname',
            'login',
            'logout',
            'refresh', // captcha refresh
        ];
    }
}
