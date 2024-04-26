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

namespace Ktpl\ElasticSearch\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ktpl\ElasticSearch\Api\Data\StopwordInterface;
use Ktpl\ElasticSearch\Api\Repository\StopwordRepositoryInterface;
use Ktpl\ElasticSearch\Api\Service\StopwordServiceInterface;

/**
 * Class Stopword
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml
 */
abstract class Stopword extends Action
{
    /**
     * @var StopwordRepositoryInterface
     */
    protected $stopwordRepository;

    /**
     * @var StopwordServiceInterface
     */
    protected $stopwordService;

    /**
     * @var Context
     */
    protected $context;

    /**
     * Stopword constructor.
     *
     * @param StopwordRepositoryInterface $stopwordRepository
     * @param StopwordServiceInterface $stopwordService
     * @param Context $context
     */
    public function __construct(
        StopwordRepositoryInterface $stopwordRepository,
        StopwordServiceInterface $stopwordService,
        Context $context
    )
    {
        $this->stopwordRepository = $stopwordRepository;
        $this->stopwordService = $stopwordService;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * Initialize page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Ktpl_ElasticSearch::search');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Stopwords'));

        return $resultPage;
    }

    /**
     * Initialize model
     *
     * @return StopwordInterface
     */
    protected function initModel()
    {
        $model = $this->stopwordRepository->create();

        if ($this->getRequest()->getParam(StopwordInterface::ID)) {
            $model = $this->stopwordRepository->get($this->getRequest()->getParam(StopwordInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     *
     * return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Ktpl_ElasticSearch::search_stopword');
    }
}
