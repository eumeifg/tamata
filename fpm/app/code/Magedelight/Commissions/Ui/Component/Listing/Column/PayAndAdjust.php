<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Ui\Component\Listing\Column;

use Magedelight\Commissions\Model\Commission\Payment as PaymentCommissionModel;

class PayAndAdjust extends \Magento\Ui\Component\Listing\Columns\Column
{

    /** Url path */
    const URL_PATH_EDIT = '#';

    /** @var UrlInterface */
    protected $_urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['vendor_payment_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => '#' . $item['vendor_payment_id'] . '',
                            'label' => __('Mark as Paid'),
                            'hidden' => ($item['transaction_type'] == PaymentCommissionModel::DEBIT_TRANSACTION_TYPE) ?
                                true : false,
                        ],
                    ];
                }
            }
        }
        return $dataSource;
    }
}
