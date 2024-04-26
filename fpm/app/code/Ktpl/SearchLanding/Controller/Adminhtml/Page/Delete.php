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
 * Class Delete
 *
 * @package Ktpl\SearchLanding\Controller\Adminhtml\Page
 */
class Delete extends Page
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

        $model = $this->initModel();

        if (!$model->getId() && $id) {
            $this->messageManager->addErrorMessage(__('This page no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->pageRepository->delete($model);

            $this->messageManager->addSuccessMessage(__('You deleted the page.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->context->getResultRedirectFactory()->create()->setPath('*/*/');
    }
}
