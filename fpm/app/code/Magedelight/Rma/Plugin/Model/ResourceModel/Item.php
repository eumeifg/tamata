<?php
namespace Magedelight\Rma\Plugin\Model\ResourceModel;

use Magento\Rma\Model\Rma\Source\Status;

class Item
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magedelight\Rma\Model\Config
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magedelight\Rma\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magedelight\Rma\Model\Config $config
    ) {
        $this->resource = $resource;
        $this->config = $config;
    }

    /**
     * Gets rma items ids by order
     *
     * @param  int $orderId
     * @return array
     */
    public function aroundGetReturnableItems(\Magento\Rma\Model\ResourceModel\Item $subject, \Closure $proceed, $orderId)
    {
        $connection = $subject->getConnection();
        $salesAdapter = $this->resource->getConnection('sales');
        $shippedSelect = $salesAdapter->select()
            ->from(
                ['order_item' => $subject->getTable('sales_order_item')],
                [
                    'order_item.item_id',
                    'order_item.qty_shipped'
                ]
            )->where(
                "CURRENT_DATE BETWEEN DATE(order_item.updated_at) AND "
                    . "DATE(DATE_ADD(order_item.updated_at, INTERVAL {$this->config->getPolicyReturnPeriod()} DAY))"
            );
                
        $orderItemsShipped = $salesAdapter->fetchPairs($shippedSelect);

        $requestedSelect = $connection->select()
            ->from(
                ['rma' => $subject->getTable('magento_rma')],
                [
                    'rma_item.order_item_id',
                    new \Zend_Db_Expr('SUM(qty_requested)')
                ]
            )
            ->joinInner(
                ['rma_item' => $subject->getTable('magento_rma_item_entity')],
                'rma.entity_id = rma_item.rma_entity_id',
                []
            )->where(
                'rma_item.order_item_id IN (?)',
                array_keys($orderItemsShipped)
            )->where(
                sprintf(
                    '%s NOT IN (?)',
                    $connection->getIfNullSql('rma.status', $connection->quote(Status::STATE_CLOSED))
                ),
                [Status::STATE_CLOSED, Status::STATE_PROCESSED_CLOSED]
            )
            ->group('rma_item.order_item_id');
        $orderItemsRequested = $connection->fetchPairs($requestedSelect);
        $result = [];
        foreach ($orderItemsShipped as $itemId => $shipped) {
            $requested = 0;
            if (isset($orderItemsRequested[$itemId])) {
                $requested = $orderItemsRequested[$itemId];
            }

            $result[$itemId] = 0;
            if ($shipped > $requested) {
                $result[$itemId] = $shipped - $requested;
            }
        }

        return $result;
    }
}
