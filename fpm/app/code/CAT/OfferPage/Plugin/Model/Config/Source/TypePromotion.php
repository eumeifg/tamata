<?php

namespace CAT\OfferPage\Plugin\Model\Config\Source;

class TypePromotion
{
    const OFFER_PAGE = 'offer_page';
    public function afterToOptionArray(\Ktpl\Pushnotification\Model\Config\Source\TypePromotion $typePromotion, $result) {
        $options = [
            [
                'value' => self::OFFER_PAGE,
                'label' => 'Page Promotions'
            ]
        ];
        return array_merge($result, $options);
    }
}
