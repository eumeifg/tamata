<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Block\Friend;

use Aheadworks\Raf\Model\Friend\Viewer as FriendViewer;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Invited
 *
 * @package Aheadworks\Raf\Block\Friend
 */
class Invited extends Template
{
    /**
     * @var FriendViewer
     */
    private $friendViewer;

    /**
     * @param Context $context
     * @param FriendViewer $friendViewer
     * @param array $data
     */
    public function __construct(
        Context $context,
        FriendViewer $friendViewer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->friendViewer = $friendViewer;
    }

    /**
     * Retrieve static block html for welcome popup
     *
     * @return string
     */
    public function getStaticBlockHtmlForWelcomePopup()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        return $this->friendViewer->getStaticBlockHtmlForWelcomePopup($storeId);
    }
}
