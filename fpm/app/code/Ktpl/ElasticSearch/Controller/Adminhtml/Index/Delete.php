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
 * Class Delete
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Index
 */
class Delete extends ParentIndex
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $index = $this->initModel();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($index->getId()) {
            try {
                $this->indexRepository->delete($index);

                $this->messageManager->addSuccessMessage(__('The search index has been deleted.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $index->getId()]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('This search index no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }
    }
}
