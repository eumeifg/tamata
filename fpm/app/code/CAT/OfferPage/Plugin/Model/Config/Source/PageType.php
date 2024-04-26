<?php

namespace CAT\OfferPage\Plugin\Model\Config\Source;


class PageType
{
    const OFFER_PAGE   = 'offer_page';

    /**
     * @param \Ktpl\BannerManagement\Model\Config\Source\PageType $pageType
     * @param $result
     * @return array
     */
    public function afterToOptionArray(
        \Ktpl\BannerManagement\Model\Config\Source\PageType $pageType,
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
