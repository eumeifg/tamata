<?php

namespace Magedelight\Vendor\Model;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Logging\Model\Event;

class Logging
{
    /**
     * @var ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     */
    public function __construct(
        ManagerInterface $messageManager,
        RequestInterface $request
    )
    {
        $this->_messageManager = $messageManager;
        $this->_request = $request;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function postDispatchVendorSave(array $config, Event $eventModel): Event
    {
        $messages = $this->_messageManager->getMessages();
        $errors = $messages->getErrors();
        $notices = $messages->getItemsByType(\Magento\Framework\Message\MessageInterface::TYPE_NOTICE);
        $isSuccess = empty($errors) && empty($notices);
        return $eventModel;//->setIsSuccess($isSuccess)->setInfo($this->_request->getParam('vendor'));
    }
}
