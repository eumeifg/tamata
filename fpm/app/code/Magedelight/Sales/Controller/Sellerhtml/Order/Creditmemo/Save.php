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
namespace Magedelight\Sales\Controller\Sellerhtml\Order\Creditmemo;

use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class Save extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Sales\Controller\Sellerhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Sales\Controller\Sellerhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Sales\Controller\Sellerhtml\Order\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->authSession = $authSession;
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Save creditmemo
     * We can save only new creditmemo. Existing creditmemos are not editable
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPost('creditmemo');

        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $vendor_order_id = $this->getRequest()->getParam('vendor_order_id');
            $vendorId = $this->authSession->getUser()->getVendorId();
            /* Validate items if quantity set to 0 for all items. */
            if (!array_key_exists('items', $data) || empty($data['items'])) {
                $this->messageManager->addError(__('Cannot create credit memo as there are no items to generate.'));
                $resultRedirect->setPath(
                    'rbsales/order_creditmemo/add',
                    ['order_id' => $orderId,'vendor_order_id'=>$vendor_order_id,'tab'=>'10,0']
                );
                return $resultRedirect;
            }
            /* Validate items if quantity set to 0 for all items. */
            $this->creditmemoLoader->setOrderId($orderId);
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $this->creditmemoLoader->setVendorId($vendorId);
            $creditmemo = $this->creditmemoLoader->load();
            if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }

                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $creditmemo->setCustomerNote($data['comment_text']);
                    $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }

                if (isset($data['do_offline'])) {
                    /*do not allow online refund for Refund to Store Credit*/
                    if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Cannot create online refund for Refund to Store Credit.')
                        );
                    }
                }
                $creditmemoManagement = $this->_objectManager->create(
                    \Magento\Sales\Api\CreditmemoManagementInterface::class
                );
                $creditmemoManagement->refund($creditmemo, (bool)$data['do_offline'], !empty($data['send_email']));

                if (!empty($data['send_email'])) {
                    $this->creditmemoSender->send($creditmemo);
                }

                $this->messageManager->addSuccess(__('You created the credit memo.'));
                $resultRedirect->setPath('rbsales/order_creditmemo/index', ['tab'=>'3,3']);
                return $resultRedirect;
            } else {
                $resultRedirect->setPath(
                    'rbsales/order_creditmemo/add',
                    ['order_id' => $orderId,'vendor_order_id'=>$vendor_order_id,'tab'=>'10,0']
                );
                return $resultRedirect;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addError(__('We can\'t save the credit memo right now.'));
        }
        $resultRedirect->setPath(
            'rbsales/order_creditmemo/add',
            ['order_id' => $orderId,'vendor_order_id'=>$vendor_order_id,'tab'=>'10,0']
        );
        return $resultRedirect;
    }
}
