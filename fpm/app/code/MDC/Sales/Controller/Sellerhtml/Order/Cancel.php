<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace MDC\Sales\Controller\Sellerhtml\Order;

use Magedelight\Backend\App\Action\Context;
use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as CoreOrder;
use Magento\Sales\Model\Order\Item as CoreOrderItem;

class Cancel extends \Magedelight\Sales\Controller\Sellerhtml\Order\Cancel
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * Cancel constructor.
     * @param Context $context
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrder
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->_logger = $logger;
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct($context, $orderRepository, $coreRegistry, $vendorOrderRepository, $logger);
    }

    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $order = $this->_initOrder();
        $vendorOrder = $this->vendorOrderRepository->getById(
            $this->getRequest()->getParam('vendor_order_id')
        );

        if ($order && $vendorOrder && $vendorOrder->canCancel()) {
            try {
                $vendorOrder->registerCancel($order);
                $vendorOrder->setData('cancelled_by', CancelledBy::SELLER);
                
                /*** Added fixes for order cancel issue when coupon code applied unable to cancel ***/
                $cancelledItems = [];
				$itemsCount = [];
				foreach ($order->getAllItems() as $item) {
					if ($item->getParentItemId()) {
						continue;
					}
					$itemsCount[] =  $item->getId();
					if ($item->getStatusId() === CoreOrderItem::STATUS_CANCELED) {
						$cancelledItems[] = $item->getId();
					}
				}
				
				if (count($itemsCount) === count($cancelledItems)) {
					$state = CoreOrder::STATE_CANCELED;
					$order->setState($state)
						->setStatus($order->getConfig()->getStateDefaultStatus($state));
				}
				/*** Added fixes for order cancel issue when coupon code applied unable to cancel ***/
				
                $this->_objectManager->create(
                    \Magento\Framework\DB\Transaction::class
                )
                        ->addObject($vendorOrder)
                        ->addObject($order)
                        ->save();
                $this->_eventManager->dispatch(
                    'vendor_orders_cancel_after',
                    ['vendor_order_ids' => [$vendorOrder->getId()]]
                );
                $this->messageManager->addSuccessMessage(__('The order has been canceled.'));
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $errorMessage = __('The order has could not been canceled.');
                $this->messageManager->addErrorMessage($errorMessage);
            }
            $this->_coreRegistry->register('vendor_order', $vendorOrder);
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'rbsales/order/view',
            ['id' => $vendorOrder->getId(), 'tab' => $this->getTab()]
        );
    }

    /**
     * Initialize order model instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        try {
            $order = $this->_orderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        return $order;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        // return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders_cancel');
    }
}
