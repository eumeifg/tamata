<?php
/**
 * Ktpl
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_DealsZone
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com/)
 * @license     https://https://www.KrishTechnolabs.com/
 */

namespace Ktpl\DealsZone\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Category extends AbstractHelper
{
    /**
     * @return array
     */
    public function getAdditionalImageTypes()
    {
        return ['dealzone_image'];
    }
    /**
     * Retrieve image URL
     * @param $image
     * @return string
     */
    public function getImageUrl($image)
    {
        $url = false;
        //$image = $this->getImage();
        if ($image) {
            if (is_string($image)) {
                $url = $this->_urlBuilder->getBaseUrl(
                    ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]
                ) . 'catalog/category/' . $image;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }
}
