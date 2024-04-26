<?php

namespace Magedelight\Catalog\Model;

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
    public function __construct(
        RequestInterface $request
    )
    {
        $this->_request = $request;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     */
    public function postDispatchProductMassDelete(array $config, Event $eventModel): Event
    {
        $data = $this->_request->getParam('vendor_product') ? implode(',', $this->_request->getParam('vendor_product')) : '';
        $eventModel->setInfo($data);
        return $eventModel;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     */
    public function postDispatchProductMassUnList(array $config, Event $eventModel): Event
    {
        $data = $this->_request->getParam('vendor_product') ? implode(',', $this->_request->getParam('vendor_product')) : '';
        $eventModel->setInfo($data);
        return $eventModel;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     */
    public function postDispatchProductMassList(array $config, Event $eventModel): Event
    {
        $data = $this->_request->getParam('vendor_product') ? implode(',', $this->_request->getParam('vendor_product')) : '';
        $eventModel->setInfo($data);
        return $eventModel;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     */
    public function postDispatchListProduct(array $config, Event $eventModel): Event
    {
        $id = $this->_request->getParam('id');
        $id = $id ?? '';
        $eventModel->setInfo($id);
        return $eventModel;
    }

    /**
     * @param array $config
     * @param Event $eventModel
     * @return Event
     */
    public function postDispatchUnListProduct(array $config, Event $eventModel): Event
    {
        $id = $this->_request->getParam('id');
        $id = $id ?? '';
        $eventModel->setInfo($id);
        return $eventModel;
    }
}
