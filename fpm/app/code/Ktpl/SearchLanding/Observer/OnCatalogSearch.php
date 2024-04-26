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

namespace Ktpl\SearchLanding\Observer;

use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Ktpl\SearchLanding\Api\Repository\PageRepositoryInterface;
use Magento\Search\Model\QueryFactory;
use Ktpl\SearchLanding\Api\Data\PageInterface;
use Magento\Framework\UrlFactory;

/**
 * Class OnCatalogSearch
 *
 * @package Ktpl\SearchLanding\Observer
 */
class OnCatalogSearch implements ObserverInterface
{
    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var HttpResponse
     */
    private $response;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var QueryFactory
     */
    private $query;

    /**
     * OnCatalogSearch constructor.
     *
     * @param HttpResponse $response
     * @param PageRepositoryInterface $pageRepository
     * @param QueryFactory $queryFactory
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        HttpResponse $response,
        PageRepositoryInterface $pageRepository,
        QueryFactory $queryFactory,
        UrlFactory $urlFactory
    )
    {
        $this->response = $response;
        $this->pageRepository = $pageRepository;
        $this->query = $queryFactory->get();
        $this->urlFactory = $urlFactory;
    }

    /**
     * Observer for controller_action_postdispatch_catalogsearch_result_index
     *
     * @param EventObserver $observer
     * @return bool|void
     */
    public function execute(EventObserver $observer)
    {
        $queryText = strip_tags($this->query->getQueryText());

        $collection = $this->pageRepository->getCollection();
        $collection->addFieldToFilter(PageInterface::IS_ACTIVE, true)
            ->addFieldToFilter('query_text', $queryText);

        if ($collection->count()) {
            $page = $collection->getFirstItem();
            $url = $this->urlFactory->create()->getUrl($page->getUrlKey());
            $this->response->setRedirect($url);
            return true;
        }

        return false;
    }
}
