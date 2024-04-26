<?php

namespace CAT\Address\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->getParams()) {
            try {
                $model = $this->_objectManager->create('CAT\Address\Model\RomCity');
                $data = $this->getRequest()->getPostValue();

                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new LocalizedException(__('The wrong item is specified.'));
                    }
                }

                if (empty($data['entity_id'])) {
                    unset($data['entity_id']);
                }

                $model->setData($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccess(__('You saved the item.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('manage_city/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('manage_city/*/*');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('entity_id');
                if (!empty($id)) {
                    $this->_redirect('manage_city/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('manage_city/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('manage_city/*/edit', ['id' => $this->getRequest()->getParam('entity_id')]);
                return;
            }
        }
        $this->_redirect('manage_city/*/*');
    }
}
