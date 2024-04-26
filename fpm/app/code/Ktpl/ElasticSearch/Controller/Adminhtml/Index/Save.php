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

use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\Index as ParentIndex;

/**
 * Class Save
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Index
 */
class Save extends ParentIndex
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getParams()) {
            $index = $this->initModel();

            if (!$index->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This search index no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $index->setTitle($this->getRequest()->getParam(IndexInterface::TITLE))
                ->setIdentifier($this->getRequest()->getParam(IndexInterface::IDENTIFIER))
                ->setIsActive($this->getRequest()->getParam(IndexInterface::IS_ACTIVE))
                ->setPosition($this->getRequest()->getParam(IndexInterface::POSITION))
                ->setAttributes($this->getRequest()->getParam('attributes'))
                ->setProperties($this->getRequest()->getParam('properties'));

            try {
                $this->indexRepository->save($index);

                $this->messageManager->addSuccessMessage(__('You saved the search index.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [IndexInterface::ID => $index->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [IndexInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}
