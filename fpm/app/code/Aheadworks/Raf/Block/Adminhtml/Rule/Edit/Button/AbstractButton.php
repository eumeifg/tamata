<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Block\Adminhtml\Rule\Edit\Button;

use Magento\Backend\Block\Widget\Context;
use Aheadworks\Raf\Model\Rule\Form\SingleWebsiteChecker;

/**
 * Class AbstractButton
 * @package Aheadworks\Raf\Block\Adminhtml\Rule\Edit\Button
 */
abstract class AbstractButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var SingleWebsiteChecker
     */
    private $singleWebsiteChecker;

    /**
     * AbstractButton constructor.
     * @param Context $context
     * @param SingleWebsiteChecker $singleWebsiteChecker
     */
    public function __construct(
        Context $context,
        SingleWebsiteChecker $singleWebsiteChecker
    ) {
        $this->context = $context;
        $this->singleWebsiteChecker = $singleWebsiteChecker;
    }

    /**
     * Check if button can be visible or not depending on single website
     *
     * @return bool
     */
    protected function isAllowedToShowButton()
    {
        return !$this->singleWebsiteChecker->isSingleWebsite();
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
