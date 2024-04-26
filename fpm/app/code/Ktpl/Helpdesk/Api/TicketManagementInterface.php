<?php

namespace Ktpl\Helpdesk\Api;

use Ktpl\Helpdesk\Api\Data\TicketInterface;

/**
 * @api
 * @since 100.0.2
 */
interface TicketManagementInterface
{
    /**
     * @param int $customerId
     * @return \Ktpl\Helpdesk\Api\Data\CreateTicketInterface
     */
    public function createTicket($customerId);

    /**
     * Submit new ticket
     * @param string $customerName
     * @param string $customerEmail
     * @param string $subject
     * @param string $description
     * @param string $telephone
     * @return string|mixed
     */
    public function submitTicket(
        $customerName,
        $customerEmail,
        $subject,
        $description,
        $telephone
    );

    /**
     * @param int $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ktpl\Helpdesk\Api\Data\TicketSearchResultsInterface
     */
    public function getList($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get ticket by Id
     * @param int $customerId
     * @param int $ticketId
     * @return TicketInterface
     */
    public function getById($customerId, $ticketId);

    /**
     * Submit new message
     * @param int $customerId
     * @param int $ticketId
     * @param string $message
     * @param bool $close
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function submitMessage($customerId, $ticketId, $message, $close);
}