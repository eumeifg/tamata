<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magedelight\Catalog\Model\ProductRequest;

class Actionlink extends AbstractRenderer
{
    protected $productRequest;
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productRequest = $productRequestFactory->create();
        $this->_authorization = $context->getAuthorization();
    }

    /**
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $params = [
            'id'=> $row->getId() ,
            'existing' => $this->getRequest()->getParam('existing', 0),
            'status' => $this->getRequest()->getParam('status', 0),
            'store' => $row->getStoreId()
        ];
        $url=$this->getUrl('*/*/edit', $params);
        return sprintf("<a href='%s'>%s</a>", $url, 'Edit');
    }
}
