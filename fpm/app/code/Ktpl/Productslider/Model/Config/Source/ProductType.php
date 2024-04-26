<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ProductType
 * @package Ktpl\Productslider\Model\Config\Source
 */
class ProductType implements ArrayInterface
{
    const NEW_PRODUCTS = 'new';
    const BEST_SELLER_PRODUCTS = 'best-seller';
    const FEATURED_PRODUCTS = 'featured';
    const MOSTVIEWED_PRODUCTS = 'mostviewed';
    const ONSALE_PRODUCTS = 'onsale';
    const RECENT_PRODUCT = 'recent';
    const WISHLIST_PRODUCT = 'wishlist';
    const CATEGORY = 'category';
    const CUSTOM_PRODUCTS = 'custom';
    const BRAND_N_VENDOR = 'brand_n_vendor';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function toArray()
    {
        return [
            self::NEW_PRODUCTS         => __('New Products'),
            self::BEST_SELLER_PRODUCTS => __('Best Seller Products'),
            self::FEATURED_PRODUCTS    => __('Featured Products'),
            self::MOSTVIEWED_PRODUCTS  => __('Most Viewed Products'),
            self::ONSALE_PRODUCTS      => __('On Sale Products'),
            self::RECENT_PRODUCT       => __('Recent Products'),
            //            self::WISHLIST_PRODUCT     => __('WishList Products'),
            self::CATEGORY             => __('Select By Category'),
            self::CUSTOM_PRODUCTS      => __('Custom Specific Products'),
            self::BRAND_N_VENDOR      => __('Brands & Vendors')
        ];
    }

    /**
     * @param $type
     * @return mixed|string
     */
    public function getLabel($type)
    {
        $types = $this->toArray();
        if (isset($types[$type])) {
            return $types[$type];
        }

        return '';
    }

    /**
     * @param null $type
     * @return array|mixed
     */
    public function getBlockMap($type = null)
    {
        $maps = [
            self::NEW_PRODUCTS         => \Ktpl\Productslider\Block\NewProducts::class,
            self::BEST_SELLER_PRODUCTS => \Ktpl\Productslider\Block\BestSellerProducts::class,
            self::FEATURED_PRODUCTS    => \Ktpl\Productslider\Block\FeaturedProducts::class,
            self::MOSTVIEWED_PRODUCTS  => \Ktpl\Productslider\Block\MostViewedProducts::class,
            self::ONSALE_PRODUCTS      => \Ktpl\Productslider\Block\OnSaleProduct::class,
            self::RECENT_PRODUCT       => \Ktpl\Productslider\Block\RecentProducts::class,
            self::WISHLIST_PRODUCT     => \Ktpl\Productslider\Block\WishlistProducts::class,
            self::CATEGORY             => \Ktpl\Productslider\Block\CategoryId::class,
            self::CUSTOM_PRODUCTS      => \Ktpl\Productslider\Block\CustomProducts::class,
        ];

        if ($type && isset($maps[$type])) {
            return $maps[$type];
        }

        return $maps;
    }
}
