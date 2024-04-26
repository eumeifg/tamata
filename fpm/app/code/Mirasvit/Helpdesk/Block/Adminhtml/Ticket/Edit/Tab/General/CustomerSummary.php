<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Block\Adminhtml\Ticket\Edit\Tab\General;

use Magento\Framework\View\Element\Template;
use Mirasvit\Helpdesk\Api\Service\Ticket\TicketManagementInterface;

class CustomerSummary extends Template
{
    protected $timezoneHelper;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Mirasvit\Helpdesk\Helper\Customer $helpdeskCustomer,
        \Mirasvit\Helpdesk\Helper\Order $helpdeskOrder,
        TicketManagementInterface $ticketManagement,
        \Mirasvit\Helpdesk\Helper\Timezone $timezoneHelper,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->registry         = $registry;
        $this->helpdeskCustomer = $helpdeskCustomer;
        $this->helpdeskOrder    = $helpdeskOrder;
        $this->ticketManagement = $ticketManagement;
        $this->timezoneHelper   = $timezoneHelper;
        $this->context          = $context;

        parent::__construct($context, []);
    }

    /**
     * @return \Mirasvit\Helpdesk\Model\Ticket
     */
    public function getTicket()
    {
        return $this->registry->registry('current_ticket');
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getConfigJson()
    {
        $ticket = $this->getTicket();

        $customersOptions = [];
        $ordersOptions    = [
            [
                'name' => (string)__('Unassigned'),
                'id'   => 0,
            ],
        ];

        $orders = [];
        if ($ticket->getCustomerId() || $ticket->getQuoteAddressId()) {
            $customers = $this->helpdeskCustomer->getCustomerArray(
                false,
                $ticket->getCustomerId(),
                $ticket->getQuoteAddressId()
            );
            $email     = false;

            foreach ($customers as $value) {
                $customersOptions[] = [
                    'name' => $value['name'],
                    'id'   => $value['id'],
                ];
                $email              = $value['email'];
            }

            $orders = $this->helpdeskOrder->getOrderArray($email, $ticket->getCustomerId());
        } elseif ($ticket->getCustomerEmail()) {
            $orders = $this->helpdeskOrder->getOrderArray($ticket->getCustomerEmail());
        }

        foreach ($orders as $value) {
            $ordersOptions[] = [
                'name' => $value['name'],
                'id'   => $value['id'],
                'url'  => $value['url'],
            ];
        }

        $url = '#';
        if ($ticket->getCustomerId()) {
            $url = $this->context->getUrlBuilder()->getUrl('customer/index/edit/', ['id' => $ticket->getCustomerId()]);
        }
        $config = [
            '_customer'        => [
                'id'     => $ticket->getCustomerId(),
                'email'  => $ticket->getCustomerEmail(),
                'cc'     => $ticket->getCc() ? implode(', ', $ticket->getCc()) : '',
                'bcc'    => $ticket->getBcc() ? implode(', ', $ticket->getBcc()) : '',
                'name'   => $ticket->getCustomerName(),
                'url'    => $url,
                'orders' => $ordersOptions,
            ],
            '_orderId'         => (int)$ticket->getOrderId(),
            '_rmas'            => $this->ticketManagement->getRmasOptions($ticket),
            '_emailTo'         => $ticket->getCustomerEmail(),
            '_loaderImg'       => $this->getViewFileUrl('images/loader-2.gif'),
            '_autocompleteUrl' => $this->getUrl('helpdesk/ticket/customerfind'),

            '_localTime'    => $this->getLocalTime(),
            '_localIsNight' => $this->isLocalNight(),
            '_nightImg'     => $this->getViewFileUrl('Mirasvit_Helpdesk::images/night.svg'),
            '_dayImg'       => $this->getViewFileUrl('Mirasvit_Helpdesk::images/day.svg'),
        ];

        return \Zend_Json_Encoder::encode($config);
    }

    public function getLocalTime()
    {
        $ticket   = $this->getTicket();
        $timezone = $this->timezoneHelper->getCustomerTimezone($ticket->getCustomerId(), $ticket->getCustomerEmail());

        if (!$timezone) {
            return "";
        }

        $tz   = new \DateTimeZone($timezone);
        $date = new \DateTime("now", $tz);
        $utc  = $date->format("T");

        return (string)__("local time: %1, %2", $this->formatTime(
            $date,
            \IntlDateFormatter::SHORT
        ), $utc);
    }

    private function isLocalNight()
    {
        $ticket   = $this->getTicket();
        $timezone = $this->timezoneHelper->getCustomerTimezone($ticket->getCustomerId(), $ticket->getCustomerEmail());

        if (!$timezone) {
            return "";
        }

        $tz   = new \DateTimeZone($timezone);
        $date = new \DateTime("now", $tz);

        $hourOfDay = $date->format('G');

        return ($hourOfDay > 21 || $hourOfDay < 7) ? true : false;
    }
}
