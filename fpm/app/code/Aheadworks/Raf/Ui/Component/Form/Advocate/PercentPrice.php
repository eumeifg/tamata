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
 * Class PercentPrice
 *
 * @package Aheadworks\Raf\Ui\Component\Form\Advocate
 */
class PercentPrice extends Field
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
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);

        if (isset($dataSource['data']['cumulative_percent_amount'])) {
            $dataSource['data']['cumulative_percent_amount'] = $this->priceFormatter->getFormattedPercentPrice(
                $dataSource['data']['cumulative_percent_amount']
            );
        }
        return $dataSource;
    }
}
