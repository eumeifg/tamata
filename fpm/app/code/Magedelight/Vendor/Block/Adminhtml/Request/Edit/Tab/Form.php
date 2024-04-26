<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Adminhtml\Request\Edit\Tab;

use Magedelight\Vendor\Model\Source\RequestStatuses;
use Magedelight\Vendor\Model\Source\RequestTypes;
use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Form extends GenericForm implements TabInterface
{
    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Vendor\Model\Source\RequestStatuses
     */
    protected $requestStatuses;

    /**
     * @var RequestTypes
     */
    protected $requestTypes;
    /**
     * @var \Magedelight\Vendor\Model\Source\Status
     */
    private $vendorStatuses;
    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory
     */
    private $vendorCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param RequestStatuses $requestStatuses
     * @param RequestTypes $requestTypes
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param array $data
     * @param \Magedelight\Vendor\Model\Source\Status $vendorStatuses
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Vendor\Model\Source\RequestStatuses $requestStatuses,
        \Magedelight\Vendor\Model\Source\RequestTypes $requestTypes,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        array $data = [],
        \Magedelight\Vendor\Model\Source\Status $vendorStatuses,
        \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
    ) {
        $this->requestStatuses = $requestStatuses;
        $this->requestTypes = $requestTypes;
        $this->vendorRepository = $vendorRepository;

        parent::__construct($context, $registry, $formFactory, $data);
        $this->vendorStatuses = $vendorStatuses;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
    }

    /**
     * Prepare label for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Request Information');
    }

    /**
     * Prepare title for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Request Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareForm()
    {
        /** @var $model \Ves\Brand\Model\Brand */
        $model = $this->_coreRegistry->registry('vendor_status_request');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('request_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Request Information')]);

        if ($model) {
            $fieldset->addField('request_id', 'hidden', ['name' => 'request_id']);
            $fieldset->addField('vendor_id', 'hidden', ['name' => 'vendor_id']);
            $vendor = $this->vendorRepository->getById($model->getVendorId());
            $model->setVacationFromDate($vendor->getData('vacation_from_date'));
            $model->setVacationToDate($vendor->getData('vacation_to_date'));
        }

        if ($model) {
            $fieldset->addField(
                'vendor_name',
                'link',
                [
                    'name'   => 'vendor_name',
                    'label'  => __('Vendor Name'),
                    'title'  => __('Vendor Name'),
                    'href'   => $this->getUrl('vendor/index/edit', ['vendor_id' => $model->getVendorId()]),
                    'target' => '_blank',
                ]
            );
        } else {
            $collection = $this->vendorCollectionFactory->create();
            $collection->getSelect()->joinLeft(
                ['rvwd'=>'md_vendor_website_data'],
                'rvwd.vendor_id = main_table.vendor_id',
                ['business_name','name']
            );
            $collection->addFieldToFilter('rvwd.email_verified', ['eq' => 1]);

            $itemsArray = [];

            foreach ($collection as $item) {
                $itemsArray[$item->getVendorId()] = $this->getVendorName($item) . ' ('
                    . $this->getOptionText($item->getStatus()) . ')';
            }
            $fieldset->addField(
                'vendor_name',
                'select',
                [
                    'name'     => 'vendor_name',
                    'class'    => 'custom-select',
                    'label'    => __('Vendor Name'),
                    'title'    => __('Vendor Name'),
                    'values'   => $itemsArray,
                    'required' => true,
                ]
            )->setAfterElementHtml('
                <script type="text/javascript">
                    require([
                         "jquery",
                    ], function($){
                        $(window).load(function() {
                            $("#request_vendor_name").customselect();
                        });
                      });
               </script>
            ');
        }

        $fieldset->addField(
            'request_type',
            'select',
            [
                'name'   => 'request_type',
                'label'  => __('Request Type'),
                'title'  => __('Request Type'),
                'values' => $this->requestTypes->toOptionArray()
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormatWithLongYear();
        $fieldset->addField(
            'vacation_from_date',
            'date',
            [
                'name'        => 'vacation_from_date',
                'label'       => __('Vacation From Date'),
                'title'       => __('Vacation From Date'),
                //'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat,
                'required'    => false,
            ]
        );

        $fieldset->addField(
            'vacation_to_date',
            'date',
            [
                'date_format' => $dateFormat,
                'name'        => 'vacation_to_date',
                'label'       => __('Vacation To Date'),
                'title'       => __('Vacation To Date'),
                //'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'required'    => false,
            ]
        );

        $fieldset->addField(
            'reason',
            'textarea',
            [
                'name'     => 'reason',
                'label'    => __('Reason'),
                'title'    => __('Reason')
            ]
        );

        if ($model) {
            $fieldset->addField(
                'requested_at',
                'text',
                [
                    'name'     => 'requested_at',
                    'label'    => __('Requested At'),
                    'title'    => __('Requested At')
                ]
            );
        }

        if ($model) {
            if ($model->getStatus() == RequestStatuses::VENDOR_REQUEST_STATUS_APPROVED) {
                $fieldset->addField(
                    'approved_at',
                    'text',
                    [
                        'name'     => 'approved_at',
                        'label'    => __('Approved At'),
                        'title'    => __('Approved At'),
                        'readonly' => true
                    ]
                );
            }
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'label'  => __('Status'),
                'title'  => __('Page Status'),
                'name'   => 'status',
                'values' => $this->requestStatuses->toOptionArray(),
                'note'   => __('Status operations of request auto assigned to vendor profile.')

            ]
        );

        if ($model) {
            $this->setChild(
                'form_after',
                $this->getLayout()->createBlock(
                    \Magento\Backend\Block\Widget\Form\Element\Dependence::class
                )->addFieldMap(
                    "{$htmlIdPrefix}request_type",
                    'request_type'
                )
                    ->addFieldMap(
                        "{$htmlIdPrefix}vacation_from",
                        'vacation_from'
                    )->addFieldMap(
                        "{$htmlIdPrefix}vacation_to",
                        'vacation_to'
                    )->addFieldDependence(
                        'vacation_from',
                        'request_type',
                        RequestTypes::VENDOR_REQUEST_TYPE_VACATION
                    )->addFieldDependence(
                        'vacation_to',
                        'request_type',
                        RequestTypes::VENDOR_REQUEST_TYPE_VACATION
                    )
            );
        }
        if ($model) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     *
     * @param string $optionId
     * @return string .
     */
    public function getOptionText($optionId = '')
    {
        return $this->vendorStatuses->getOptionText($optionId);
    }

    /**
     * @param $vendor
     * @return mixed
     */
    protected function getVendorName($vendor)
    {
        if ($vendor->getVendorId()) {
            $data = array_values(array_filter([$vendor->getBusinessName(), $vendor->getName()]));
            return $data[0];
        }
    }
}
