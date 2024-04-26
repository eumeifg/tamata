<?php

namespace CAT\GiftCard\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;

/**
 * Class GiftCardRule
 * @package CAT\GiftCard\Controller\Adminhtml
 */
abstract class GiftCardRule extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
    }

    protected function _initRule()
    {
        $this->_coreRegistry->register(
            \CAT\GiftCard\Model\RegistryConstants::CURRENT_GIFT_CARD_RULE,
            $this->_objectManager->create(\CAT\GiftCard\Model\GiftCardRule::class)
        );
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('rule_id')) {
            $id = (int)$this->getRequest()->getParam('rule_id');
        }

        if ($id) {
            $this->_coreRegistry->registry(\CAT\GiftCard\Model\RegistryConstants::CURRENT_GIFT_CARD_RULE)->load($id);
        }
    }

    public function _initAction() {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'CAT_GiftCard::cat_giftcardrule'
        )->_addBreadcrumb(
            __('Gift Card Promotions'),
            __('Gift Card Promotions')
        );
        return $this;
    }
}