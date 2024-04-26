<?php

namespace Ktpl\Helpdesk\Model;

use Ktpl\Helpdesk\Api\Data\TicketInterface;
use Mirasvit\Helpdesk\Controller\Form;
use Mirasvit\Helpdesk\Model\Config;

class TicketManagement implements \Ktpl\Helpdesk\Api\TicketManagementInterface
{
    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $emailTemplateFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\Config
     */
    protected $config;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Field
     */
    protected $helpdeskField;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Process
     */
    protected $helpdeskProcess;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory
     */
    protected $ticketCollectionFactory;

    /**
     * @var \Ktpl\Helpdesk\Api\Data\TicketSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\DepartmentFactory
     */
    protected $departmentFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\StatusFactory
     */
    protected $statusFactory;

    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Message\CollectionFactory
     */
    protected $messageCollectionFactory;

    /**
     * @var \Ktpl\Helpdesk\Api\Data\HistoryInterfaceFactory
     */
    protected $historyInterfaceFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    protected $uploaderFactory;

    protected $createTicketInterfaceFactory;

    protected $departmentInterface;

    protected $orderDataInterface;

    protected $priorityInterface;

    protected $orderCollectionFactory;

    protected $helpdeskOrder;

    protected $priorityFactory;

    protected $attachmentInterfaceFactory;

