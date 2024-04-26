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

namespace Ktpl\ElasticSearch\Plugin;

use Ktpl\ElasticSearch\Model\Config;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlFactory;

/**
 * Class NoRoutePlugin
 *
 * @package Ktpl\ElasticSearch\Plugin
 */
class NoRoutePlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @var array
     */
    private $mediaTypes = [
        'jpg',
        'jpeg',
        'gif',
        'png',
        'css',
        'js',
        'ttf',
        'eot',
        'svg',
        'woff',
        'woff2',
        'ico',
        'map',
        'txt',
        'xml',
    ];

    /**
     * NoRoutePlugin constructor.
     *
     * @param Config $config
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Config $config,
        RequestInterface $request,
        ManagerInterface $messageManager,
        UrlFactory $urlFactory
    )
    {
        $this->config = $config;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->urlFactory = $urlFactory;
    }

    /**
     * Get search query url
     *
     * @param HttpInterface $response
     * @return void
     */
    public function beforeSendResponse(HttpInterface $response)
    {
        /** @var \Magento\Framework\App\Response\Http $response */
        if ($response->getHttpResponseCode() == 404 && $this->config->isNorouteToSearchEnabled()) {
            $extension = pathinfo($this->request->getRequestString(), PATHINFO_EXTENSION);

            if (!$this->request->isGet() || in_array($extension, $this->mediaTypes)) {
                return;
            }

            $searchQuery = $this->getSearchQuery($this->request);

            if (!$searchQuery) {
                return;
            }

            $message = __('The page you requested was not found, but we have searched for relevant content.');

            $this->messageManager->addNoticeMessage($message);

            $url = $this->urlFactory->create()
                ->addQueryParams(['q' => $searchQuery])
                ->getUrl('catalogsearch/result');

            $response
                ->setRedirect($url)
                ->setStatusCode(301);
        }
    }

    /**
     * Get search query
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return string
     */
    protected function getSearchQuery($request)
    {
        $ignored = [
            'html',
            'php',
            'catalog',
            'catalogsearch',
            'search',
            'rma',
            'account',
            'customer',
            'helpdesk',
            'wishlist',
            'newsletter',
            'contact',
            'sendfriend',
            'product_compare',
            'review',
            'product',
            'checkout',
            'paypal',
            'sales',
            'downloadable',
            'rewards',
            'credit',
        ];

        $maxQueryLength = 128;
        $expr = '/(\W|' . implode('|', $ignored) . ')+/';
        $requestString = preg_replace($expr, ' ', $request->getRequestString());

        $terms = preg_split('/[ \- \\/_]/', $requestString);
        $terms = array_filter(array_unique($terms));

        return trim(substr(implode(' ', $terms), 0, $maxQueryLength));
    }
}
