<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Aheadworks\Raf\Model\Advocate\PriceFormatter;

/**
 * Class Price
 * @package Aheadworks\Raf\Ui\Component\Listing\Column
 */
class Price extends Column
{
    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceFormatter $priceFormatter
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceFormatter $priceFormatter,
        array $components = [],
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item['website_id'])) {
                    continue;
                }

                $item[$this->getData('name') . '_orig'] = $item[$this->getData('name')] * 1;
                $item[$this->getData('name')] = $this->priceFormatter->getFormattedFixedPriceByWebsite(
                    $item[$this->getData('name')],
                    $item['website_id']
                );
            }
        }
        return $dataSource;
    }
}
