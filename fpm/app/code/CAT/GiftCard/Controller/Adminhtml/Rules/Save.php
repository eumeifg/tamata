<?php

namespace CAT\GiftCard\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use CAT\GiftCard\Model\ResourceModel\Coupon\CollectionFactory;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * @var Date
     */
    protected $_dateFilter;

    /**
     * @var CollectionFactory
     */
    protected $couponCollection;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Action\Context $context
     * @param Date $dateFilter
     * @param CollectionFactory $couponCollection
     * @param TimezoneInterface|null $timezone
     * @param DataPersistorInterface|null $dataPersistor
     */
    public function __construct(
        Action\Context         $context,
        Date                   $dateFilter,
        CollectionFactory      $couponCollection,
        TimezoneInterface      $timezone = null,
        DataPersistorInterface $dataPersistor = null
    )
    {
        $this->_dateFilter = $dateFilter;
        $this->couponCollection = $couponCollection;
        parent::__construct($context);
        $this->timezone = $timezone ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
                TimezoneInterface::class
            );
        $this->dataPersistor = $dataPersistor ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
                DataPersistorInterface::class
            );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $data['use_auto_generation'] = 1;
        if ($data) {
            try {
                /** @var $model \CAT\GiftCard\Model\GiftCardRule */
                $model = $this->_objectManager->create(\CAT\GiftCard\Model\GiftCardRule::class);

                if (empty($data['from_date'])) {
                    $data['from_date'] = $this->timezone->formatDate();
                }
                $filterValues = ['from_date' => $this->_dateFilter];
                if ($this->getRequest()->getParam('to_date')) {
                    $filterValues['to_date'] = $this->_dateFilter;
                }
                $inputFilter = new \Zend_Filter_Input(
                    $filterValues,
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                if (!$this->checkRuleExists($model)) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The wrong rule is specified.'));
                }
                $isUsageChanged = false;
                if ($data['uses_per_customer'] != $model->getUsesPerCustomer()) {
                    $isUsageChanged = true;
                }

                $session = $this->_objectManager->get(\Magento\Backend\Model\Session::class);

                $validateResult = $this->validateData(new \Magento\Framework\DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $session->setPageData($data);
                    $this->dataPersistor->set('giftcard_rule', $data);
                    $this->_redirect('giftcardrule/rules/edit', ['id' => $model->getId()]);
                    return;
                }
                unset($data['rule']);

                $useAutoGeneration = (int)(
                    !empty($data['use_auto_generation']) && $data['use_auto_generation'] !== 'false'
                );
                $model->setUseAutoGeneration($useAutoGeneration);
                $model->setData($data);
                $session->setPageData($model->getData());

                $model->save();
                if (($isUsageChanged)) {
                    $this->updateCouponsByRuleId($this->getRequest()->getParam('rule_id'), $data);
                }
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('giftcardrule/rules/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('giftcardrule/rules/index');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('giftcardrule/rules/edit', ['id' => $id]);
                } else {
                    $this->_redirect('giftcardrule/rules/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData($data);
                $this->_redirect('giftcardrule/rules/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                return;
            }
        }
        $this->_redirect('giftcardrule/rules/');
    }

    private function checkRuleExists(\CAT\GiftCard\Model\GiftCardRule $model): bool
    {
        $id = $this->getRequest()->getParam('rule_id');
        if ($id) {
            $model->load($id);
            if ($model->getId() != $id) {
                return false;
            }
        }
        return true;
    }

    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result = [];
        $fromDate = $toDate = null;

        if ($dataObject->hasFromDate() && $dataObject->hasToDate()) {
            $fromDate = $dataObject->getFromDate();
            $toDate = $dataObject->getToDate();
        }

        if ($fromDate && $toDate) {
            $fromDate = new \DateTime($fromDate);
            $toDate = new \DateTime($toDate);

            if ($fromDate > $toDate) {
                $result[] = __('End Date must follow Start Date.');
            }
        }

        /*if ($dataObject->hasWebsiteIds()) {
            $websiteIds = $dataObject->getWebsiteIds();
            if (empty($websiteIds)) {
                $result[] = __('Please specify a website.');
            }
        }
        if ($dataObject->hasCustomerGroupIds()) {
            $customerGroupIds = $dataObject->getCustomerGroupIds();
            if (empty($customerGroupIds)) {
                $result[] = __('Please specify Customer Groups.');
            }
        }*/

        return !empty($result) ? $result : true;
    }

    /**
     * @param $ruleId
     * @param $usesPerCustomer
     * @return void
     */
    public function updateCouponsByRuleId($ruleId, $data)
    {
        $couponCollection = $this->couponCollection->create()->addRuleIdsToFilter([$ruleId]);
        if ($couponCollection->getSize()) {
            foreach ($couponCollection as $coupon) {
                if (!empty($data['uses_per_customer'])) {
                    $coupon->setUsagePerCustomer($data['uses_per_customer']);
                }
                $coupon->save();
            }
        }
    }
}
