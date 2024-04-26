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

namespace Ktpl\ElasticSearch\Controller\Adminhtml\Synonym;

use Magento\Framework\Controller\ResultFactory;
use Ktpl\ElasticSearch\Api\Data\SynonymInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\Synonym;

/**
 * Class Edit
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Synonym
 */
class Edit extends Synonym
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $model = $this->initModel();
        $id = $this->getRequest()->getParam(SynonymInterface::ID);

        if (!$model->getId() && $id) {
            $this->messageManager->addErrorMessage(__('This synonym no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(
                $model->getId() ? __('Synonyms for "%1"', $model->getTerm()) : __('New Synonym')
            );

        return $resultPage;
    }
}
