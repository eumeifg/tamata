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

class Price extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Price
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
            'price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'type' => 'currency',
                'currency_code' => $this->_scopeConfig->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE)
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
            'last_send_date',
            [
                'header' => __('Last Notification'),
                'index' => 'last_send_date',
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
        return $this;
    }
}