    /**
     * TicketManagement constructor.
     * @param \Magento\Email\Model\TemplateFactory $emailTemplateFactory
     * @param Config $config
     * @param \Mirasvit\Helpdesk\Helper\Field $helpdeskField
     * @param \Mirasvit\Helpdesk\Helper\Process $helpdeskProcess
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollectionFactory
     * @param \Ktpl\Helpdesk\Api\Data\TicketSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Mirasvit\Helpdesk\Model\DepartmentFactory $departmentFactory
     * @param \Mirasvit\Helpdesk\Model\StatusFactory $statusFactory
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Message\CollectionFactory $messageCollectionFactory
     * @param \Ktpl\Helpdesk\Api\Data\HistoryInterfaceFactory $historyInterfaceFactory
     * @param \Magento\Customer\Model\Customer $customerModel
     */
    public function __construct(
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        Config $config,
        \Mirasvit\Helpdesk\Helper\Field $helpdeskField,
        \Mirasvit\Helpdesk\Helper\Process $helpdeskProcess,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollectionFactory,
        \Ktpl\Helpdesk\Api\Data\TicketSearchResultsInterfaceFactory $searchResultsFactory,
        \Mirasvit\Helpdesk\Model\DepartmentFactory $departmentFactory,
        \Mirasvit\Helpdesk\Model\StatusFactory $statusFactory,
        \Mirasvit\Helpdesk\Model\ResourceModel\Message\CollectionFactory $messageCollectionFactory,
        \Ktpl\Helpdesk\Api\Data\HistoryInterfaceFactory $historyInterfaceFactory,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Ktpl\Helpdesk\Api\Data\CreateTicketInterfaceFactory $createTicketInterfaceFactory,
        \Ktpl\Helpdesk\Api\Data\DepartmentInterfaceFactory $departmentInterface,
        \Ktpl\Helpdesk\Api\Data\OrderDataInterfaceFactory $orderDataInterface,
        \Ktpl\Helpdesk\Api\Data\PriorityInterfaceFactory $priorityInterface,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Mirasvit\Helpdesk\Helper\Order $helpdeskOrder,
        \Mirasvit\Helpdesk\Model\PriorityFactory $priorityFactory,
        \Ktpl\Helpdesk\Api\Data\AttachmentInterfaceFactory $attachmentInterfaceFactory
    ) {
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->config = $config;
        $this->helpdeskField = $helpdeskField;
        $this->helpdeskProcess = $helpdeskProcess;
        $this->scopeConfig = $scopeConfig;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->departmentFactory = $departmentFactory;
        $this->statusFactory = $statusFactory;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->historyInterfaceFactory = $historyInterfaceFactory;
        $this->customerModel = $customerModel;
        $this->uploaderFactory = $uploaderFactory;
        $this->createTicketInterfaceFactory = $createTicketInterfaceFactory;
        $this->departmentInterface = $departmentInterface;
        $this->orderDataInterface = $orderDataInterface;
        $this->priorityInterface = $priorityInterface;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->helpdeskOrder = $helpdeskOrder;
        $this->priorityFactory = $priorityFactory;
        $this->attachmentInterfaceFactory = $attachmentInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function createTicket($customerId)
    {
        $createTicket = $this->createTicketInterfaceFactory->create();

        $priorityCollection = $this->priorityFactory->create()->getCollection();
        $priorities = [];
        foreach ($priorityCollection as $priority) {
            $priorities[] = $this->priorityInterface->create()
                ->setPriorityId($priority->getId())
                ->setPriorityValue($priority->getName());
        }

        $departmentCollection = $this->departmentFactory->create()->getCollection();
        $departments = [];
        foreach ($departmentCollection as $department) {
            $departments[] = $this->departmentInterface->create()
                ->setDepartmentId($department->getDepartmentId())
                ->setDepartmentName($department->getName());
        }

        $orderCollection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('customer_id', (int)$customerId);
        $orders = [];
        foreach ($orderCollection as $order) {
            $orders[] = $this->orderDataInterface->create()
                ->setOrderId($order->getId())
                ->setOrderValue($this->helpdeskOrder->getOrderLabel($order, false));
        }


        $createTicket->setPriority($priorities);
        $createTicket->setDepartment($departments);
        $createTicket->setOrder($orders);
        $createTicket->setCustomerId($customerId);

        return $createTicket;
    }

    /**
     * @inheritdoc
     */
    public function submitTicket($customerName, $customerEmail, $subject, $description, $telephone)
    {
        $post['name'] = $customerName;
        $post['mail'] = $customerEmail;
        $post['comment'] = $description;
        $post['subject'] = $subject;
        $post['telephone'] = $telephone;
        $post['email'] = '';
        $post['hideit'] = '';

        try {
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($post);

            $error = false;

            if (!\Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
                $error = true;
            }

            if (!\Zend_Validate::is(trim($post['comment']), 'NotEmpty')) {
                $error = true;
            }

            if (!\Zend_Validate::is(trim($post['mail']), 'NotEmpty')) {
                $error = true;
            }

            if (!\Zend_Validate::is(trim($post['subject']), 'NotEmpty')) {
                $error = true;
            }

            if ($error) {
                throw new \Exception('Please enter required data');
            }

            try {
                $uploader = $this->uploaderFactory->create(['fileId' => 'attachment[0]']);
                $fileData = $uploader->validateFile();
                $hasPostFiles = $fileData && !empty($fileData['name']);
            } catch (\Exception $e) {
                $hasPostFiles = false;
            }

            if ($this->config->getGeneralContactUsIsActive()) {
                //POST
                //name
                //email
                //comment
                //telephone
                //priority_id
                //department_id
                $params = [];
                $params['subject'] = $post['subject'];
                $params['message'] = $post['comment'];
                if ($phone = $post['telephone']) {
                    $params['message'] .= "\n".__('Telephone').': '.$phone;
                }
                if (isset($post['priority_id'])) {
                    $params['priority_id'] = $post['priority_id'];
                }
                if (isset($post['department_id'])) {
                    $params['department_id'] = $post['department_id'];
                }

                $params['customer_name'] = $post['name'];
                $params['customer_email'] = $post['mail'];
                $collection = $this->helpdeskField->getContactFormCollection();
                foreach ($collection as $field) {
                    if (isset($post[$field->getCode()])) {
                        $params[$field->getCode()] = $post[$field->getCode()];
                    }
                }
                if (empty($post['email'])) { //spam protection
                    $this->helpdeskProcess->createFromPost($params, Config::CHANNEL_CONTACT_FORM);
                }
            } else {
                $mailTemplate = $this->emailTemplateFactory->create();
                /* @var $mailTemplate \Magento\Email\Model\Template */
                $mailTemplate->setDesignConfig(['area' => 'frontend'])
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        $this->scopeConfig->getValue(Form::XML_PATH_EMAIL_TEMPLATE),
                        $this->scopeConfig->getValue(Form::XML_PATH_EMAIL_SENDER),
                        $this->scopeConfig->getValue(Form::XML_PATH_EMAIL_RECIPIENT),
                        null,
                        ['data' => $postObject]
                    );

                if (!$mailTemplate->getSentSuccess()) {
                    throw new \Exception('Something went wrong while sending mail');
                }
            }
            $message =  'Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.';
            return $message;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new \Exception('Unable to submit your request. Please, try again later');
        }
    }

