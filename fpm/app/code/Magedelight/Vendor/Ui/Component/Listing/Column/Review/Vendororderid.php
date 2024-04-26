<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Ui\Component\Listing\Column\Review;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Vendororderid extends Column
{
    protected $urlBuilder;
    
    public function __construct(
        ContextInterface $context,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                  $item[$fieldName] = $this->_getVendorOrderId($item['vendor_order_id']);
            }
        }
        return $dataSource;
    }
    private function _getVendorOrderId($vendorId)
    {
        $collection = $this->vendorOrder->getCollection()->addFieldToFilter('vendor_order_id', $vendorId);
        return $collection->getFirstItem()->getIncrementId();
    }
}
