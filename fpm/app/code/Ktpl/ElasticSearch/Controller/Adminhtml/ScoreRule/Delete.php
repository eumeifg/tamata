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

use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule;

/**
 * Class Delete
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule
 */
class Delete extends ScoreRule
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(ScoreRuleInterface::ID);

        if ($id) {
            try {
                $rule = $this->scoreRuleRepository->get($id);
                $this->scoreRuleRepository->delete($rule);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->messageManager->addSuccessMessage(
                __('Rule was removed')
            );
        } else {
            $this->messageManager->addErrorMessage(__('Please select rule'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
