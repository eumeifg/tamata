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

namespace Ktpl\ElasticSearch\Controller\Adminhtml\Index;

use Ktpl\ElasticSearch\Controller\Adminhtml\Index as ParentIndex;

/**
 * Class Reindex
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Index
 */
class Reindex extends ParentIndex
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $index = $this->initModel();

        if (!$index->getId()) {
            $this->messageManager->addErrorMessage(__('This search index no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->indexRepository->getInstance($index)->reindexAll();
            $this->messageManager->addSuccessMessage(__('%1 reindex successfully completed.', $index->getTitle()));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
