<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule;

use Ktpl\ElasticSearch\Service\CompatibilityService;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule;

/**
 * Class Save
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule
 */
class Save extends ScoreRule
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam(ScoreRuleInterface::ID);
        $model = $this->initModel();
        $data = $this->getRequest()->getPostValue();
        $data = $this->filter($data, $model);

        if ($data) {

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setTitle($data[ScoreRuleInterface::TITLE])
                ->setIsActive($data[ScoreRuleInterface::IS_ACTIVE])
                ->setActiveFrom($data[ScoreRuleInterface::ACTIVE_FROM])
                ->setActiveTo($data[ScoreRuleInterface::ACTIVE_TO])
                ->setStoreIds($data[ScoreRuleInterface::STORE_IDS])
                ->setScoreFactor($data[ScoreRuleInterface::SCORE_FACTOR])
                ->setConditionsSerialized($data[ScoreRuleInterface::CONDITIONS_SERIALIZED])
                ->setPostConditionsSerialized($data[ScoreRuleInterface::POST_CONDITIONS_SERIALIZED]);

            try {
                $this->scoreRuleRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [ScoreRuleInterface::ID => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', [ScoreRuleInterface::ID => $model->getId()]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    /**
     * Filter
     *
     * @param array $data
     * @param ScoreRuleInterface $scoreRule
     * @return array
     */
    private function filter(array $data, ScoreRuleInterface $scoreRule)
    {
        $scoreFactorType = $data['score_factor_type'];
        $scoreFactorUnit = $data['score_factor_unit'];
        $scoreFactorRelatively = $data['score_factor_relatively'];

        if ($scoreFactorType == '+') {
            if ($scoreFactorUnit == '*') {
                $p = '*';
            } else {
                $p = '+';
            }
        } else {
            if ($scoreFactorUnit == '*') {
                $p = '/';
            } else {
                $p = '-';
            }
        }

        $data[ScoreRuleInterface::SCORE_FACTOR] = implode('|', [
            $p, $data['score_factor'], $scoreFactorRelatively]);

        $rule = $scoreRule->getRule();
        if (isset($data['rule']) && isset($data['rule']['conditions'])) {
            $rule->loadPost(['conditions' => $data['rule']['conditions']]);

            $conditions = $rule->getConditions()->asArray();

            if (CompatibilityService::is21()) {
                $conditions = serialize($conditions);
            } else {
                $conditions = \Zend_Json::encode($conditions);
            }

            $data[ScoreRuleInterface::CONDITIONS_SERIALIZED] = $conditions;
        }

        if (isset($data['rule']) && isset($data['rule']['post_conditions'])) {
            $rule->loadPost(['actions' => $data['rule']['post_conditions']]);

            $postConditions = $rule->getActions()->asArray();

            if (CompatibilityService::is21()) {
                $postConditions = serialize($postConditions);
            } else {
                $postConditions = \Zend_Json::encode($postConditions);
            }

            $data[ScoreRuleInterface::POST_CONDITIONS_SERIALIZED] = $postConditions;
        }

        return $data;
    }
}
