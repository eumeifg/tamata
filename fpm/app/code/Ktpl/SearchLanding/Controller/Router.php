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

namespace Ktpl\SearchLanding\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\SearchLanding\Api\Data\PageInterface;
use Ktpl\SearchLanding\Api\Repository\PageRepositoryInterface;
use Magento\Search\Model\QueryFactory;

/**
 * Class Router
 *
 * @package Ktpl\SearchLanding\Controller
 */
class Router implements RouterInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * Router constructor.
     *
     * @param PageRepositoryInterface $pageRepository
     * @param StoreManagerInterface $storeManager
     * @param ActionFactory $actionFactory
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        StoreManagerInterface $storeManager,
        ActionFactory $actionFactory
    )
    {
        $this->pageRepository = $pageRepository;
        $this->storeManager = $storeManager;
        $this->actionFactory = $actionFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     * @return bool|\Magento\Framework\App\ActionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(RequestInterface $request)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $identifier = trim($request->getPathInfo(), '/');

        $collection = $this->pageRepository->getCollection();
        $collection->addFieldToFilter(PageInterface::IS_ACTIVE, true)
            ->addFieldToFilter(PageInterface::URL_KEY, $identifier)
            ->addStoreFilter($this->storeManager->getStore()->getId());

        if ($collection->count()) {
            /** @var PageInterface $page */
            $page = $collection->getFirstItem();

            $params = [
                PageInterface::ID => $page->getId(),
                QueryFactory::QUERY_VAR_NAME => $page->getQueryText(),
            ];
            $request
                ->setModuleName('search_landing')
                ->setControllerName('page')
                ->setActionName('view')
                ->setParams($params)
                ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create(
                'Magento\Framework\App\Action\Forward',
                ['request' => $request]
            );
        }

        return false;
    }
}
