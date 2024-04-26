<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Controller\Sellerhtml\SalesRule;

use Magento\Framework\DataObject;
use Magento\SalesRule\Model\Rule;

class Save extends AbstractAction
{

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $_coupon;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $custGroups;
    
    protected $date;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $custGroups
     * @param \Magento\SalesRule\Model\Coupon $coupon
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $date
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\VendorPromotion\Helper\Data $helper,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Customer\Model\ResourceModel\Group\Collection $custGroups,
        \Magento\SalesRule\Model\Coupon $coupon,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $date
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->custGroups = $custGroups;
        $this->_coupon = $coupon;
        $this->date = $date;
        parent::__construct($context, $helper);
    }
    
    protected function _filterDates($requestData)
    {
        $dateFilter = $this->date;
        $inputFilter = new \Zend_Filter_Input(
            ['from_date' => $dateFilter],
            [],
            $requestData
        );
        $requestData = $inputFilter->getUnescaped();
        return $requestData;
    }

    public function execute()
    {
        $tab = $this->getRequest()->getParam('tab');
        if ($this->getRequest()->getPost()) {
            try {
                if (!$this->validation($this->getRequest()->getPost())) {
                    return $this->_redirect('*/*/new', ['tab' => $tab]);
                }
                $model = $this->ruleFactory->create();
                $this->_eventManager->dispatch(
                    'adminhtml_controller_salesrule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = (array)$this->getRequest()->getPost();
                $data = $this->_filterDates($data);
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $this->validateRule();
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Exception(__('Rule not found.'));
                    }
                    $vendorIds = explode(',', $model->getVendorId());
                    if (!in_array($this->_auth->getUser()->getVendorId(), $vendorIds)) {
                        throw new \Exception(__('Rule not found.'));
                    }
                } else {
                    $vendorIds = [];
                    if (!$this->validateCoupenCode()) {
                        return $this->_redirect('*/*/new', ['tab' => $tab]);
                    }
                }
                $vendorIds[] = $this->_auth->getUser()->getVendorId();
                $data['vendor_id'] = array_unique($vendorIds);
                $validateResult = $model->validateData(new DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    return $this->_redirect('*/*/edit', ['id'=>$model->getId()]);
                }
                if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                    && isset($data['discount_amount'])) {
                    $data['discount_amount'] = min(100, $data['discount_amount']);
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                $data['stop_rules_processing'] = 0;
                unset($data['rule']);
                $data['coupon_code'] = $this->getRequest()->getParam('coupon_code');
                if (!empty($data['coupon_code'])) {
                    $data['coupon_type'] = Rule::COUPON_TYPE_SPECIFIC;
                }
                $model->loadPost($data);
                $model->save();
                $this->messageManager->addSuccess(__('The rule has been saved.'));
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', ['id' => $model->getId(), 'tab' => $tab]);
                }
                return $this->_redirect('*/*', ['tab' => $tab]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    return $this->_redirect('*/*/edit', ['id' => $id, 'tab' => $tab]);
                } else {
                    return $this->_redirect('*/*/new', ['tab' => $tab]);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while saving the rule data.')
                );
                return $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('rule_id'), 'tab' => $tab]);
            }
        }
        $this->messageManager->addError(__('Unable to find a data to save'));
        return $this->_redirectRuleAfterPost();
    }
    public function validateRule()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        $vendorId = $this->_auth->getUser()->getVendorId();
        $collection = $this->ruleFactory->create()->getCollection()
            ->addFieldToFilter('rule_id', $ruleId)
            ->addFieldToFilter('vendor_id', ['finset' => $vendorId]);

        $collection->load();
        $tab = $this->getRequest()->getParam('tab');
        if (!$collection->getFirstItem()->getId()) {
            $this->messageManager->addErrorMessage(__('Rule Not Found'));
            return $this->_redirect('*/*', ['tab' => $tab]);
        }
        return $this;
    }
    
    public function validateCoupenCode()
    {
        $couponCode = $this->getRequest()->getParam('coupon_code');
        if (!empty($couponCode)) {
            $ruleId = $this->_coupon->loadByCode($couponCode)->getRuleId();
            if (empty($ruleId)) {
                return true;
            } else {
                $tab = $this->getRequest()->getParam('tab');
                $this->messageManager->addErrorMessage(__('You are not authorised to use this coupon code. Please try other than this.'));
                return false;
            }
        }
        return true;
    }

    protected function validation($data)
    {
        if (!empty($data) && !array_key_exists('name', $data) && empty($data['name'])) {
            $this->messageManager->addErrorMessage(__('Name is a required field.'));
            return false;
        } elseif (!array_key_exists('is_active', $data) && empty($data['is_active'])) {
            $this->messageManager->addErrorMessage(__('Status is a required field.'));
            return false;
        } elseif (!array_key_exists('website_ids', $data) && empty($data['webisite_ids'])) {
            $this->messageManager->addErrorMessage(__('Please select website.'));
            return false;
        } elseif (!array_key_exists('customer_group_ids', $data) && empty($data['customer_group_ids'])) {
            $this->messageManager->addErrorMessage(__('Please select Customer Group.'));
            return false;
        } elseif (!array_key_exists('discount_amount', $data) && empty($data['discount_amount'])) {
            $this->messageManager->addErrorMessage(__('Please Add Discount Amount.'));
            return false;
        } else {
            return true;
        }
    }
}
