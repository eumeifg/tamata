<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Controller\Adminhtml\Rule;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action as BackendAction;
use Aheadworks\Raf\Model\Rule\Form\SingleWebsiteChecker;
use Aheadworks\Raf\Model\Service\RuleService;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Raf\Api\Data\RuleInterface;

/**
 * Class Index
 *
 * @package Aheadworks\Raf\Controller\Adminhtml\Rule
 */
class Index extends BackendAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Raf::rules';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var SingleWebsiteChecker
     */
    private $singleWebsiteChecker;

    /**
     * @var RuleService
     */
    private $ruleService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SingleWebsiteChecker $singleWebsiteChecker
     * @param RuleService $ruleService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SingleWebsiteChecker $singleWebsiteChecker,
        RuleService $ruleService,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->singleWebsiteChecker = $singleWebsiteChecker;
        $this->resultPageFactory = $resultPageFactory;
        $this->ruleService = $ruleService;
        $this->storeManager = $storeManager;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if ($this->singleWebsiteChecker->isSingleWebsite()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            /** @var RuleInterface $rule */
            if ($rule = $this->ruleService->getActiveRule($websiteId)) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $rule->getId()]);
            } else {
                return $resultRedirect->setPath('*/*/new');
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Aheadworks_Raf::rules');
        $resultPage->getConfig()->getTitle()->prepend(__('Rules'));
        return $resultPage;
    }
}
