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
use Magento\Backend\Model\View\Result\ForwardFactory;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml
 */
abstract class Index extends Action
{
    /**
     * @var IndexRepositoryInterface
     */
    protected $indexRepository;
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param IndexRepositoryInterface $scoreRuleRepository
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        IndexRepositoryInterface $scoreRuleRepository,
        ForwardFactory $resultForwardFactory
    )
    {
        $this->context = $context;
        $this->indexRepository = $scoreRuleRepository;
        $this->session = $context->getSession();
        $this->resultForwardFactory = $resultForwardFactory;

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
        $resultPage->getConfig()->getTitle()->prepend(__('Search Indexes'));

        return $resultPage;
    }

    /**
     * Initialize model
     *
     * @return IndexInterface
     */
    protected function initModel()
    {
        $model = $this->indexRepository->create();

        if ($this->getRequest()->getParam(IndexInterface::ID)) {
            $model = $this->indexRepository->get($this->getRequest()->getParam(IndexInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Ktpl_ElasticSearch::search_index');
    }
}
