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
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Ktpl\ElasticSearch\Api\Repository\SynonymRepositoryInterface;
use Ktpl\ElasticSearch\Api\Service\SynonymServiceInterface;

/**
 * Class Delete
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Synonym
 */
class Delete extends Synonym
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * Delete constructor.
     *
     * @param Filter $filter
     * @param SynonymRepositoryInterface $synonymRepository
     * @param SynonymServiceInterface $synonymService
     * @param Context $context
     */
    public function __construct(
        Filter $filter,
        SynonymRepositoryInterface $synonymRepository,
        SynonymServiceInterface $synonymService,
        Context $context
    )
    {
        $this->filter = $filter;
        parent::__construct($synonymRepository, $synonymService, $context);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {

        $ids = [];

        if ($this->getRequest()->getParam(SynonymInterface::ID)) {
            $ids = [$this->getRequest()->getParam(SynonymInterface::ID)];
        }

        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)
            || $this->getRequest()->getParam(Filter::EXCLUDED_PARAM)
        ) {
            $ids = $this->filter->getCollection($this->synonymRepository->getCollection())->getAllIds();
        }

        if ($ids) {
            foreach ($ids as $id) {
                try {
                    $page = $this->synonymRepository->get($id);
                    $this->synonymRepository->delete($page);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }

            $this->messageManager->addSuccessMessage(
                __('%1 item(s) was removed', count($ids))
            );
        } else {
            $this->messageManager->addErrorMessage(__('Please select item(s)'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
