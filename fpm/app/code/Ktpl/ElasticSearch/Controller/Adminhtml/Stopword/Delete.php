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
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Ktpl\ElasticSearch\Api\Repository\StopwordRepositoryInterface;
use Ktpl\ElasticSearch\Api\Service\StopwordServiceInterface;

/**
 * Class Delete
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Stopword
 */
class Delete extends Stopword
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * Delete constructor.
     *
     * @param Filter $filter
     * @param StopwordRepositoryInterface $stopwordRepository
     * @param StopwordServiceInterface $stopwordService
     * @param Context $context
     */
    public function __construct(
        Filter $filter,
        StopwordRepositoryInterface $stopwordRepository,
        StopwordServiceInterface $stopwordService,
        Context $context
    )
    {
        $this->filter = $filter;

        parent::__construct($stopwordRepository, $stopwordService, $context);
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

        if ($this->getRequest()->getParam(StopwordInterface::ID)) {
            $ids = [$this->getRequest()->getParam(StopwordInterface::ID)];
        }

        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)
            || $this->getRequest()->getParam(Filter::EXCLUDED_PARAM)
        ) {
            $ids = $this->filter->getCollection($this->stopwordRepository->getCollection())->getAllIds();
        }
        if ($ids) {
            foreach ($ids as $id) {
                try {
                    $page = $this->stopwordRepository->get($id);
                    $this->stopwordRepository->delete($page);
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
