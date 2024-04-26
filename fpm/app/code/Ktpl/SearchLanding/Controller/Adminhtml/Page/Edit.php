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

use Magento\Framework\Controller\ResultFactory;
use Ktpl\SearchLanding\Api\Data\PageInterface;
use Ktpl\SearchLanding\Controller\Adminhtml\Page;

/**
 * Class Edit
 *
 * @package Ktpl\SearchLanding\Controller\Adminhtml\Page
 */
class Edit extends Page
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Page\Interceptor|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $id = $this->getRequest()->getParam(PageInterface::ID);
        $model = $this->initModel();

        if ($id && !$model) {
            $this->messageManager->addErrorMessage(__('This page no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(
            $model->getTitle() ? $model->getTitle() : __('New Page')
        );

        return $resultPage;
    }
}
