<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report
 * @version   1.3.77
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Mirasvit\Report\Api\Data\StateInterface;
use Mirasvit\Report\Repository\StateRepository;

class StateService
{
    private $userContext;

    private $sessionManager;

    private $stateRepository;

    public function __construct(
        UserContextInterface $userContext,
        SessionManagerInterface $sessionManager,
        StateRepository $stateRepository
    ) {
        $this->userContext     = $userContext;
        $this->sessionManager  = $sessionManager;
        $this->stateRepository = $stateRepository;
    }

    public function saveState($namespace, array $config)
    {
        $state = $this->loadState($namespace);

        if (!$state) {
            $state = $this->stateRepository->create();

            $state->setNamespace($namespace . $this->sessionManager->getSessionId())
                ->setUserId($this->userContext->getUserId())
                ->setIdentifier('current')
                ->setCurrent(true)
                ->setCreatedAt(date('Y-m-d'))
                ->setUpdatedAt(date('Y-m-d'));
        }

        $state->setConfig(\Zend_Json::encode($config));

        $this->stateRepository->save($state);
    }

    /**
     * @param string $namespace
     *
     * @return StateInterface|false
     */
    private function loadState($namespace)
    {
        $namespace = $namespace . $this->sessionManager->getSessionId();

        $model = $this->stateRepository->getCollection()
            ->addFieldToFilter(StateInterface::USER_ID, $this->userContext->getUserId())
            ->addFieldToFilter(StateInterface::BOOKMARKSPACE, $namespace)
            ->getFirstItem();

        if ($model->getId()) {
            $state = $this->stateRepository->create();
            $state->setData($model->getData());

            return $state;
        }

        return false;
    }

    public function mergeState($namespace, $defaultConfig)
    {
        $state = $this->loadState($namespace);

        if (!$state) {
            return $defaultConfig;
        }

        foreach ($state->getConfig() as $key => $value) {
            $defaultConfig[$key] = $value;
        }

        return $defaultConfig;
    }
}