<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Asset\Repository;
use Ktpl\BannerManagement\Helper\Data;

/**
 * Class Template
 *
 * @package Ktpl\BannerManagement\Model\Config\Source
 */
class Template implements OptionSourceInterface
{
    const DEMO1 = 'demo1.png';
    const DEMO2 = 'demo2.png';
    const DEMO3 = 'demo3.png';
    const DEMO4 = 'demo4.jpg';
    const DEMO5 = 'demo5.jpg';

    /**
     * @var Repository
     */
    private $_assetRepo;

    /**
     * Template constructor.
     *
     * @param Repository $assetRepo
     */
    public function __construct(Repository $assetRepo)
    {
        $this->_assetRepo = $assetRepo;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 0,
                'label' => __('Demo template 1')
            ],
            [
                'value' => 1,
                'label' => __('Demo template 2')
            ],
            [
                'value' => 2,
                'label' => __('Demo template 3')
            ],
            [
                'value' => 3,
                'label' => __('Demo template 4')
            ],
            [
                'value' => 4,
                'label' => __('Demo template 5')
            ],
        ];

        return $options;
    }

    /**
     * @return false|string
     */
    public function getTemplateHtml()
    {
        $imgTmp    = '<div class="item" style="background:url({{media url="ktpl/bannermanagement/banner/demo/{{imgName}}}}) center center no-repeat;background-size:cover;">
                <div class="container" style="position:relative">
                    <img src="{{media url="ktpl/bannermanagement/banner/demo/{{imgName}}}}" alt="{{imgName}}">
                </div>
            </div>';
        $templates = [
            self::DEMO1 => [
                'tpl' => $imgTmp,
                'var' => '{{imgName}}'
            ],
            self::DEMO2 => [
                'tpl' => $imgTmp,
                'var' => '{{imgName}}'
            ],
            self::DEMO3 => [
                'tpl' => $imgTmp,
                'var' => '{{imgName}}'
            ],
            self::DEMO4 => [
                'tpl' => $imgTmp,
                'var' => '{{imgName}}'
            ],
            self::DEMO5 => [
                'tpl' => $imgTmp,
                'var' => '{{imgName}}'
            ],
        ];

        return Data::jsonEncode($templates);
    }

    /**
     * @return false|string
     */
    public function getImageUrls()
    {
        $urls = [];
        foreach ($this->toOptionArray() as $template) {
            $urls[$template['value']] = $this->_assetRepo->getUrl('Ktpl_BannerManagement::images/' . $template['value']);
        }

        return Data::jsonEncode($urls);
    }
}
