<?php
 
namespace MDC\PickupPoints\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use MDC\PickupPoints\Model\Pickups;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var Pickups
     */
    protected $pickUpmodel;

    /**
     * @var Session
     */
    protected $adminsession;

    /**
     * @param Action\Context $context
     * @param Pickups           $pickUpmodel
     * @param Session        $adminsession
     */
    public function __construct(
        Action\Context $context,
        Pickups $pickUpmodel,
        Session $adminsession
    ) {
        parent::__construct($context);
        $this->pickUpmodel = $pickUpmodel;
        $this->adminsession = $adminsession;
    }

    /**
     * Save blog record action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $pickup_point_id = $this->getRequest()->getParam('pickup_point_id');
            if ($pickup_point_id) {
                $this->pickUpmodel->load($pickup_point_id);
            }

            $this->pickUpmodel->setData($data);

            try {
                $this->pickUpmodel->save();
                $this->messageManager->addSuccess(__('The data has been saved.'));
                $this->adminsession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('back') == 'add') {
                        return $resultRedirect->setPath('*/*/add');
                    } else {
                        return $resultRedirect->setPath('*/*/edit', ['pickup_point_id' => $this->pickUpmodel->getBlogId(), '_current' => true]);
                    }
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['pickup_point_id' => $this->getRequest()->getParam('pickup_point_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}