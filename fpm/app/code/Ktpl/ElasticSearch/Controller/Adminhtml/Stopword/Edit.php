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

use Magento\Framework\Controller\ResultFactory;
use Ktpl\ElasticSearch\Api\Data\StopwordInterface;
use Ktpl\ElasticSearch\Controller\Adminhtml\Stopword;

/**
 * Class Edit
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Stopword
 */
class Edit extends Stopword
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $model = $this->initModel();
        $id = $this->getRequest()->getParam(StopwordInterface::ID);

        if (!$model->getId() && $id) {
            $this->messageManager->addErrorMessage(__('This stopword no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(
                $model->getId() ? __('Stopword "%1"', $model->getTerm()) : __('New Stopword')
            );

        return $resultPage;
    }
}
