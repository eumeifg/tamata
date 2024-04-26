<?php


namespace MDC\Sales\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{

    const SHIPPED_STATUSES = ['shipped', 'in_transit','out_warehouse', 'complete'];

    const NOT_SHIPPED_STATUSES = ['pending', 'confirmed', 'processing', 'packed'];

    protected $orderLogFactory;

    protected $timezone;

    protected $resourceConnection;

    protected $orderRepository;

    protected $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MDC\Sales\Model\OrderLog $orderLogFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderLogFactory = $orderLogFactory;
        $this->timezone = $timezone;
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    public function getOrderProcessHistory($vendorOrderId)
    {
        /*$vendorOrderId = explode("-", $vendorOrderId);
        $vendorOrderId = $vendorOrderId[0];*/
        $customTableData = $this->orderLogFactory->getCollection()->addFieldToFilter(
                            'inc_vendor_order_id', ['eq' => $vendorOrderId]
                            )->getData();
        return $customTableData;
    }

    public function formatDateToTimezone($date)
    {
        $dateTimeZone = $this->timezone->date(new \DateTime($date))->format('d M Y H:i:s A');
        return $dateTimeZone;
    }

    public function updateInBoxStatusAndItemCounter($vendororder)
    {
        if($vendororder->getOrderId()) {
            try {
                $connection = $this->resourceConnection->getConnection();
                $selectQuery = $connection->select()
                    ->from('md_vendor_order', 'status')
                    ->where('order_id=?', $vendororder->getOrderId());
                    $result = $connection->fetchAll($selectQuery);
                    if(!empty($result)) {
                        $order = $this->orderRepository->get($vendororder->getOrderId());
                        $status = [];
                        $inBoxStatuses = [];
                        $i = 0;
                        foreach ($result as $value) {
                            $status[] = $value['status'];
                            if(!in_array($value['status'], ['canceled', 'closed'])) {
                                if(in_array($value['status'], self::SHIPPED_STATUSES)) {
                                    $i += 1;
                                    $inBoxStatuses[] = 1;
                                } elseif (in_array($value['status'],self::NOT_SHIPPED_STATUSES)) {
                                    $inBoxStatuses[] = 0;
                                }
                            } else {
                                $inBoxStatuses[] = 2;
                            }
                        }
                        
                        $inBoxStatus = array_unique($inBoxStatuses);
                        if(in_array(1, $inBoxStatus) && !in_array(0, $inBoxStatus)) {
                            $inboxVal = 'all';
                        } elseif(!in_array(1, $inBoxStatus) && in_array(0, $inBoxStatus)) {
                            $inboxVal = 'none';
                        } elseif(in_array(1, $inBoxStatus) && in_array(0, $inBoxStatus)) {
                            $inboxVal = 'partial';
                        } else {
                            $inboxVal = '';
                        }
                        if(count($result) > 0) {
                            $itemCounter = $i.' of '. count($result);
                            $order->setItemCounter($itemCounter);
                        }
                        $order->setInBoxStatus($inboxVal);
                        $this->orderRepository->save($order);
                    }
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }
}
