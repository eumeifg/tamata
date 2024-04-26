<?php

namespace CAT\Custom\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ResourceConnection;

class OrderData extends Column {

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
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
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!array_key_exists('customer_id', $item) || $item['customer_id'] == '') {
                    continue;
                }
                $connection = $this->resourceConnection->getConnection();
                $selectQuery = $connection->select()->from('customer_feedback_by_admin')->where('customer_id=?', $item['customer_id']);
                $results = $connection->fetchRow($selectQuery);
                $score = 'NA';
                $feedback = 'NA';
                if(!empty($results)) {
                    $score = $results['score'];
                    $feedback = $results['comment'];
                }
                $item['customer_score'] = $score;
                $item['customer_feedback'] = $feedback;
            }
        }
        return $dataSource;
    }
}