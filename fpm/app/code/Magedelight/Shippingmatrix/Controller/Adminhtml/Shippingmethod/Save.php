<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Controller\Adminhtml\Shippingmethod;

class Save extends \Magedelight\Shippingmatrix\Controller\Adminhtml\Shippingmethod
{

    /**
     * @var \Magedelight\Shippingmatrix\Model\ShippingMethodFactory
     */
    protected $_shippingMethodFactory;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Shippingmatrix\Model\ShippingMethodFactory $shippingMethodFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Shippingmatrix\Model\ShippingMethodFactory $shippingMethodFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_shippingMethodFactory = $shippingMethodFactory;
        parent::__construct($context);
    }

    /**
     * Check Grid List Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Shippingmatrix::add_edit');
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('shipping_method_id');
            
            // Get current store
            if (!$id) {
                $model = $this->_shippingMethodFactory->create();
                $model->setShippingMethod(trim($data['shipping_method']));
            } else {
                $model = $this->_shippingMethodFactory->create()->load($id);
                if ($model->getShippingMethodId()) {
                    $model->setShippingMethod(trim($data['shipping_method']));
                }
            }
            
            try {
                $model->save();
                $this->messageManager->addSuccess(__('The shipping method has been saved.'));
                $this->dataPersistor->clear('shipping_method');
             
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['shipping_method_id' => $model->getShippingMethodId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the shipping method.'));
            }

            $this->dataPersistor->set('shipping_method', $data);
            return $resultRedirect->setPath('*/*/edit', ['shipping_method_id' => $this->getRequest()->getParam('shipping_method_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
