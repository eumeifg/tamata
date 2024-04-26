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
namespace Magedelight\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Description of Register
 */
class AdminOrderEmailCancel implements ObserverInterface
{

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/admin_order_cancel/template';
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AdminOrderEmailCancel constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if (!$this->scopeConfig->getValue('emailconfiguration/admin_order_cancel/enabled')) {
            return;
        }
        $order = $observer->getEvent()->getOrder();
        $order_id = $order->getIncrementId();

        $vendorOrder = $observer->getEvent()->getVendorOrder();
        $vendor_order_id = $vendorOrder->getVendorOrderId();

        foreach ($order->getAllItems() as $item) {
            $proName[] = $item->getName();
        }

        $templateVars['order_id'] = $order->getIncrementId()."-".$vendor_order_id;
        $templateVars['customer_name'] = $order->getCustomerName();
        $templateVars['customer_email'] = $order->getCustomerEmail();
        $templateVars['product_name'] = $proName;

        $this->_sendNotification($templateVars);
    }

    protected function _sendNotification($templateVars)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope('general')
            ->addTo($templateVars['customer_email'])
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
