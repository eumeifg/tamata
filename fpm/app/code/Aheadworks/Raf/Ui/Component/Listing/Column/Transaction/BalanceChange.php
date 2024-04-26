<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Ui\Component\Listing\Column\Transaction;

use Magento\Ui\Component\Listing\Columns\Column;
use Aheadworks\Raf\Model\Advocate\PriceFormatter;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Aheadworks\Raf\Api\Data\TransactionInterface;
use Aheadworks\Raf\Model\Source\Rule\AdvocateOffType;

/**
 * Class BalanceChange
 * @package Aheadworks\Raf\Ui\Component\Listing\Column\Transaction
 */
class BalanceChange extends Column
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
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $showPlus = $item[$this->getData('name')] > 0;

                $item['row_сlass_' . $this->getData('name')] = $item[$this->getData('name')] > 0
                    ? 'aw_raf__balance-green'
                    : 'aw_raf__balance-red';

                $formattedPrice = __('N/A');
                if ($item[TransactionInterface::AMOUNT_TYPE] == AdvocateOffType::FIXED) {
                    $formattedPrice = $this->priceFormatter->getFormattedFixedPriceByWebsite(
                        $item[$this->getData('name')],
                        $item['website_id']
                    );
                }
                if ($item[TransactionInterface::AMOUNT_TYPE] == AdvocateOffType::PERCENT) {
                    $formattedPrice = $this->priceFormatter->getFormattedPercentPrice(
                        $item[$this->getData('name')]
                    );
                }

                $item[$this->getData('name')] = ($showPlus ? '+' : '') . $formattedPrice;
            }
        }

        return $dataSource;
    }
}
