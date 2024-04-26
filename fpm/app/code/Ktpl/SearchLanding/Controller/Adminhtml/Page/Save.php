<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchLanding
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchLanding\Controller\Adminhtml\Page;

use Ktpl\SearchLanding\Api\Data\PageInterface;
use Ktpl\SearchLanding\Controller\Adminhtml\Page;

/**
 * Class Save
 *
 * @package Ktpl\SearchLanding\Controller\Adminhtml\Page
 */
class Save extends Page
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(PageInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->filter($this->getRequest()->getParams());

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This page no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setQueryText($data[PageInterface::QUERY_TEXT])
                ->setUrlKey($data[PageInterface::URL_KEY])
                ->setTitle($data[PageInterface::TITLE])
                ->setMetaKeywords($data[PageInterface::META_KEYWORDS])
                ->setMetaDescription($data[PageInterface::META_DESCRIPTION])
                ->setLayoutUpdate($data[PageInterface::LAYOUT_UPDATE])
                ->setStoreIds($data[PageInterface::STORE_IDS])
                ->setIsActive($data[PageInterface::IS_ACTIVE]);

            try {
                $this->pageRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the page.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [PageInterface::ID => $model->getId()]);
                }

                return $this->context->getResultRedirectFactory()->create()->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath(
                    '*/*/edit',
                    [PageInterface::ID => $this->getRequest()->getParam(PageInterface::ID)]
                );
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    /**
     * Filter raw data
     *
     * @param array $rawData
     * @return array
     */
    private function filter(array $rawData)
    {
        return $rawData;
    }
}
