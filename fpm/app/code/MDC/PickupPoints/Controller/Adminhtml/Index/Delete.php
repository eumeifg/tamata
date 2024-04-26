<?php
 
namespace MDC\PickupPoints\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;


class Delete extends Action
{

    /**
     * @var \MDC\PickupPoints\Model\Pickups;
     */
    protected $pickupModel;

    /**
     * @param Action\Context $context
     * @param \MDC\PickupPoints\Model\Pickups $pickupsModel
     */
    public function __construct(
        Action\Context $context,
        \MDC\PickupPoints\Model\Pickups $pickupsModel
    ) {
        parent::__construct($context);
        $this->pickupModel = $pickupsModel;
    }


    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('pickup_point_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->pickupModel;
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Record deleted successfully.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['pickup_point_id' => $id]);
            }
        }
        $this->messageManager->addError(__('Record does not exist.'));
        return $resultRedirect->setPath('*/*/');
    }
}