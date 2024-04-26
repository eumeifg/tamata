<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Form;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SingleWebsiteChecker
 * @package Aheadworks\Raf\Controller\Adminhtml\Rule
 */
class SingleWebsiteChecker
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Check if there is only one website in the system
     *
     * @return bool
     */
    public function isSingleWebsite()
    {
        $websites = $this->storeManager->getWebsites();
        return (count($websites) > 1) ? false : true;
    }
}
