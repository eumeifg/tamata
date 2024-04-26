<?php

namespace MDC\Sales\Model\WebApi;

use Magedelight\Catalog\Model\Product;
use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magento\Framework\App\ResourceConnection;
use Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\DB\Transaction as Transaction;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Authorization\Model\UserContextInterface;
use Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\CollectionFactory as ProductWebsiteCollectionFactory;

/**
 * Class OrderStatusManager
 * @package MDC\Sales\Model\WebApi
 */
class OrderStatusManager implements \MDC\Sales\Api\OrderStatusManagerInterface
{
    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CustomMessageInterfaceFactory
     */
    protected $customMessageInterface;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var ProductWebsiteCollectionFactory
     */
    protected $productWebsiteCollectionFactory;

    /**
     * OrderStatusManager constructor.
     * @param PsrLogger $logger
     * @param DateTime $dateTime
     * @param UserContextInterface $userContext
     * @param CollectionFactory $collectionFactory
     * @param ManagerInterface $eventManager
     * @param CustomMessageInterfaceFactory $customMessageInterface
     * @param OrderRepositoryInterface $orderRepository
     * @param Transaction $transaction
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        PsrLogger $logger,
        DateTime $dateTime,
        UserContextInterface $userContext,
        CollectionFactory $collectionFactory,
        ManagerInterface $eventManager,
        CustomMessageInterfaceFactory $customMessageInterface,
        OrderRepositoryInterface $orderRepository,
        Transaction $transaction,
        ResourceConnection $resourceConnection,
        ProductWebsiteCollectionFactory $productWebsiteCollectionFactory
    ) {
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->userContext = $userContext;
        $this->collectionFactory = $collectionFactory;
        $this->eventManager = $eventManager;
        $this->customMessageInterface = $customMessageInterface;
        $this->orderRepository = $orderRepository;
        $this->transaction = $transaction;
        $this->resourceConnection = $resourceConnection;
        $this->_connection = $this->resourceConnection->getConnection();
        $this->productWebsiteCollectionFactory = $productWebsiteCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function vendorBulkOrderConfirm($vendorOrderIds)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('vendor_order_id', ['in' => implode(',',$vendorOrderIds)]);
        $vendorIdArray = array_column($collection->getData(), 'vendor_id', 'vendor_order_id');
        $vendorIds = array_unique($vendorIdArray);
        $vendorId = $this->userContext->getUserId();
        $diffArray = array_diff($vendorIds, [$vendorId]);
        if ($diffArray) {
            $noBelongingOrders = array_keys(array_intersect($vendorIdArray, $diffArray));
            throw new \Exception(__('This order does not belongs to you. %1', implode(',', $noBelongingOrders)));
        }
        $isConfirmed = array_column($collection->getData(), 'is_confirmed', 'vendor_order_id');
        $confirmed = array_diff($isConfirmed, [0]);
        if (!empty($confirmed)) {
            throw new \Exception(__('Orders are already confirmed. %1', implode(',', array_keys($confirmed))));
        }
        $customMessage = $this->customMessageInterface->create();
        $failedIds = [];
        $successIds = [];
        foreach ($collection as $vendorOrder) {
            try {
                $vendorOrder->setData("is_confirmed", 1)
                    ->setData('confirmed_at', $this->dateTime->gmtDate())
                    ->save();
                $this->eventManager->dispatch('vendor_orders_confirm_after', ['vendor_order' => $vendorOrder]);
                $successIds[] = $vendorOrder->getId();
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $failedIds[] = $vendorOrder->getId();
            }
        }
        if(!empty($failedIds)) {
            $customMessage->setMessage(__('Failed to confirm orders %1', implode(',', $failedIds)));
            $customMessage->setStatus(false);
            return $customMessage;
        } else {
            $customMessage->setMessage(__('successfully confirm order(s) %1', implode(',', $successIds)));
            $customMessage->setStatus(true);
            return $customMessage;
        }
    }

    /**
     * @inheritDoc
     */
    public function vendorBulkOrderCancel($vendorOrderIds, $isUnlist)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('vendor_order_id', ['in' => implode(',',$vendorOrderIds)]);
        $vendorIdArray = array_column($collection->getData(), 'vendor_id', 'vendor_order_id');
        $vendorIds = array_unique($vendorIdArray);
        $vendorId = $this->userContext->getUserId();
        $diffArray = array_diff($vendorIds, [$vendorId]);
        if ($diffArray) {
            $noBelongingOrders = array_keys(array_intersect($vendorIdArray, $diffArray));
            throw new \Exception(__('This order does not belongs to you. %1', implode(',', $noBelongingOrders)));
        }
        $customMessage = $this->customMessageInterface->create();
        $orderIdNotExist = [];
        $failedIds = [];
        $successIds = [];
        foreach ($collection as $vendorOrder) {
            try {
                $order = $this->orderRepository->get($vendorOrder->getOrderId());
                if ($order && $vendorOrder && $vendorOrder->canCancel()) {
                    $vendorOrder->registerCancel($order);
                    $vendorOrder->setData('cancelled_by', CancelledBy::SELLER);
                    $this->transaction->addObject($vendorOrder)
                        ->addObject($order)
                        ->save();
                    $successIds[] = $vendorOrder->getId();
                } else {
                    throw new \Exception(__('order can\'t be canceled'));
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->critical($e);
                $orderIdNotExist[] = $vendorOrder->getOrderId();
            } catch (\Exception $ex) {
                $this->logger->critical($ex);
                $failedIds[] = $vendorOrder->getId();
            }
        }
        $errorMsg = '';
        if(!empty($orderIdNotExist)) {
            $errorMsg.= __('This order no longer exists. %1  .', implode(',', $orderIdNotExist));
        }
        if (!empty($failedIds)) {
            $errorMsg.= __('The order was not canceled, Please try again later. %1  .', implode(',', $failedIds));
        }
        if (!empty($successIds)) {
            $this->eventManager->dispatch(
                'vendor_orders_cancel_after',
                ['vendor_order_ids' => $successIds]
            );
        }
        if ($isUnlist && !empty($successIds)) {
            $this->unListItems($successIds);
        }
        if ($errorMsg) {
            $customMessage->setMessage($errorMsg);
            $customMessage->setStatus(false);
            return $customMessage;
        } else {
            $customMessage->setMessage(__('The order has been canceled. %1  .', implode(',', $successIds)));
            $customMessage->setStatus(true);
            return $customMessage;
        }
    }

