<?php
namespace MDC\Sales\Controller\Sellerhtml\Order;

use Magedelight\Backend\App\Action\Context;
use Magedelight\Sales\Api\OrderRepositoryInterface;
use MDC\Sales\Model\Source\Order\PickupStatus;

class UpdatePickupStatus extends \Magedelight\Backend\App\Action
{

    /**
     * @var OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * UpdatePickupStatus constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $vendorOrderRepository 
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $vendorOrderRepository
    ) {
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct($context);
    }

    /**
     * Update suborder pickup status
     *
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $pickupStatus = $this->getRequest()->getParam('pickup_status', PickupStatus::READY_TO_PICK);
        try{
            $vendorOrder = $this->vendorOrderRepository->getById($this->getRequest()->getParam('vendor_order_id'));
            if($vendorOrder->getVendorId() != $this->_auth->getUser()->getVendorId()){
                 $this->messageManager->addErrorMessage(__('Requested order does not exist.'));
                 return $resultRedirect->setPath('rbsales/order/index', ['tab' => $this->getTab()]);
            }
            $vendorOrder->setPickupStatus($pickupStatus);
            $this->vendorOrderRepository->save($vendorOrder);
            $this->messageManager->addSuccessMessage(__('Pickup status successfully updated.'));
        }catch (\Exception $exception){
            $this->messageManager->addErrorMessage(__('Failed to update pickup status.'));
            $this->logger->critical($exception);
        }
        return $resultRedirect->setPath('rbsales/order/index', ['tab' => $this->getTab()]);
    }
    
    protected function _isAllowed()
    {         
        // return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders_ready_to_pick');
    }

}
