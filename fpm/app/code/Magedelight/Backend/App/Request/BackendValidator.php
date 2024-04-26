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
declare(strict_types=1);

namespace Magedelight\Backend\App\Request;

use Magedelight\Backend\App\AbstractAction;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Request\ValidatorInterface;
use Magento\Framework\App\RequestInterface;
use Magedelight\Backend\Model\Auth;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw as RawResult;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magedelight\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\Phrase;

/**
 * Do backend validations.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackendValidator implements ValidatorInterface
{
    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var BackendUrl
     */
    private $backendUrl;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var RawFactory
     */
    private $rawResultFactory;

    /**
     * @param Auth $auth
     * @param FormKeyValidator $formKeyValidator
     * @param BackendUrl $backendUrl
     * @param RedirectFactory $redirectFactory
     * @param RawFactory $rawResultFactory
     */
    public function __construct(
        Auth $auth,
        FormKeyValidator $formKeyValidator,
        BackendUrl $backendUrl,
        RedirectFactory $redirectFactory,
        RawFactory $rawResultFactory
    ) {
        $this->auth = $auth;
        $this->formKeyValidator = $formKeyValidator;
        $this->backendUrl = $backendUrl;
        $this->redirectFactory = $redirectFactory;
        $this->rawResultFactory = $rawResultFactory;
    }

    /**
     * @param RequestInterface $request
     * @param ActionInterface $action
     *
     * @return bool
     */
    private function validateRequest(
        RequestInterface $request,
        ActionInterface $action
    ): bool {
        /** @var bool|null $valid */
        $valid = null;

        if ($action instanceof CsrfAwareActionInterface) {
            $valid = $action->validateForCsrf($request);
        }

        if ($valid === null) {
            $validFormKey = true;
            $validSecretKey = true;
            if ($request instanceof HttpRequest && $request->isPost()) {
                $validFormKey = $this->formKeyValidator->validate($request);
            } elseif ($this->auth->isLoggedIn()
                && $this->backendUrl->useSecretKey()
            ) {
                /* Skip validation on cms pages. */
                $requestPathParams = explode('/', trim($request->getPathInfo(), '/'));
                $areaFrontName = array_shift($requestPathParams);
                $routeName = array_shift($requestPathParams);
                if ($routeName === 'cms') {
                    return true;
                }
                /* Skip validation on cms pages. */
                
                $secretKeyValue = (string)$request->getParam(
                    BackendUrl::SECRET_KEY_PARAM_NAME,
                    null
                );
                $secretKey = $this->backendUrl->getSecretKey();
                $validSecretKey = ($secretKeyValue === $secretKey);
            }
            $valid = $validFormKey && $validSecretKey;
        }

        return $valid;
    }

    /**
     * @param RequestInterface $request
     * @param ActionInterface $action
     *
     * @return InvalidRequestException
     */
    private function createException(
        RequestInterface $request,
        ActionInterface $action
    ): InvalidRequestException {
        /** @var InvalidRequestException|null $exception */
        $exception = null;

        if ($action instanceof CsrfAwareActionInterface) {
            $exception = $action->createCsrfValidationException($request);
        }

        if ($exception === null) {
            if ($request instanceof HttpRequest && $request->isAjax()) {
                //Sending empty response for AJAX request since we don't know
                //the expected response format and it's pointless to redirect.
                /** @var RawResult $response */
                $response = $this->rawResultFactory->create();
                $response->setHttpResponseCode(401);
                $response->setContents('');
                $exception = new InvalidRequestException($response);
            } else {
                //For regular requests.
                $startPageUrl = $this->backendUrl->getStartupPageUrl();
                $response = $this->redirectFactory->create()
                    ->setUrl($this->backendUrl->getUrl($startPageUrl));
                $exception = new InvalidRequestException(
                    $response,
                    [
                        new Phrase(
                            'Invalid security or form key. Please refresh the page.'
                        )
                    ]
                );
            }
        }

        return $exception;
    }

    /**
     * @inheritDoc
     */
    public function validate(
        RequestInterface $request,
        ActionInterface $action
    ): void {
        if ($action instanceof AbstractAction) {
            //Abstract Action has build-in validation.
            if (!$action->_processUrlKeys()) {
                throw new InvalidRequestException($action->getResponse());
            }
        } else {
            //Fallback validation.
            if (!$this->validateRequest($request, $action)) {
                throw $this->createException($request, $action);
            }
        }
    }
}
