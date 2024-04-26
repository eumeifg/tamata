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

use Magento\Framework\App\ObjectManager;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule;
use Ktpl\ElasticSearch\Model\ScoreRule\Indexer\ScoreRuleIndexer;

/**
 * Class Apply
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule
 */
class Apply extends ScoreRule
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $model = $this->initModel();

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This rule no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }

        try {
            $objectManager = ObjectManager::getInstance();

            $scoreRuleIndexer = $objectManager->create(ScoreRuleIndexer::class);
            $scoreRuleIndexer->execute($model, []);

            $this->messageManager->addSuccessMessage(__('You applied the rule.'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', [ScoreRuleInterface::ID => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/');
        }
    }
}
