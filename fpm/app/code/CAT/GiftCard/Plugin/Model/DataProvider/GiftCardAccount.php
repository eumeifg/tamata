<?php

namespace CAT\GiftCard\Plugin\Model\DataProvider;

use Magento\Store\Api\Data\StoreInterface;
use CAT\GiftCard\Helper\Data as GiftCardHelper;
/**
 *
 */
class GiftCardAccount
{
    /**
     * @var GiftCardHelper
     */
    protected $giftCardHelper;

    /**
     * @param GiftCardHelper $giftCardHelper
     */
    public function __construct(
        GiftCardHelper $giftCardHelper
    ) {
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * @param \Magento\GiftCardAccountGraphQl\Model\DataProvider\GiftCardAccount $subject
     * @param string $giftCardCode
     * @param StoreInterface $store
     */
    public function beforeGetByCode(\Magento\GiftCardAccountGraphQl\Model\DataProvider\GiftCardAccount $subject, string $giftCardCode, StoreInterface $store) {
        //echo "<pre>"; print_r($giftCardCode); echo "</pre>";
        $customGiftCard = $this->giftCardHelper->getCustomGiftCardAccountDetails($giftCardCode, $store);
        //echo "<pre>"; print_r($customGiftCard); echo "</pre>";
        if ($customGiftCard) {
            return $customGiftCard;
        }
        return array($giftCardCode, $store);

        //die('====>');
    }
}
