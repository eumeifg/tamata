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

namespace Ktpl\ElasticSearch\Controller\Adminhtml\Stopword;

use Ktpl\ElasticSearch\Api\Data\StopwordInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\Stopword;

/**
 * Class Save
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Stopword
 */
class Save extends Stopword
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam(StopwordInterface::ID);

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This stopword no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setTerm($this->getRequest()->getParam(StopwordInterface::TERM))
                ->setStoreId($this->getRequest()->getParam(StopwordInterface::STORE_ID));

            try {
                $this->stopwordRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the stopword.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [StopwordInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}
