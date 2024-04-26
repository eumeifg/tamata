<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Email\Processor\VariableProcessor;

use Aheadworks\Raf\Model\Source\Customer\Advocate\Email\BaseAmountVariables;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StoreUrl
 *
 * @package Aheadworks\Raf\Model\Advocate\Email\Processor\VariableProcessor
 */
class StoreUrl implements VariableProcessorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareVariables($variables)
    {
        /** @var StoreInterface $store */
        $store = $variables[BaseAmountVariables::STORE];

        $variables[BaseAmountVariables::STORE_URL] = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        return $variables;
    }
}