    /**
     * @inheritdoc
     */
    public function getList($customerId, \Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->ticketCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('folder', ['neq' => Config::FOLDER_SPAM])
            ->addOrder('created_at', 'DESC');

        $items = [];
        foreach ($collection as $item) {
            $departmentId = $item->getDepartmentId();
            $statusId = $item->getStatusId();
            $department = $this->departmentFactory->create()->load($departmentId);
            $status = $this->statusFactory->create()->load($statusId);
            $item->addData([
                'department_name' => $department->getName(),
                'status_title' => $status->getName()
            ]);
            $items[] = $item->getData();
        }

        $searchResults->setTotalCount($collection->getSize());
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $searchResults->setItems($items);
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getById($customerId, $ticketId)
    {
        $ticket = $this->ticketCollectionFactory->create()
            ->joinFields()
            ->addFieldToFilter('main_table.ticket_id', $ticketId)
            ->addFieldToFilter('main_table.customer_id', $customerId)
            ->getFirstItem();

        $status = $this->statusFactory->create()->load($ticket->getStatusId());
        $ticket->setStatusTitle($status->getName());
        $department = $this->departmentFactory->create()->load($ticket->getDepartmentId());
        $ticket->setDepartmentName($department->getName());
        $ticketMessageCollection = $this->messageCollectionFactory->create();
        $ticketMessageCollection
            ->addFieldToFilter('ticket_id', $ticketId)
            ->setOrder('created_at', 'desc');

        $ticketHistory = [];
        foreach ($ticketMessageCollection as $ticketMessage) {
            $department = $this->departmentFactory->create()->load($ticketMessage->getUserId());
            $customerName = $ticketMessage->getUserName();
            if ($ticketMessage->getCustomerName()) {
                $customerName = $ticketMessage->getCustomerName();
            }

            $history = $this->historyInterfaceFactory->create();
            $history->setDepartmentName($department->getName());
            $history->setDate($ticketMessage->getCreatedAt());
            $history->setFrom($customerName);
            $history->setMessage($ticketMessage->getBody());

            $attachments = [];
            foreach ($ticketMessage->getAttachments() as $attachmentData) {
                $attachment = $this->attachmentInterfaceFactory->create();
                $attachmentLink = '';
                if ($attachmentData->getIsAllowed()) {
                    $attachmentLink = $attachmentData->getUrl().$attachmentData->getName();
                }
                $attachmentName = $attachmentData->getName();
                $attachment->setName($attachmentName);
                $attachment->setLink($attachmentLink);
                $attachments[] = $attachment;
            }

            $history->setAttachments($attachments);
            $ticketHistory[] = $history;
        }
        $ticket->setHistory($ticketHistory);
        return $ticket;
    }

    /**
     * @inheritdoc
     */
    public function submitMessage($customerId, $ticketId, $message, $close)
    {
        $ticket = $this->ticketCollectionFactory->create()
            ->joinFields()
            ->addFieldToFilter('main_table.ticket_id', $ticketId)
            ->addFieldToFilter('main_table.customer_id', $customerId)
            ->getFirstItem();
        if ($ticket->getId() > 0) {
            //Use direct model because in addMessage Mirasvit use getName() function
            $customer = $this->customerModel->load($customerId);

            try {
                $uploader = $this->uploaderFactory->create(['fileId' => 'attachment[0]']);
                $fileData = $uploader->validateFile();
                $hasPostFiles = $fileData && !empty($fileData['name']);
            } catch (\Exception $e) {
                $hasPostFiles = false;
            }

            try {
                if ($message || $hasPostFiles) {
                    $ticket->addMessage($message, $customer, false, Config::CUSTOMER,
                        Config::MESSAGE_PUBLIC, false, Config::FORMAT_PLAIN);
                    $message = __('Your message was successfuly posted');
                }
                if ($ticket && $close) {
                    $ticket->close();
                    $message = __('Ticket was successfuly closed');
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $message = __('Something went wrong');
            }
            return $message->getText();
        }
    }
}
