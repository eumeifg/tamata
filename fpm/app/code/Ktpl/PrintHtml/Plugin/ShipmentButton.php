<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\PrintHtml\Plugin;

class ShipmentButton
{
    protected $_backendUrl;
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Registry $registry
    ) {
        $this->_backendUrl = $backendUrl;
        $this->_coreRegistry = $registry;
    }

    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    public function beforeGetShipment(\Magento\Shipping\Block\Adminhtml\View $subject){

         $shipment = $this->getShipment();
         $url = $this->_backendUrl->getUrl("printhtml/index",['shipment_id' => $shipment->getId(),'shipmentdata' => $shipment]);
         //$url = $this->_backendUrl->getUrl("printhtml/index");

         $subject->addButton(
                    'generate_shipment_html',
                    [
                        'label' => __('Generate HTML'),
                        'onclick' => 'window.open( \''.$url.'\')',
                        'class' => 'reset'
                        //'onclick' => 'setLocation("'.$url.'")',
                    ],
                    -1
                );

        return null;
    }
}