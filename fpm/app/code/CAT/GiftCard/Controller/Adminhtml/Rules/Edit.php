<?php

namespace CAT\GiftCard\Controller\Adminhtml\Rules;

use CAT\GiftCard\Controller\Adminhtml\GiftCardRule;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends GiftCardRule implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create(\CAT\GiftCard\Model\GiftCardRule::class);
        $this->_coreRegistry->register(\CAT\GiftCard\Model\RegistryConstants::CURRENT_GIFT_CARD_RULE, $model);

        $resultPage = $this->resultPageFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('giftcard/*');
                return;
            }
            $resultPage->getLayout()->getBlock('giftcard_rule_edit_tab_coupons')->setCanShow(true);
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_initAction();
        $this->_addBreadcrumb($id ? __('Edit Rule') : __('New Rule'), $id ? __('Edit Rule') : __('New Rule'),null);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getRuleId() ? __($model->getRuleName()) : __('New Gift Card Rule')
        );
        $this->_view->renderLayout();
    }
}