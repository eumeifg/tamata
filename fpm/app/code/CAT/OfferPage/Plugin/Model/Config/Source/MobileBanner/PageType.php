<?php

namespace CAT\OfferPage\Plugin\Model\Config\Source\MobileBanner;

class PageType
{
    const OFFER_PAGE   = 'offer_page';

    public function afterToOptionArray(
        \MDC\MobileBanner\Model\Config\Source\PageType $pageType,
        $result
    ) {
        $options = [
            [
                'value' => self::OFFER_PAGE,
                'label' => __('Offer Page')
            ]
        ];
        return array_merge($result, $options);
    }
}
