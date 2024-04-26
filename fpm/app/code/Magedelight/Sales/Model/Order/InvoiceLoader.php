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
namespace Magedelight\Sales\Model\Order;

use Magedelight\Sales\Api\Data\CustomMessageInterface;
use Magedelight\Sales\Model\Sales\Service\InvoiceService;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException as LocalizedExceptionAlias;

class InvoiceLoader extends DataObject
{
    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CustomMessageInterface
     */
    protected $customMessageInterface;

    /**
     * InvoiceLoader constructor.
     * @param InvoiceService $invoiceService
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param CustomMessageInterface $customMessageInterface
     * @param array $data
     */
    public function __construct(
        InvoiceService $invoiceService,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        CustomMessageInterface $customMessageInterface,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->customMessageInterface = $customMessageInterface;
        $this->invoiceService = $invoiceService;
        parent::__construct($data);
    }

    /**
     * Initialize invoice items QTY
     *
     * @return array
     */
    protected function getItemQtys()
    {
        $data = $this->getInvoice();
        return isset($data['items']) ? $data['items'] : [];
    }

    /**
     * Initialize invoice model instance
     *
     * @param $vendorOrderId
     * @return bool|\Magento\Sales\Model\Order\Invoice
     */
    public function load($vendorOrderId)
    {
        $orderId = $this->getOrderId();

        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);

            if (!$order->canInvoice()) {
                throw new LocalizedExceptionAlias(
                    __('The order does not allow an invoice to be created.')
                );
            }
            $invoice = $this->invoiceService->prepareInvoice($order, $this->getItemQtys(), $vendorOrderId);

            if (!$invoice->getTotalQty()) {
                $this->customMessageInterface->setMessage(
                    __('The invoice can\'t be created without products. Add products and try again.')
                );
                return $this->customMessageInterface->setStatus(false);
            }
            return $invoice->setOrder($order);
        } catch (LocalizedExceptionAlias $exception) {
            $this->customMessageInterface->setMessage($exception->getMessage());
            return $this->customMessageInterface->setStatus(false);
        } catch (\Exception $exception) {
            $this->customMessageInterface->setMessage(__('Cannot create an invoice.'));
            return $this->customMessageInterface->setStatus(false);
        }
    }
}
