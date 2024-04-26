<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MDC\MobileBanner\Controller\Adminhtml\Banner;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \MDC\MobileBanner\Model\Banner $bannerModel
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->serialize = $serialize;
        $this->bannerModel =$bannerModel;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $dynamicRowData = $this->getRequest()->getParam('dynamic_rows_container');

        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
        
            $model = $this->bannerModel->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Banner no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            if($dynamicRowData) {
                $serializedData = $this->serialize->serialize($dynamicRowData);
                $data['section_details'] = $serializedData;
            }
            $model->setData($data);
        
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Banner.'));
                $this->dataPersistor->clear('mdc_mobilebanner_banner');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Banner.'));
            }
        
            $this->dataPersistor->set('mdc_mobilebanner_banner', $data);
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
