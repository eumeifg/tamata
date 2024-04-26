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

class Shipmentlink extends AbstractRenderer
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
        $url=$this->getUrl(
            'adminhtml/order_shipment/view',
            ['shipment_id'=>$row->getEntityId() ,'storeId' => $row->getStoreId()]
        );
        return sprintf("<a href='%s'>%s</a>", $url, 'View');
    }
}
