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
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Magedelight\Sales\Model\Order
     */
    private $_vendorOrder;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * @var \Magento\Shipping\Model\ShipmentProviderInterface
     */
    protected $shipmentProvider;

    /**
     * NewAction constructor.
     * @param Context $context
     * @param \Magedelight\Sales\Controller\Sellerhtml\Order\ShipmentLoader $shipmentLoader
     * @param Registry $registry
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param \Magento\Shipping\Model\ShipmentProviderInterface $shipmentProvider
     */
    public function __construct(
        Context $context,
        \Magedelight\Sales\Controller\Sellerhtml\Order\ShipmentLoader $shipmentLoader,
        Registry $registry,
        \Magedelight\Vendor\Model\Design $design,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magento\Shipping\Model\ShipmentProviderInterface $shipmentProvider
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->design = $design;
        $this->_registry = $registry;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->shipmentProvider = $shipmentProvider;
        parent::__construct($context);
    }

    /**
     * Shipment create page
     *
     * @return void
     */
    public function execute()
    {
        $this->getVendorOrder();
        $this->design->applyVendorDesign();

        $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
        $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
        $this->shipmentLoader->setShipment($this->shipmentProvider->getShipmentData());
        $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));

        $shipment = $this->shipmentLoader->load($this->getRequest()->getParam('vendor_order_id'));

        if ($shipment) {
            $comment = $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
            if ($comment) {
                $shipment->setCommentText($comment);
            }
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addErrorMessage(__('Cannot Generate shipment.'));
            $this->_redirect(
                '*/order/index',
                ['order_id' => $this->getRequest()->getParam('order_id'), 'tab' => "2,0"]
            );
        }
    }

    protected function getVendorOrder()
    {
        if (!($vendorId = $this->_auth->getUser()->getVendorId())) {
            return false;
        }
        $vendorOrder = $this->vendorOrderRepository->getById(
            $this->getRequest()->getParam('vendor_order_id', false)
        );
        $this->_registry->register('vendor_order', $vendorOrder);

        return $vendorOrder;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
