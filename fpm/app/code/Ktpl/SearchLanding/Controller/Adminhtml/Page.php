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

namespace Ktpl\SearchLanding\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ktpl\SearchLanding\Api\Data\PageInterface;
use Ktpl\SearchLanding\Api\Repository\PageRepositoryInterface;

/**
 * Class Page
 *
 * @package Ktpl\SearchLanding\Controller\Adminhtml
 */
abstract class Page extends Action
{
    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var Context
     */
    protected $context;

    /**
     * Page constructor.
     *
     * @param PageRepositoryInterface $pageRepository
     * @param Context $context
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        Context $context
    )
    {
        $this->pageRepository = $pageRepository;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * Initialize model
     *
     * @return PageInterface
     */
    public function initModel()
    {
        $model = $this->pageRepository->create();

        if ($this->getRequest()->getParam(PageInterface::ID)) {
            $model = $this->pageRepository->get($this->getRequest()->getParam(PageInterface::ID));
        }

        return $model;
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
        $resultPage->getConfig()->getTitle()->prepend(__('Search'));
        $resultPage->getConfig()->getTitle()->prepend(__('Landing Pages'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Ktpl_SearchLanding::search_landing_page');
    }
}
