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
namespace Magedelight\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var \Magedelight\Sales\Controller\Adminhtml\Order\CreditmemoLoader
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
     * @var \Magedelight\Sales\Model\CheckStoreCreditHistory
     */
     protected $creditHistoryModel;

    /**
     * @param Action\Context $context
     * @param \Magedelight\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Action\Context $context,
        \Magedelight\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magedelight\Sales\Model\CheckStoreCreditHistory $creditHistoryModel
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->creditHistoryModel = $creditHistoryModel;
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
        if (!empty($data['comment_text'])) {
            $this->_getSession()->setCommentText($data['comment_text']);
        }
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $vendorId = $this->getRequest()->getParam('do_as_vendor');
            $vendorOrderId = $this->getRequest()->getParam('vendor_order_id');
            /* Validate items if quantity set to 0 for all items. */
            if (!array_key_exists('items', $data) || empty($data['items'])) {
                $this->messageManager->addError(__('Cannot create credit memo as there are no items to generate.'));
                $resultRedirect->setPath('rbsales/*/new', ['_current' => true]);
                return $resultRedirect;
            }
            /* Validate items if quantity set to 0 for all items. */
            $this->creditmemoLoader->setOrderId($orderId);
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $this->creditmemoLoader->setVendorId($vendorId);
            $this->creditmemoLoader->setVendorOrderId($vendorOrderId);
            $creditmemo = $this->creditmemoLoader->load();

            /*Set validation to refund store credit limitation*/
            $creditAction = '4,5'; // 5 = reverted
            $creditReverted  = $this->creditHistoryModel->getOrderRevertRefundHistory($creditmemo->getOrder(), $creditAction);            
            $customerRevertedAmount = 0;

            if($creditReverted['reverted'] && $creditReverted['reverted'] > 0 ){
                $customerRevertedAmount = $creditReverted['reverted']; 
            }

            $storeCreditAmount = $creditmemo->getOrder()->getBaseCustomerBalanceAmount();
            $customerRefundedAmount = $creditmemo->getOrder()->getCustomerBalanceRefunded();
            if($customerRevertedAmount >= $storeCreditAmount ){
                $storeCreditAmount = 0;
                $customerRefundedAmount = 0;
                $customerRevertedAmount = 0;
            }
            
            $allowedStoreCredit = ( $storeCreditAmount - $customerRevertedAmount ); 
 
            if( isset($data['refund_customerbalance_return']) && $data['refund_customerbalance_return'] > $allowedStoreCredit ){

                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'The most Store Credit available to refund is %1.',
                        $allowedStoreCredit
                        )
                );
           }
           /*Set validation to refund store credit limitation*/

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
                    //do not allow online refund for Refund to Store Credit
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
                $this->_getSession()->getCommentText(true);
                $resultRedirect->setPath('sales/order/view', ['order_id' => $creditmemo->getOrderId()]);
                return $resultRedirect;
            } else {
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addError(__('We can\'t save the credit memo right now.'));
        }
        $resultRedirect->setPath('rbsales/*/new', ['_current' => true]);
        return $resultRedirect;
    }
}
