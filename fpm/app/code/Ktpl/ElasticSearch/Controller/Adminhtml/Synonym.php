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
use Ktpl\ElasticSearch\Api\Data\SynonymInterface;
use Ktpl\ElasticSearch\Api\Repository\SynonymRepositoryInterface;
use Ktpl\ElasticSearch\Api\Service\SynonymServiceInterface;

/**
 * Class Synonym
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml
 */
abstract class Synonym extends Action
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var SynonymRepositoryInterface
     */
    protected $synonymRepository;

    /**
     * @var SynonymServiceInterface
     */
    protected $synonymService;

    /**
     * Synonym constructor.
     *
     * @param SynonymRepositoryInterface $synonymRepository
     * @param SynonymServiceInterface $synonymService
     * @param Context $context
     */
    public function __construct(
        SynonymRepositoryInterface $synonymRepository,
        SynonymServiceInterface $synonymService,
        Context $context
    )
    {
        $this->synonymRepository = $synonymRepository;
        $this->synonymService = $synonymService;
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
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Synonyms'));

        return $resultPage;
    }

    /**
     * Initialize model
     *
     * @return SynonymInterface
     */
    protected function initModel()
    {
        $model = $this->synonymRepository->create();

        if ($this->getRequest()->getParam(SynonymInterface::ID)) {
            $model = $this->synonymRepository->get($this->getRequest()->getParam(SynonymInterface::ID));
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
        return $this->context->getAuthorization()->isAllowed('Ktpl_ElasticSearch::search_synonym');
    }
}
