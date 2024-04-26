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
namespace Magedelight\Sales\Controller\Sellerhtml\Order\Shipment;

use Magedelight\Backend\App\Action\Context;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;

class Save extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transaction;

    /**
     * Save constructor.
     * @param Context $context
     * @param ShipmentSender $shipmentSender
     * @param \Magedelight\Sales\Controller\Sellerhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\DB\Transaction $transaction
     */
    public function __construct(
        Context $context,
        ShipmentSender $shipmentSender,
        \Magedelight\Sales\Controller\Sellerhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\DB\Transaction $transaction
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->labelGenerator = $labelGenerator;
        $this->shipmentSender = $shipmentSender;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->session = $session;
        $this->transaction = $transaction;
        parent::__construct($context);
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     * @throws \Exception
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $vendorOrder = $this->vendorOrderRepository->getById($this->getRequest()->getParam('vendor_order_id'));
        $vendorOrder->setData('main_order', $shipment->getOrder());
        $vendorOrder->setStatus(VendorOrder::STATUS_SHIPPED);
        $transaction = $this->transaction;
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->addObject(
            $vendorOrder
        )->save();

        return $this;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

       /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $formKeyIsValid =  true;//$this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();

        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addErrorMessage(__('We can\'t save the shipment right now.'));
            return $resultRedirect->setPath('rbsales/order/index', ['tab' => $this->getTab()]);
        }
        $vendorId = $this->_auth->getUser()->getVendorId();

        $data = $this->getRequest()->getParam('shipment');

        if ($this->getRequest()->getParam('tracking')[1]['number'] == '') {
            $trackingData = '';
        } else {
            $trackingData = $this->getRequest()->getParam('tracking');
        }

        if (!empty($data['comment_text'])) {
            $this->session->setCommentText($data['comment_text']);
        }
        $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
        try {
            $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($data);
            $this->shipmentLoader->setTracking($trackingData);
            $shipment = $this->shipmentLoader->load($this->getRequest()->getParam('vendor_order_id'));
            $data['is_visible_on_front'] = true;
            if (!$shipment) {
                $this->_redirect('rbsales/order/index');
                return;
            }

            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );

                $shipment->setCustomerNote($data['comment_text']);
                $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }
            $shipment->setVendorId($vendorId);
            $shipment->setVendorOrderId($this->getRequest()->getParam('vendor_order_id'));

            $shipment->register();

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new \Magento\Framework\DataObject();

            if ($isNeedCreateLabel) {
                $this->labelGenerator->create($shipment, $this->_request);
                $responseAjax->setOk(true);
            }

            if (!$shipment->getTotalQty()) {
                $this->messageManager->addErrorMessage(__('We cannot create an empty shipment.'));
                $this->redirectToNewShipment();
                return;
            }

            if ((!empty($data) && min($data) < 0) ||
                (is_numeric($shipment->getTotalQty()) &&
                    floor($shipment->getTotalQty()) != $shipment->getTotalQty())) {
                $this->messageManager->addErrorMessage(
                    __('Negative or decimal quantities are not allowed for shipment.')
                );
                $this->redirectToNewShipment();
                return;
            }

            $this->_saveShipment($shipment);
            $this->shipmentSender->send($shipment);
            $shipmentCreatedMessage = __('Shipment has been generated successfully.');
            $labelCreatedMessage = __('Shipping label has been created successfully.');

            $this->messageManager->addSuccessMessage(
                $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->redirectToNewShipment();
            }
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(__('An error occurred while creating shipping label.'));
            } else {
                $this->messageManager->addErrorMessage(__('Cannot save shipment.'));
                $this->redirectToNewShipment();
            }
        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->representJson($responseAjax->toJson());
        } else {
            $this->_redirect(
                'rbsales/order/view',
                ['id' => $this->getRequest()->getParam('vendor_order_id'),'tab' => $this->getTab()]
            );
        }
    }

    /**
     *
     */
    protected function redirectToNewShipment()
    {
        $this->_redirect(
            '*/*/new',
            [
                'vendor_order_id' => $this->getRequest()->getParam('vendor_order_id'),
                'order_id' => $this->getRequest()->getParam('order_id'),
                'tab' => $this->getTab()
            ]
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
