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
declare(strict_types=1);

namespace Magedelight\StoreSwitcher\Controller\Sellerhtml\Store;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreResolver;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\Generic as Session;

/**
 * Builds correct url to target store and performs redirect.
 */
class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var SidResolverInterface
     */
    private $sidResolver;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Context $context
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreResolverInterface $storeResolver
     * @param Session $session
     * @param SidResolverInterface $sidResolver
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        StoreResolverInterface $storeResolver,
        Session $session,
        SidResolverInterface $sidResolver
    ) {
        parent::__construct($context);
        $this->storeRepository = $storeRepository;
        $this->storeResolver = $storeResolver;
        $this->session = $session;
        $this->sidResolver = $sidResolver;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Store\Model\Store $currentStore */
        $currentStore = $this->storeRepository->getById($this->storeResolver->getCurrentStoreId());
        $targetStoreCode = $this->_request->getParam(StoreResolver::PARAM_NAME);
        $fromStoreCode = $this->_request->getParam('___from_store');
        $error = null;

        if ($targetStoreCode === null) {
            return $this->_redirect($currentStore->getBaseUrl());
        }

        try {
            /** @var \Magento\Store\Model\Store $targetStore */
            $fromStore = $this->storeRepository->get($fromStoreCode);
        } catch (NoSuchEntityException $e) {
            $error = __('Requested store is not found');
        }

        if ($error !== null) {
            $this->messageManager->addErrorMessage($error);
            $this->_redirect->redirect($this->_response, $currentStore->getBaseUrl());
        } else {
            $query = [
                '___from_store' => $fromStore->getCode(),
                StoreResolverInterface::PARAM_NAME => $targetStoreCode,
            ];

            if ($this->sidResolver->getUseSessionInUrl()) {
                // allow customers to stay logged in during store switching
                $sidName = $this->sidResolver->getSessionIdQueryParam($this->session);
                $query[$sidName] = $this->session->getSessionId();
            }

            $arguments = [
                '_query' => $query
            ];
            $this->_redirect->redirect($this->_response, 'stores/store/switch', $arguments);
        }
    }
}
