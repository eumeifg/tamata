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

use Ktpl\ElasticSearch\Api\Data\SynonymInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\Synonym;

/**
 * Class Save
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Synonym
 */
class Save extends Synonym
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This synonym no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setTerm($this->getRequest()->getParam(SynonymInterface::TERM))
                ->setSynonyms($this->getRequest()->getParam(SynonymInterface::SYNONYMS))
                ->setStoreId($this->getRequest()->getParam(SynonymInterface::STORE_ID));

            try {
                $this->synonymRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the synonym.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}
