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

use Magento\Framework\Controller\ResultFactory;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule;

/**
 * Class Edit
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\ScoreRule
 */
class Edit extends ScoreRule
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $model = $this->initModel();
        $id = $this->getRequest()->getParam(ScoreRuleInterface::ID);

        if (!$model->getId() && $id) {
            $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(
                $model->getId() ? __('Rule "%1"', $model->getTitle()) : __('New Rule')
            );

        return $resultPage;
    }
}
