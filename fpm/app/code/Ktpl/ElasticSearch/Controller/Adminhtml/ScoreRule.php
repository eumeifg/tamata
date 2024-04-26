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
use Magento\Framework\Registry;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Ktpl\ElasticSearch\Api\Repository\ScoreRuleRepositoryInterface;

/**
 * Class ScoreRule
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml
 */
abstract class ScoreRule extends Action
{
    /**
     * @var ScoreRuleRepositoryInterface
     */
    protected $scoreRuleRepository;
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * ScoreRule constructor.
     *
     * @param ScoreRuleRepositoryInterface $scoreRuleRepository
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param Context $context
     */
    public function __construct(
        ScoreRuleRepositoryInterface $scoreRuleRepository,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        Context $context
    )
    {
        $this->scoreRuleRepository = $scoreRuleRepository;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->context = $context;
        $this->session = $context->getSession();

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
        $resultPage->getConfig()->getTitle()->prepend(__('Score Rules'));

        return $resultPage;
    }

    /**
     * Initialize model
     *
     * @return ScoreRuleInterface
     */
    protected function initModel()
    {
        $model = $this->scoreRuleRepository->create();

        if ($this->getRequest()->getParam(ScoreRuleInterface::ID)) {
            $model = $this->scoreRuleRepository->get($this->getRequest()->getParam(ScoreRuleInterface::ID));
        }

        $this->registry->register(ScoreRuleInterface::class, $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     *
     * return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Ktpl_ElasticSearch::search_score_rule');
    }
}
