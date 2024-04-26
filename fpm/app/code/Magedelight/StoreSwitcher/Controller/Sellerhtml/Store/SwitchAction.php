<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_StoreSwitcher
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\StoreSwitcher\Controller\Sellerhtml\Store;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher;
use Magento\Store\Model\StoreSwitcherInterface;

/**
 * Handles store switching url and makes redirect.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SwitchAction extends Action
{
    /**
     * @var StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @var HttpContext
     * @deprecated 100.2.5
     */
    protected $httpContext;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var StoreManagerInterface
     * @deprecated 100.2.5
     */
    protected $storeManager;

    /**
     * @var StoreSwitcherInterface
     */
    private $storeSwitcher;

    /**
     * Initialize dependencies.
     *
     * @param ActionContext $context
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param HttpContext $httpContext
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreManagerInterface $storeManager
     * @param StoreSwitcherInterface $storeSwitcher
     */
    public function __construct(
        ActionContext $context,
        StoreCookieManagerInterface $storeCookieManager,
        HttpContext $httpContext,
        StoreRepositoryInterface $storeRepository,
        StoreManagerInterface $storeManager,
        StoreSwitcherInterface $storeSwitcher = null
    ) {
        parent::__construct($context);
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
        $this->messageManager = $context->getMessageManager();
        $this->storeSwitcher = $storeSwitcher ?: ObjectManager::getInstance()->get(StoreSwitcherInterface::class);
    }

    /**
     * Execute action
     *
     * @return void
     * @throws StoreSwitcher\CannotSwitchStoreException
     */
    public function execute()
    {
        $targetStoreCode = $this->_request->getParam(
            \Magento\Store\Model\StoreManagerInterface::PARAM_NAME,
            $this->storeCookieManager->getStoreCodeFromCookie()
        );
        $fromStoreCode = $this->_request->getParam('___from_store');

        $requestedUrlToRedirect = $this->_redirect->getRedirectUrl();
        $redirectUrl = $requestedUrlToRedirect;

        $error = null;
        try {
            $fromStore = $this->storeRepository->get($fromStoreCode);
            $targetStore = $this->storeRepository->getActiveStoreByCode($targetStoreCode);
            /* Ignore store code in urls for seller area. */
            $targetStore->setDisableStoreInUrl(true);
            /* Ignore store code in urls for seller area. */
        } catch (StoreIsInactiveException $e) {
            $error = __('Requested store is inactive');
        } catch (NoSuchEntityException $e) {
            $error = __("The store that was requested wasn't found. Verify the store and try again.");
        }
        if ($error !== null) {
            $this->messageManager->addErrorMessage($error);
        } else {
            $redirectUrl = $this->storeSwitcher->switch($fromStore, $targetStore, $requestedUrlToRedirect);
        }
        
        $this->getResponse()->setRedirect($redirectUrl);
    }
}
