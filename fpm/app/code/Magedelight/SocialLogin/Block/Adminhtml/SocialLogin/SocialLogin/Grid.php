<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\SocialLogin\Block\Adminhtml\SocialLogin\SocialLogin;

/**
 * Adminhtml sales report by category grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends \Magedelight\SocialLogin\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'entity_id';

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return 'Magedelight\SocialLogin\Model\ResourceModel\Report\Social\Collection';
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Customer Id'),
                'index' => 'entity_id',
                'sortable' => true,
                'totals_label' => __('Total'),
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-customer-id',
                'column_css_class' => 'col-customer-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header'       => __('Customer Name'),
                'sortable'     => true,
                'index'        => ['firstname', 'lastname'],
                'type'         => 'concat',
                'separator'    => ' ',
                'filter_index' => "CONCAT(customer_entity.firstname, ' -- ', customer_entity.lastname)",
                'width'        => '140px',
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Customer Email'),
                'index' => 'email',
                'totals_label' => __(''),
                'sortable' => false,
                'header_css_class' => 'col-customer-email',
                'column_css_class' => 'col-customer-email'
            ]
        );
        
        $this->addColumn(
            'created_at',
            [
                'header' => __('Registration Date'),
                'index' => 'created_at',
                'sortable' => false,
                'header_css_class' => 'col-subtotal',
                'column_css_class' => 'col-subtotal'
            ]
        );
        $this->addColumn(
            'website_name',
            [
                'header' => __('Website Name'),
                'index' => 'website_name',
                'sortable' => false,
                'header_css_class' => 'col-subtotal',
                'column_css_class' => 'col-subtotal'
            ]
        );
          $this->addColumn(
              'store_name',
              [
                'header' => __('Store Name'),
                'index' => 'store_name',
                'sortable' => false,
                'header_css_class' => 'col-subtotal',
                'column_css_class' => 'col-subtotal'
              ]
          );

           $this->addColumn(
               'sociallogin_type',
               [
                'header' => __('Social Site'),
                'index' => 'sociallogin_type',
                'sortable' => false,
                'header_css_class' => 'col-subtotal',
                'column_css_class' => 'col-subtotal'
               ]
           );
        
        $this->addExportType('*/*/exportSocialloginCsv', __('CSV'));
        $this->addExportType('*/*/exportSocialloginExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
