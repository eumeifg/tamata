<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Controller\Adminhtml\Rule;

use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\Manager;
use Magento\Framework\Api\DataObjectHelper;
use Magedelight\Abandonedcart\Api\RuleRepositoryInterface;
use Magedelight\Abandonedcart\Api\Data\RuleInterface;
use Magedelight\Abandonedcart\Api\Data\RuleInterfaceFactory;
use Magedelight\Abandonedcart\Controller\Adminhtml\Rule;
use Magedelight\Abandonedcart\Model\EmailscheduleFactory;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Save extends Rule
{
    /**
     * @var Manager
     */
    protected $messageManager;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var RuleInterfaceFactory
     */
    public $ruleFactory;
    /**
     * @var EmailscheduleFactory
     */
    protected $emailSchedule;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Registry $registry,
     * @param RuleRepositoryInterface $ruleRepository,
     * @param PageFactory $resultPageFactory,
     * @param ForwardFactory $resultForwardFactory,
     * @param Manager $messageManager,
     * @param \Magedelight\Abandonedcart\Model\RuleFactory $ruleFactory,
     * @param DataObjectHelper $dataObjectHelper,
     * @param EmailscheduleFactory $emailSchedule,
     * @param \Magento\Framework\App\Request\Http $request,
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        RuleRepositoryInterface $ruleRepository,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Manager $messageManager,
        \Magedelight\Abandonedcart\Model\RuleFactory $ruleFactory,
        DataObjectHelper $dataObjectHelper,
        EmailscheduleFactory $emailSchedule,
        \Magento\Framework\App\Request\Http $request,
        Context $context,
        SerializerInterface $serializer
    ) {
        $this->messageManager   = $messageManager;
        $this->ruleFactory      = $ruleFactory;
        $this->ruleRepository   = $ruleRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->emailSchedule    = $emailSchedule;
        $this->request = $request;
        $this->serializer = $serializer;

        parent::__construct(
            $registry,
            $ruleRepository,
            $resultPageFactory,
            $resultForwardFactory,
            $ruleFactory,
            $context
        );
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('*/*/');
        }
        /** @var $model \\Magedelight\Abandonedcart\Model\Rule */
        $model = $this->ruleFactory->create();
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $data['store_ids'] = implode(',', $data['store_ids']);
        $data['customers_group_ids'] = implode(',', $data['customers_group_ids']);
        if (!empty($data['cancel_condition'])) {
            $data['cancel_condition'] = implode(",", $data['cancel_condition']);
        }
        $id = $data['abandoned_cart_rule_id'];
        if ($id) {
            $model->load($id);
        }

        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
            $conditionsModel = $this->_objectManager->create(\Magedelight\Abandonedcart\Model\RuleCondition::class);
            $conditionDataArray = $conditionsModel->dataConvter($data);
            $data['conditions_serialized'] =  $this->serializer->serialize($conditionDataArray);
            unset($data['rule']['conditions']);
            unset($data['conditions']);
        }
        
        $scheduleEmail = '';
        if (isset($data['scheduled_email'])) {
            $scheduleEmail = $data['scheduled_email'];
        }
        
        unset($data['rule']);
        unset($data['scheduled_email']);
        unset($data['form_key']);
        if (empty($data['abandoned_cart_rule_id'])) {
            unset($data['abandoned_cart_rule_id']);
        }
        try {
            $model->setData($data);
            $model->save();

            if (!empty($model->getAbandonedCartRuleId())) {
                $abandonedCartRuleId = $model->getAbandonedCartRuleId();
                $presentScheduleIdArr = [];
                if (!empty($scheduleEmail)) {
                    $emailScheduleModelObj = $this->emailSchedule->create();
                    $emailScheduleCollection = $emailScheduleModelObj->getCollection()
                        ->addFieldToFilter('abandoned_cart_rule_id', $abandonedCartRuleId);
                    $previouscollectionArray=[];
                    if ($emailScheduleCollection->count() > 0) {
                        foreach ($emailScheduleCollection->getData() as $re) {
                            array_push($previouscollectionArray, $re['schedule_id']);
                        }
                    }

                    $emailSchedulePostArray=[];
                    foreach ($scheduleEmail as $scheduleEmailData) {
                        array_push($emailSchedulePostArray, $scheduleEmailData['schedule_id']);
                    }

                    $this->deleteEmailScheduleIds(
                        $emailScheduleModelObj,
                        $previouscollectionArray,
                        $emailSchedulePostArray
                    );

                    $this->insertupDateEmailScheduleIds(
                        $scheduleEmail,
                        $abandonedCartRuleId
                    );

                } else {
                    $emailScheduleModelObj = $this->emailSchedule->create();
                    $emailScheduleCollection = $emailScheduleModelObj->getCollection()
                        ->addFieldToFilter('abandoned_cart_rule_id', $abandonedCartRuleId);
                    if ($emailScheduleCollection->count() > 0) {
                        foreach ($emailScheduleCollection->getData() as $re) {
                            $modelEmailSchedule=$emailScheduleModelObj->load($re['schedule_id']);
                            $modelEmailSchedule->delete();
                        }
                    }
                }
            }
            $this->messageManager->addSuccess(__('Abandoned Cart rule saved successfully!'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['id' => $model->getAbandonedCartRuleId(), '_current' => true]
                );
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(nl2br($e->getMessage()));
                $this->_getSession()->setData('abandonedcart_form_data', $this->getRequest()->getParams());
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
        } catch (\Exception $e) {
                $this->messageManager->addException($e, nl2br($e->getMessage()));
                $this->_getSession()->setData('abandonedcart_form_data', $this->getRequest()->getParams());
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
        }
        if ($this->getRequest()->getParam('back')) {
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        
        return $resultRedirect->setPath('*/*/');
    }

    public function deleteEmailScheduleIds($emailScheduleModelObj, $previouscollectionArray, $emailSchedulePostArray)
    {
        $deleteemailScheduleArray=array_diff($previouscollectionArray, $emailSchedulePostArray);
        if (!empty($deleteemailScheduleArray)) {
            foreach ($deleteemailScheduleArray as $value) {
                $modelEmailSchedule=$emailScheduleModelObj->load($value);
                $modelEmailSchedule->delete();
            }
        }
        return true;
    }

    public function insertupDateEmailScheduleIds($scheduleEmail, $abandonedCartRuleId)
    {
        foreach ($scheduleEmail as $scheduleEmailData) {
            $emailScheduleModelObj = $this->emailSchedule->create();
            $scheduleEmailData['abandoned_cart_rule_id'] = $abandonedCartRuleId;
            unset($scheduleEmailData['record_id']);
            if (empty($scheduleEmailData['schedule_id'])) {
                unset($scheduleEmailData['schedule_id']);
                $emailScheduleModelObj->addData($scheduleEmailData);
                $emailScheduleModelObj->save();
            } else {
                if (!isset($scheduleEmailData['delete'])) {
                    $emailScheduleModelObj->setData($scheduleEmailData);
                    $emailScheduleModelObj->save();
                }
            }
        }
        return true;
    }
}
