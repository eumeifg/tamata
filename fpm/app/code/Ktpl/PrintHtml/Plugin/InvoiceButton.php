<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\PrintHtml\Plugin;

class InvoiceButton
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

    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    public function beforeGetInvoice(\Magento\Sales\Block\Adminhtml\Order\Invoice\View $subject){

         $invoice = $this->getInvoice();
         $url = $this->_backendUrl->getUrl("printhtml/invoice",['invoice_id' => $invoice->getId()]);
         //$url = $this->_backendUrl->getUrl("printhtml/index");

         $subject->addButton(
                    'generate_invoice_html',
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