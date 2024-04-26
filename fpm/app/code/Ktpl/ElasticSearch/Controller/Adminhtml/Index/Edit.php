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

use Magento\Framework\Controller\ResultFactory;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\Index as ParentIndex;

/**
 * Class Edit
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Index
 */
class Edit extends ParentIndex
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $model = $this->initModel();

        if ($this->getRequest()->getParam(IndexInterface::ID)) {
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This search index no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Search Index "%1"', $model->getTitle()) : __('New Search Index')
        );

        return $resultPage;
    }
}
