<?php

namespace Magedelight\Abandonedcart\Block\Adminhtml\Report\Filter;

/**
 * Adminhtml report filter form
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Report type options
     *
     * @var array
     */
    protected $_reportTypeOptions = [];

    /**
     * Report field visibility
     *
     * @var array
     */
    protected $_fieldVisibility = [];

    /**
     * Report field opions
     *
     * @var array
     */
    protected $_fieldOptions = [];

    /**
     * Category collection factory
     *
     */
    protected $_socialLoginFactory;

    /**
     * Category helper
     *
     */
    protected $_categoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
     * @param \Magento\Catalog\Helper\Category $categoryHelper,
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Abandonedcart\Model\ResourceModel\Report\CollectionFactory $reportsCollectionFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = []
    ) {
        $this->reportsCollectionFactory = $reportsCollectionFactory;
        $this->_categoryHelper = $categoryHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getCustomerEmails()
    {

        $customerCollection = $this->reportsCollectionFactory->create();
        $customerCollection->getSelect()->group('customer_id');
        $customerEmailArr = [];
        foreach ($customerCollection as $email) {
            if (!in_array($email->getEmail(), $customerEmailArr)) {
                $customerEmailArr[] = $email->getEmail();
            }
        }
       /* echo '<pre>';
        print_r($customerEmailArr);
        die();*/
        return $customerEmailArr;
    }

    /**
     * Set field visibility
     *
     * @param string $fieldId
     * @param bool $visibility
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function setFieldVisibility($fieldId, $visibility)
    {
        $this->_fieldVisibility[$fieldId] = (bool)$visibility;
    }

    /**
     * Get field visibility
     *
     * @param string $fieldId
     * @param bool $defaultVisibility
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFieldVisibility($fieldId, $defaultVisibility = true)
    {
        if (!array_key_exists($fieldId, $this->_fieldVisibility)) {
            return $defaultVisibility;
        }
        return $this->_fieldVisibility[$fieldId];
    }

    /**
     * Set field option(s)
     *
     * @param string $fieldId Field id
     * @param mixed $option Field option name
     * @param mixed|null $value Field option value
     *
     * @return void
     */
    public function setFieldOption($fieldId, $option, $value = null)
    {
        if (is_array($option)) {
            $options = $option;
        } else {
            $options = [$option => $value];
        }
        if (!array_key_exists($fieldId, $this->_fieldOptions)) {
            $this->_fieldOptions[$fieldId] = [];
        }
        foreach ($options as $k => $v) {
            $this->_fieldOptions[$fieldId][$k] = $v;
        }
    }

    /**
     * Add report type option
     *
     * @param string $key
     * @param string $value
     * @return $this
     * @codeCoverageIgnore
     */
    public function addReportTypeOption($key, $value)
    {
        $this->_reportTypeOptions[$key] = __($value);
        return $this;
    }

    /**
     * Add fieldset with general report fields
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/mycustomreport');
        /*echo $actionUrl;
        die();*/

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => $actionUrl,
                    'method' => 'get'
                ]
            ]
        );

        $htmlIdPrefix = 'category_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Filter')]);

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset->addField('store_ids', 'hidden', ['name' => 'store_ids']);

        $CustomerEmaillist = $this->getCustomerEmails();

        $fieldset->addField(
            'period_type',
            'select',
            [
                'name' => 'period_type',
                'options' => ['day' => __('Day'), 'month' => __('Month'), 'year' => __('Year')],
                'label' => __('Period'),
                'title' => __('Period')
            ]
        );

        $fieldset->addField(
            'from',
            'date',
            [
                'name' => 'from',
                'date_format' => $dateFormat,
                'label' => __('Date From'),
                'title' => __('Date From'),
                'required' => true,
                'class' => 'admin__control-text'
            ]
        );

        $fieldset->addField(
            'to',
            'date',
            [
                'name' => 'to',
                'date_format' => $dateFormat,
                'label' => __('Date To'),
                'title' => __('Date To'),
                'required' => true,
                'class' => 'admin__control-text'
            ]
        );

         $fieldset->addField(
             'email',
             'select',
             [
                'name' => 'email',
                'label' => __('Customer Email'),
                'title' => __('Customer Email'),
                'values' => $CustomerEmaillist,
                'required' => true
             ]
         );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fields values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _initFormValues()
    {
        $data = $this->getFilterData()->getData();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                $data[$key] = explode(',', $value[0]);
            }
        }
        $this->getForm()->addValues($data);
        return parent::_initFormValues();
    }

    /**
     * This method is called before rendering HTML
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();

        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
            // apply field visibility
            foreach ($fieldset->getElements() as $field) {
                if (!$this->getFieldVisibility($field->getId())) {
                    $fieldset->removeField($field->getId());
                }
            }
            // apply field options
            foreach ($this->_fieldOptions as $fieldId => $fieldOptions) {
                $field = $fieldset->getElements()->searchById($fieldId);
                /** @var \Magento\Framework\DataObject $field */
                if ($field) {
                    foreach ($fieldOptions as $k => $v) {
                        $field->setDataUsingMethod($k, $v);
                    }
                }
            }
        }

        return $result;
    }
}
