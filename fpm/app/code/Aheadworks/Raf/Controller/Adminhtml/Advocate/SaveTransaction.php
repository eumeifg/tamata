<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Controller\Adminhtml\Advocate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action as BackendAction;
use Aheadworks\Raf\Api\TransactionManagementInterface;
use Aheadworks\Raf\Model\Source\Transaction\Action as ActionSource;
use Aheadworks\Raf\Model\Source\Rule\AdvocateOffType;
use Aheadworks\Raf\Api\RuleManagementInterface;

/**
 * Class SaveTransaction
 *
 * @package Aheadworks\Raf\Controller\Adminhtml\Advocate
 */
class SaveTransaction extends BackendAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Raf::advocates';

    /**
     * @var Context
     */
    private $context;

    /**
     * @var TransactionManagementInterface
     */
    private $transactionManagement;

    /**
     * @var RuleManagementInterface
     */
    private $ruleManagement;

    /**
     * @param Context $context
     * @param TransactionManagementInterface $transactionManagement
     * @param RuleManagementInterface $ruleManagement
     */
    public function __construct(
        Context $context,
        TransactionManagementInterface $transactionManagement,
        RuleManagementInterface $ruleManagement
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->transactionManagement = $transactionManagement;
        $this->ruleManagement = $ruleManagement;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $this->saveTransaction($data);
                $this->messageManager->addSuccessMessage(__('You created transaction.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/view', ['id' => $data['id']]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while creating transaction.')
                );
            }
            $id = isset($data['id']) ? $data['id'] : false;
            if ($id) {
                return $resultRedirect->setPath('*/*/view', ['id' => $id, '_current' => true]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Save transaction
     *
     * @param array $data
     * @throws LocalizedException
     */
    private function saveTransaction($data)
    {
        $activeRule = $this->ruleManagement->getActiveRule($data['website_id']);
        if (!$activeRule) {
            throw new LocalizedException(__('No active rule found for this advocate'));
        }

        if (isset($data['transaction_amount']) && !empty($data['transaction_amount'])) {
            $currentAdminUserId = $this->context->getAuth()->getUser()->getId();
            $this->transactionManagement->createTransaction(
                $data['customer_id'],
                $data['website_id'],
                ActionSource::ADJUSTED_BY_ADMIN,
                $data['transaction_amount'],
                $activeRule->getAdvocateOffType(),
                $currentAdminUserId,
                $data['transaction_admin_comment'],
                null
            );
        }
    }
}
