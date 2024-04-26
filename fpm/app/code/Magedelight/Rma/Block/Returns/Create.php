<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Rma\Block\Returns;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Create extends \Magento\Rma\Block\Returns\Create
{
    protected $request;
    
    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Collection\ModelFactory $modelFactory
     * @param \Magento\Eav\Model\Form\Factory $formFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Rma\Model\ItemFactory $itemFactory
     * @param \Magento\Rma\Model\Item\FormFactory $itemFormFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Rma\Helper\Data $rmaData
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magedelight\Rma\Helper\Data $rbrmaData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Collection\ModelFactory $modelFactory,
        \Magento\Eav\Model\Form\Factory $formFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Rma\Model\ItemFactory $itemFactory,
        \Magento\Rma\Model\Item\FormFactory $itemFormFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Rma\Helper\Data $rmaData,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magedelight\Rma\Helper\Data $rbrmaData,
        array $data = []
    ) {
        $this->_rbRmaData = $rbrmaData;
        parent::__construct(
            $context,
            $modelFactory,
            $formFactory,
            $eavConfig,
            $itemFactory,
            $itemFormFactory,
            $rmaData,
            $registry,
            $addressRenderer,
            $data
        );
        $this->_request = $request;
        $this->_vendorOrderFactory = $vendorOrderFactory->create();
        $this->_vendorFactory = $vendorFactory->create();
    }
    
    /**
     * Initialize current order
     *
     * @return void
     */
    public function _construct()
    {
        $order = $this->_coreRegistry->registry('current_order');
        $vendorId = intval($this->_request->getParam('vendor_id'));
        if (!$order) {
            return;
        }
        $this->setOrder($order);
        $items = $this->_rmaData->getOrderItems($order);
        /*
         * Date: 23-Nov-2018
         * RD  : Only Applicable filter will display.
         */
        foreach ($items as $key => $item) {
            $itemVendorId = intval($item->getVendorId());
            if ($itemVendorId !== $vendorId) {
                $items->removeItemByKey($key);
            }
            $productId = $item->getProductId();
            $canReturnProduct = $this->_rbRmaData->canReturnProduct($productId, $item->getStoreId());
            if (!$canReturnProduct) {
                $items->removeItemByKey($key);
            }
        }
        
        $this->setItems($items);
        $formData = $this->_session->getRmaFormData(true);
        if (!empty($formData)) {
            $data = new \Magento\Framework\DataObject();
            $data->addData($formData);
            $this->setFormData($data);
        }
        $errorKeys = $this->_session->getRmaErrorKeys(true);
        if (!empty($errorKeys)) {
            $data = new \Magento\Framework\DataObject();
            $data->addData($errorKeys);
            $this->setErrorKeys($data);
        }
    }
    /**
     * getVendorOrderData
     */
    public function getVendorInfo()
    {
        $vendorId = $this->_request->getParam('vendor_id');
        $vendorData = $this->_vendorFactory->load($vendorId);
        if ($vendorData->getVendorId()) {
            return $vendorData->getData();
        }
        return false;
    }
}
