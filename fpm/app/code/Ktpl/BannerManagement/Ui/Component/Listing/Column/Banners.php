<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Ktpl\BannerManagement\Helper\Data as bannerHelper;

/**
 * Class Banners
 *
 * @package Ktpl\BannerManagement\Ui\Component\Listing\Column
 */
class Banners extends Column
{
    /**
     * @var bannerHelper
     */
    protected $helperData;

    /**
     * Banners constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param bannerHelper       $helperData
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        bannerHelper $helperData,
        array $components = [],
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['slider_id'])) {
                    $id                           = $item['slider_id'];
                    $data                         = $this->helperData->getBannerCollection($id)->getSize();
                    $item[$this->getData('name')] = ($data > 0) ? $data . '<span> banners </span>' : '<b>' . __("No banner added") . '</b>';
                }
            }
        }

        return $dataSource;
    }
}
