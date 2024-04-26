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
namespace MDC\Sales\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magedelight\Sales\Model\Order as VendorOrder;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

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
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator
     * @param ShipmentSender $shipmentSender
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     */
    public function __construct(
        Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator,
        ShipmentSender $shipmentSender,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->labelGenerator = $labelGenerator;
        $this->shipmentSender = $shipmentSender;
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct($context);
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->addObject(
            $shipment->getVendorOrder()
        )->save();

        return $this;
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addErrorMessage(__('We can\'t save the shipment right now.'));
            return $resultRedirect->setPath('sales/order/index');
        }

        $data = $this->getRequest()->getParam('shipment');
        if (!array_key_exists('vendor_id', $data)) {
            $this->messageManager->addErrorMessage(__('We can\'t save the shipment right now.'));
            return $resultRedirect->setPath('sales/order/index');
        }
        if (!empty($data['comment_text'])) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setCommentText($data['comment_text']);
        }

        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $this->shipmentLoader->setOrderId($orderId);
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($data);
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load(
                $this->getRequest()->getParam('vendor_order_id')
            );
            if (!$shipment) {
                $this->_redirect('*/*/');
                return;
            }
            $vendorOrder = $this->vendorOrderRepository->getById(
                $data['vendor_order_id']
            );

            if (!$vendorOrder->canShip()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow to create a shipment.')
                );
            }
            $shipment->setData('vendor_id',$data['vendor_id']);
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );

                $shipment->setCustomerNote($data['comment_text']);
                $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }

            $shipment->register();

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new \Magento\Framework\DataObject();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel) {
                $this->labelGenerator->create($shipment, $this->_request);
                $responseAjax->setOk(true);
            }
            $vendorOrder->setData('main_order', $shipment->getOrder());
            $vendorOrder->setStatus(VendorOrder::STATUS_SHIPPED);
            $shipment->setVendorOrder($vendorOrder);
            $shipment->setVendorOrderId($data['vendor_order_id']);
            $this->_saveShipment($shipment);

            if (!empty($data['send_email'])) {
                $this->shipmentSender->send($shipment);
            }

            $shipmentCreatedMessage = __('The shipment has been created.');
            $labelCreatedMessage = __('You created the shipping label.');

            $this->messageManager->addSuccessMessage(
                $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
            );
            $this->_eventManager->dispatch('vendor_order_shipment_generate_after', ['order' => $vendorOrder]);
            $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(__('An error occurred while creating shipping label.'));
            } else {
                $this->messageManager->addErrorMessage(__('Cannot save shipment.'));
                $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
            }
        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->representJson($responseAjax->toJson());
        } else {
            $this->_redirect('sales/order/view', ['order_id' => $shipment->getOrderId()]);
        }
    }
}
