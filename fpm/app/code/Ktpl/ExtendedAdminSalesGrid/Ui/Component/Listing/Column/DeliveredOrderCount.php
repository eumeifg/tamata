<?php

namespace Ktpl\ExtendedAdminSalesGrid\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ResourceConnection;
use Magedelight\Sales\Model\Order;

/**
 * Class DeliveredOrderCount
 * @package Ktpl\ExtendedAdminSalesGrid\Ui\Component\Listing\Column
 */
class DeliveredOrderCount extends Column
{
    const SALES_ORDER_TABLE = 'sales_order';
    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * DeliveredOrderCount constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ResourceConnection $resourceConnection
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResourceConnection $resourceConnection,
        array $components = [],
        array $data = []
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_connection = $this->_resourceConnection->getConnection();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key =>$item) {
                $deliveredQuery = $this->_connection->select()
                    ->from(['so' => self::SALES_ORDER_TABLE], ['customer_email', 'so.entity_id', 'so.increment_id', 'order_status' =>'so.status'])
                    ->joinLeft(['mvo' => 'md_vendor_order'],'mvo.order_id = so.entity_id', ['vendor_status' => 'mvo.status'])
                    ->where('customer_email=?', $item['customer_email'])
                    ->where('mvo.status=?',Order::STATUS_COMPLETE)
                    ->group('so.entity_id');
                $deliveredResult = $this->_connection->fetchAll($deliveredQuery);
                $dataSource['data']['items'][$key]['delivered_count'] = count($deliveredResult);

                $pendingQuery = $this->_connection->select()
                    ->from(['so' => self::SALES_ORDER_TABLE], ['customer_email', 'so.entity_id', 'so.increment_id', 'order_status' =>'so.status'])
                    ->joinLeft(['mvo' => 'md_vendor_order'],'mvo.order_id = so.entity_id', ['vendor_status' => 'mvo.status'])
                    ->where('customer_email=?', $item['customer_email'])
                    ->where('mvo.status=?',Order::STATUS_PENDING)
                    ->group('so.entity_id');
                $pendingResult = $this->_connection->fetchAll($pendingQuery);
                $dataSource['data']['items'][$key]['pending_count'] = count($pendingResult);
            }
        }
        return $dataSource;
    }
}