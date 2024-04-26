<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Controller\Adminhtml\ProductRequest;

use Magedelight\Catalog\Model\ProductRequest;

class EditPost extends \Magedelight\Catalog\Controller\Adminhtml\ProductRequest
{

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $productRequest;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
    ) {
        $this->productRequest = $productRequestFactory->create();
        parent::__construct($context);
    }
    /**
     * Edit Brand Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();

        if ($data) {
            $id = $this->getRequest()->getParam('product_request_id');
            $this->productRequest->load($id);
            if (!$this->productRequest->getId()) {
                $this->messageManager->addError(__('This Product Request no longer exists. '));
                $this->_redirect('*/*/');
                return;
            }
            $status = $this->getRequest()->getParam('status');
            if ($status === ProductRequest::STATUS_DISAPPROVED) {
                $this->productRequest->setData('status', $status);
                $this->productRequest->setData('disapprove_message', $data['disapprove_message']);
                try {
                    $this->productRequest->save();
                    $this->messageManager->addSuccess(__('The Product request has been disapproved.'));
                    $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', ['id' => $this->productRequest->getId(), '_current' => true]);
                        return;
                    }
                    $this->_redirect('*/*/');
                    return;
                } catch (\Magento\Framework\Model\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                    $this->messageManager->addError($e->getMessage());
                    $this->messageManager->addException(
                        $e,
                        __('Something went wrong while saving the product request.')
                    );
                }

                $this->_getSession()->setFormData($data);
            }
        }
        $this->_redirect('*/*/');
    }
}
