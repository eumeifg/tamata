<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Ui\Component\Form\Advocate;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;
use Aheadworks\Raf\Model\Advocate\PriceFormatter;

/**
 * Class Price
 * @package Aheadworks\Raf\Ui\Component\Form\Advocate
 */
class Price extends Field
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
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);

        if (isset($dataSource['data']['cumulative_amount'])) {
            $dataSource['data']['cumulative_amount_orig'] = $dataSource['data']['cumulative_amount'] * 1;
            $dataSource['data']['cumulative_amount'] = $this->priceFormatter->getFormattedFixedPriceByWebsite(
                $dataSource['data']['cumulative_amount'],
                $dataSource['data']['website_id']
            );
        }
        return $dataSource;
    }
}
