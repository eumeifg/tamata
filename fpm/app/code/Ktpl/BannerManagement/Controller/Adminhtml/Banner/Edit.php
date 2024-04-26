<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\BannerManagement\Controller\Adminhtml\Banner;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Edit CMS block action.
 */
class Edit extends \Ktpl\BannerManagement\Controller\Adminhtml\Banner implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ktpl\BannerManagement\Model\BannerRepository $bannerRepository,
        \Ktpl\BannerManagement\Model\BannerFactory $bannerFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->bannerFactory = $bannerFactory;
        $this->bannerRepository = $bannerRepository;
        parent::__construct($context, $coreRegistry, $bannerRepository, $bannerFactory);
    }

    /**
     * Edit CMS block
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');

        $banner = $this->initBanner();

        // 2. Initial checking
        if ($id) {

            if (!$banner) {
                $this->messageManager->addErrorMessage(__('This banner no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('ktpl_banner', $banner);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Banner') : __('New Banner'),
            $id ? __('Edit Banner') : __('New Banner')
        );

        $resultPage->getConfig()->getTitle()->prepend(__('Blocks'));
        $resultPage->getConfig()->getTitle()->prepend($banner->getBannerId() ? $banner->getTitle() : __('New Banner'));

        return $resultPage;
    }
}
