<?php
namespace Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Messages;

use Magento\SalesRule\Model\RegistryConstants;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Sales rule coupon
     *
     * @var \Magento\SalesRule\Helper\Coupon
     */
    protected $_salesRuleCoupon = null;

    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\SalesRule\Helper\Coupon $salesRuleCoupon
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\SalesRule\Helper\Coupon $salesRuleCoupon,
        \Cminds\Coupon\Helper\Data $cmindsHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Cminds\Coupon\Model\Error $error,

        array $data = []
    )
    {
        $this->_salesRuleCoupon = $salesRuleCoupon;
        $this->data = $cmindsHelper;
        $this->error = $error;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare coupon codes generation parameters form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {

        $priceRule = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);

        $params = $this->getRequest()->getParams();

        if(isset($params['id'])) {
            $errorsCollection = $this->error->load($params['id'],
                'rule_id')->getData();
        } else {
            $errorsCollection = [];
        }



        if ($this->data->isCouponErrorModuleEnabled()) {
            /** @var \Magento\Framework\Data\Form $form */
            $form = $this->_formFactory->create(
                [
                    'data' => [
                        'id' => 'edit_form',
                        'method' => 'post',
                        'action' => $this->getData('action')
                    ]
                ]
            );

            $fieldset = $form->addFieldset('general', []);
            $fieldset->addClass('ignore-validate');

            if(!isset($params['id'])){
                $fieldset->addField(
                    'before_load_note',
                    'note',
                    [
                        'text' => __('Please save rule before you can enter custom error messages.'),
                    ]
                );
            } else {
                $fieldset->addField(
                    'coupon_not_apply_rule',
                    'text',
                    [
                        'name' => 'coupon_not_apply_rule',
                        'label' => __('Coupon code exist but do not apply to the rule conditions'),
                        'title' => __('Coupon code exist but do not apply to the rule conditions'),
                        'note' => __('You can use shortcode %s to display the coupon code used by the customer'),
                        'value' => (isset($errorsCollection['coupon_not_apply_rule'])) ? $errorsCollection['coupon_not_apply_rule'] : '',
                        'id' => 'coupon_not_apply_rule',
                        'data-form-part' => 'sales_rule_form'
                    ]
                );
                $fieldset->addField(
                    'coupon_expired',
                    'text',
                    [
                        'name' => 'coupon_expired',
                        'label' => __('Coupon code exist but is expired'),
                        'title' => __('Coupon code exist but is expired'),
                        'note' => __('You can use shortcode %s to display the coupon code used by the customer'),
                        'value' => (isset($errorsCollection['coupon_expired'])) ? $errorsCollection['coupon_expired'] : '',
                        'id' => 'coupon_expired',
                        'data-form-part' => 'sales_rule_form'
                    ]
                );
                $fieldset->addField(
                    'customer_not_belong_group',
                    'text',
                    [
                        'name' => 'customer_not_belong_group',
                        'label' => __("Customer doesn't belong to the assigned customer group"),
                        'title' => __("Customer doesn't belong to the assigned customer group"),
                        'note' => __('You can use shortcode %s to display the coupon code used by the customer'),
                        'value' => (isset($errorsCollection['customer_not_belong_group'])) ? $errorsCollection['customer_not_belong_group'] : '',
                        'id' => 'customer_not_belong_group',
                        'data-form-part' => 'sales_rule_form'
                    ]
                );
                $fieldset->addField(
                    'coupon_used_multiple',
                    'text',
                    [
                        'name' => 'coupon_used_multiple',
                        'label' => __('Message when coupon was used more than it can be used'),
                        'title' => __('Message when coupon was used more than it can be used'),
                        'note' => __('You can use shortcode %s to display the coupon code used by the customer'),
                        'value' => (isset($errorsCollection['coupon_used_multiple'])) ? $errorsCollection['coupon_used_multiple'] : '',
                        'id' => 'coupon_used_multiple',
                        'data-form-part' => 'sales_rule_form'
                    ]
                );
                $fieldset->addField(
                    'coupon_used_multiple_customer_group',
                    'text',
                    [
                        'name' => 'coupon_used_multiple_customer_group',
                        'label' => __('Message when coupon was used more than it can be used in customer group'),
                        'title' => __('Message when coupon was used more than it can be used in customer group'),
                        'note' => __('You can use shortcode %s to display the coupon code used by the customer'),
                        'value' => (isset($errorsCollection['coupon_used_multiple_customer_group'])) ? $errorsCollection['coupon_used_multiple_customer_group'] : '',
                        'id' => 'coupon_used_multiple_customer_group',
                        'data-form-part' => 'sales_rule_form'
                    ]
                );
                $fieldset->addField(
                    'coupon_other_messages',
                    'text',
                    [
                        'name' => 'coupon_other_messages',
                        'label' => __('Any other error messages applies'),
                        'title' => __('Any other error messages applies'),
                        'note' => __('You can use shortcode %s to display the coupon code used by the customer'),
                        'value' => (isset($errorsCollection['coupon_other_messages'])) ? $errorsCollection['coupon_other_messages'] : '',
                        'id' => 'coupon_other_messages',
                        'data-form-part' => 'sales_rule_form'
                    ]
                );

            }
                $this->setForm($form);

                $this->_eventManager->dispatch(
                    'adminhtml_promo_quote_edit_tab_messages_form_prepare_form',
                    ['form' => $form]
                );

            return parent::_prepareForm();
        }
    }

    /**
     * Retrieve URL to Generate Action
     *
     * @return string
     */
    public function getGenerateUrl()
    {
        return $this->getUrl('sales_rule/*/generate');
    }
}
