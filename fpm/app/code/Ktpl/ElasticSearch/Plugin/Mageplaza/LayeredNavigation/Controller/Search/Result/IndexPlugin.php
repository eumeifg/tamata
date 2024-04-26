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

namespace Ktpl\ElasticSearch\Plugin\Mageplaza\LayeredNavigation\Controller\Search\Result;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class IndexPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin\Mageplaza\LayeredNavigation\Controller\Search\Result
 */
class IndexPlugin extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    private $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @type \Magento\Framework\Json\Helper\Data
     */
    private $_jsonHelper;

    /**
     * @type \Magento\CatalogSearch\Helper\Data
     */
    private $_helper;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     * @var Resolver
     */
    private $layerResolver;

    /**
     * IndexPlugin constructor.
     *
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param \Magento\CatalogSearch\Helper\Data $helper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        \Magento\CatalogSearch\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->_jsonHelper = $jsonHelper;
        $this->_helper = $helper;
    }

    /**
     * Load products after layered navigation navigated
     *
     * @param $subject
     * @param \Closure $proceed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        $query = $this->_queryFactory->get();

        $query->setStoreId($this->_storeManager->getStore()->getId());

        if ($query->getQueryText() != '') {
            if ($this->_helper->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
                if ($query->getRedirect()) {
                    $this->getResponse()->setRedirect($query->getRedirect());

                    return;
                }
            }

            $this->_helper->checkNotes();

            if ($this->getRequest()->isAjax()) {
                $this->_view->loadLayout();
                $navigation = $this->_view->getLayout()->getBlock('catalogsearch.leftnav');
                $products = $this->_view->getLayout()->getBlock('searchindex.result');
                $result = ['products' => $products->toHtml(), 'navigation' => $navigation->toHtml()];
                $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
            } else {
                $this->_view->loadLayout();
                $this->_view->renderLayout();
            }
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
    }
}
