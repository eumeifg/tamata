<?php

namespace Magedelight\OffersImportExport\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Logging\Model\Event;

class Logging
{

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->_request = $request;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     */
    public function postDispatchViewAddOffers(array $config, Event $eventModel): Event
    {
        $eventModel->setInfo('Offer Add View');
        return $eventModel;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     */
    public function postDispatchSaveAddOffers(array $config, Event $eventModel): Event
    {
        $eventModel->setInfo(json_encode($_FILES));
        return $eventModel;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     */
    public function postDispatchViewUpdateOffers(array $config, Event $eventModel): Event
    {
        $eventModel->setInfo('Offer Update View');
        return $eventModel;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     */
    public function postDispatchUpdateOffers(array $config, Event $eventModel): Event
    {
        $eventModel->setInfo(json_encode($_FILES));
        return $eventModel;
    }
}
