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
namespace MDC\Commissions\Block\Adminhtml\VendorGroupCommission;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends FormContainer
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize commission edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'vendor_group_commission_id';
        $this->_blockGroup = 'MDC_Commissions';
        $this->_controller = 'adminhtml_vendorGroupCommission';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save'));

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]
                ]
            ],
            -100
        );

        $this->buttonList->update('delete', 'label', __('Delete'));
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $commission = $this->coreRegistry->registry('vendor_group_commission');
        if ($commission->getId()) {
            return __("Edit Commission '%1'", $this->escapeHtml($commission->getTitle()));
        } else {
            return __('New Vendor Group Commission');
        }
    }
}
