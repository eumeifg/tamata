<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Controller\Advocate;

use Aheadworks\Raf\Controller\AdvocateAction;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 *
 * @package Aheadworks\Raf\Controller\Advocate
 */
class Index extends AdvocateAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Referral Program'));

        return $resultPage;
    }
}
