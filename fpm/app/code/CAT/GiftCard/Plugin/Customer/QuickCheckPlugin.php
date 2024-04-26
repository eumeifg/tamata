<?php

namespace CAT\GiftCard\Plugin\Customer;

use Magento\GiftCardAccount\Controller\Cart\QuickCheck;
use CAT\GiftCard\Helper\Data as GiftCardHelper;
/**
 * Class QuickCheckPlugin
 * @package CAT\GiftCard\Plugin\Customer
 */
class QuickCheckPlugin
{
    /**
     * @var GiftCardHelper
     */
    protected $giftCardHelper;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $objectFactory;

    /**
     * QuickCheckPlugin constructor.
     * @param GiftCardHelper $giftCardHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     */
    public function __construct(
        GiftCardHelper $giftCardHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\DataObjectFactory $objectFactory
    ) {
        $this->giftCardHelper = $giftCardHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->objectFactory = $objectFactory;
    }
    public function afterExecute(QuickCheck $subject, $result) {
        //echo "<pre>"; print_r($subject->getRequest()->getParams()); echo "</pre>";
        //echo "<pre>"; print_r($result); echo "</pre>";
        $card = $this->giftCardHelper->quickCheckCustomGiftCard($subject);
        //echo "<pre>"; print_r($card->getData()); echo "</pre>";
        if ($card) {
            $this->_coreRegistry->unregister('current_giftcardaccount_check_error');
            $this->_coreRegistry->unregister('current_giftcardaccount');
            $cardObj = $this->objectFactory->create();
            $cardObj->setData('giftcardaccount_id', $card->getCouponId());
            $cardObj->setData('code', $card->getCode());
            $cardObj->setData('balance', '1000');
            $cardObj->setData('date_expires', '');
            //echo "<pre>"; print_r($cardObj->getData()); echo "</pre>";
            $result = $this->_coreRegistry->register('current_giftcardaccount', $cardObj);
            return $result;
        }
        return $result;
        //die('++++++++++');
    }
}