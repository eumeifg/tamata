<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace MDC\Catalog\Helper\Listing;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Escaper;

class Data extends AbstractHelper
{

    /**
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $_queryFactory;
    protected $scopeConfig;

    /**
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $sellerUrl;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_configurableProduct;

     /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $_priceHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Escaper $escaper
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Escaper $escaper,
        QueryFactory $queryFactory,
        \Magedelight\Backend\Model\UrlInterface $sellerUrl,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magedelight\Catalog\Helper\Pricing\Data $priceHelper
    ) {
        $this->_escaper = $escaper;
        $this->_queryFactory = $queryFactory;
        $this->request = $context->getRequest();
        $this->scopeConfig = $context->getScopeConfig();
        $this->sellerUrl = $sellerUrl;
        $this->_productRepository = $productRepository;
        $this->_configurableProduct = $configurableProduct;
        $this->_priceHelper = $priceHelper;
        parent::__construct($context);
    }

    /**
     *
     * @return type
     */
    public function getQueryParamName()
    {
        return QueryFactory::QUERY_VAR_NAME;
    }

    /**
     *
     * @param type $query
     * @return type
     */
    public function getResultUrl($query = null)
    {
        return $this->sellerUrl->getUrl(
            'rbcatalog/listing/index',
            ['_query' => [QueryFactory::QUERY_VAR_NAME => $query], '_secure' => $this->_request->isSecure()]
        );
    }

    /**
     *
     * @return string
     */
    public function searchText()
    {
        $search = $this->request->getParam('q');
        if ($search) {
            return $search;
        } else {
            return '';
        }
    }

    public function getLiveMassUnlist()
    {
        return $this->sellerUrl->getUrl('rbcatalog/listing/massunlist');
    }

    public function getLiveMasslist()
    {
        return $this->sellerUrl->getUrl('rbcatalog/listing/masslist');
    }

    /**
     * @return array
     */
    public function getAttributesList()
    {
        return
            [
                'color',
                'shoe_size',
                'screen_size',
                'age',
                'perfume_size',
                'clothing_size',
                'men_shoe',
                'women_shoe',
                'kids_shoe',
                'brand',
                'resolution',
                'screen_size',
                'storage_capacity',
                'interface',
                'men_clothing_size',
                'women_clothing_size',
                'baby_clothing_size',
                'underwear_size',
                'jewellery_sizes',
                'jewellery_size'
            ];
    }

    public function loadProductById($productId)
    {
        $parentIds = $this->_configurableProduct->getParentIdsByChild($productId);
        $parentId = array_shift($parentIds);
        if($parentId){
            $product = $this->_productRepository->getById($parentId);
            return $product->getUrlKey();
        } else {
            $product = $this->_productRepository->getById($productId);
            return $product->getUrlKey();
        }
    }

    public function getMinVendorOfferedPrice($prices){
        $minPrice = min($prices);
        $finalMinPrice = $this->_priceHelper->currency($minPrice, true, false);

        return $finalMinPrice;

    }
}
