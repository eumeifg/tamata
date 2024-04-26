<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Block\Adminhtml\Catalog\Product\Edit\Tab\Alerts;

class Stock extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'index' => 'firstname',
                'renderer' => 'Magedelight\ProductAlert\Block\Adminhtml\Catalog\Product\Edit\Tab\Alerts\Renderer\FirstName',
            ]
        );

        $this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
                'index' => 'lastname',
                'renderer' => 'Magedelight\ProductAlert\Block\Adminhtml\Catalog\Product\Edit\Tab\Alerts\Renderer\LastName',
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'renderer' => 'Magedelight\ProductAlert\Block\Adminhtml\Catalog\Product\Edit\Tab\Alerts\Renderer\Email',
            ]
        );


        $this->addColumn(
            'add_date',
            [
                'header' => __('Date Subscribed'),
                'index' => 'add_date',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'send_date',
            [
                'header' => __('Last Notification'),
                'index' => 'send_date',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'send_count',
            [
                'header' => __('Send Count'),
                'index' => 'send_count',
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getAlertStockId',
                'actions' => [
                    [
                        'caption' => __('Remove'),
                        'url' => [
                            'base' => 'productalert/stock/delete',
                            'params' => [
                                'store' => $this->getRequest()->getParam(
                                    'store'
                                )
                            ]
                        ],
                        'field' => 'alert_stock_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'alert_stock_id',
            ]
        );
        return $this;
    }
}