    public function unListItems($vendorOrderIds) {
        $collection = $this->productWebsiteCollectionFactory->create();
        $collection->addFieldToFilter('main_table.status', Product::STATUS_LISTED);
        $collection->getSelect()->join(
            ['mdvp' => 'md_vendor_product'],
            'mdvp.vendor_product_id = main_table.vendor_product_id',
            ['marketplace_product_id','qty']
        );
        $collection->getSelect()->join(
            ['soi' => 'sales_order_item'],
            'soi.vendor_sku = mdvp.vendor_sku',
            ''
        );
        $collection->getSelect()->where('soi.vendor_order_id IN ('.implode(',', $vendorOrderIds).')');

        $productIds = $products = [];
        $updatedRecordsCount = 0;
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $item->setStatus(0);
                $item->save();
                $productIds[] = $item->getMarketplaceProductId();
                $products[$item->getVendorProductId()]['qty'] = $item->getQty();
                $products[$item->getVendorProductId()]['marketplace_product_id'] = $item->getMarketplaceProductId();
                $updatedRecordsCount++;
            }
        }
        if ($updatedRecordsCount > 0) {
            $eventParams = [
                'marketplace_product_ids' => $productIds ,
                'vendor_products' => $products
            ];
            $this->eventManager->dispatch('frontend_vendor_product_mass_unlist_after', $eventParams);
        }
    }
}