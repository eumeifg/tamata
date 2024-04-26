<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_Commissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\Commissions\Block\Adminhtml\VendorGroupCommission\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magedelight\Commissions\Model\Source\Status;
use Magedelight\Commissions\Model\Source\CalculationType;

/**
 * @author Rocket Bazaar Core Team
 */
class VendorGroupCommission extends GenericForm implements TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var status
     */
    protected $status;
    /**
     * @var CalculationType
     */
    protected $calculationType;
    
    /**
     * @var \MDC\Commissions\Model\Source\VendorGroup
     */
    protected $vendorGroups;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \MDC\Commissions\Model\Source\VendorGroup $vendorGroups
     * @param Status $status
     * @param CalculationType $calculationType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \MDC\Commissions\Model\Source\VendorGroup $vendorGroups,
        Status $status,
        CalculationType $calculationType,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->status = $status;
        $this->calculationType = $calculationType;
        $this->vendorGroups = $vendorGroups;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $commission = $this->_coreRegistry->registry('vendor_group_commission');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('vendorgroup_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Vendor Group Commission & Fees Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        if ($commission->getId()) {
            $fieldset->addField(
                'vendor_group_commission_id',
                'hidden',
                [
                    'name' => 'vendor_group_commission_id'
                ]
            );
        }
        
        $fieldset->addField(
            'vendor_group_id',
            'select',
            [
                'name' => 'vendor_group_id',
                'label' => __('Vendor Group'),
                'title' => __('Vendor Group'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'values' => $this->vendorGroups->toOptionArray()
            ]
        );
        
        $fieldset->addField(
            'calculation_type',
            'select',
            [
                'name' => 'calculation_type',
                'label' => __('Calculation Type'),
                'title' => __('Calculation Type'),
                'values' => $this->calculationType->toOptionArray()
            ]
        );
        
        $fieldset->addField(
            'commission_value',
            'text',
            [
                'name' => 'commission_value',
                'label' => __('Commission'),
                'title' => __('Value'),
                'class' => 'validate-number validate-not-negative-number',
                'required' => true
            ]
        );
        
        $fieldset->addField(
            'marketplace_fee_type',
            'select',
            [
                'name' => 'marketplace_fee_type',
                'label' => __('Marketplace Fee Commission Type'),
                'title' => __('Marketplace Fee Commission Type'),
                'values' => $this->calculationType->toOptionArray(),
                'note' => __('Selected calculation type is considered for Marketplace fee calculation')
            ]
        );

        $fieldset->addField(
            'marketplace_fee',
            'text',
            [
                'name' => 'marketplace_fee',
                'label' => __('Marketplace Fee Commission'),
                'title' => __('Marketplace Fee Value'),
                'required' => true,
                'note' => __('Marketplace fee to be paid either in percentage or flat as per the selection')
            ]
        );
        
        $fieldset->addField(
            'cancellation_calculation_type',
            'select',
            [
                'name' => 'cancellation_calculation_type',
                'label' => __('Cancellation Fee Commission Type'),
                'title' => __('Cancellation Fee Commission Type'),
                'values' => $this->calculationType->toOptionArray(),
                'note' => __('Selected calculation type is considered for cancellation calculation')
            ]
        );

        $fieldset->addField(
            'cancellation_commission_value',
            'text',
            [
                'name' => 'cancellation_commission_value',
                'label' => __('Cancellation Fee Commission'),
                'title' => __('Cancellation Fee Value'),
                'required' => true,
                'note' => __('Enter the value for the cancellation fee charged to be levied')
            ]
        );

        $websites = $fieldset->addField(
            'website_id',
            'select',
            [
                'name' => 'website_id',
                'class' => 'custom-select',
                'label' => __('Website'),
                'title' => __('Website'),
                'required' => true,
                'values' => $this->_systemStore->getWebsiteValuesForForm(true)
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'values' => $this->status->toOptionArray()
            ]
        );

        $this->_eventManager->dispatch(
            'adminhtml_vendorgroup_commission_page_edit_tab_main_prepare_form',
            ['form' => $form]
        );

        $form->addValues($commission->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Vendor Group Commission');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
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
}
