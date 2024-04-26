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

namespace Ktpl\SearchLanding\Controller\Page;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Ktpl\SearchLanding\Api\Data\PageInterface;
use Ktpl\SearchLanding\Api\Repository\PageRepositoryInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;

/**
 * Class View
 *
 * @package Ktpl\SearchLanding\Controller\Page
 */
class View extends \Magento\CatalogSearch\Controller\Result\Index
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var PageRepositoryInterface
     */
    private $resultPageFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * View constructor.
     *
     * @param PageRepositoryInterface $pageRepository
     * @param Registry $registry
     * @param PageFactory $pageFactory
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param Context $context
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        Registry $registry,
        PageFactory $pageFactory,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        Context $context
    )
    {
        $this->registry = $registry;
        $this->pageRepository = $pageRepository;
        $this->resultPageFactory = $pageFactory;

        parent::__construct($context, $catalogSession, $storeManager, $queryFactory, $layerResolver);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(PageInterface::ID);

        $page = $this->pageRepository->get($id);

        $this->registry->register('search_landing_page', $page);

        $resultPage = $this->resultPageFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->initLayout();
        $resultPage->addHandle('catalogsearch_result_index');
        $resultPage->addHandle('search_landing_page');

        if ($page->getLayoutUpdate()) {
            $resultPage->addUpdate($page->getLayoutUpdate());
        }

        parent::execute();

        $resultPage->getConfig()->getTitle()->set('fafa');
    }
}