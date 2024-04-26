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
namespace Magedelight\Sales\Block\Adminhtml\Order\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Actionlink extends AbstractRenderer
{
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    public function render(DataObject $row)
    {
        $params = ['order_id'=>$row->getOrderId() ,'vendor_id' => $row->getVendorId(),'storeId' => $row->getStoreId()];
        $status = $this->getRequest()->getParam('status', false);
        if ($status) {
            $params['status'] = $status;
        }
        $params['vendor_order_id'] = $row->getId();
        $url=$this->getUrl('*/*/view', $params);
        return sprintf("<a href='%s'>%s</a>", $url, 'View');
    }
}
