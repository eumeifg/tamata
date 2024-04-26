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

namespace Ktpl\ElasticSearch\Block\Options;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class GoogleSitelinks
 *
 * @package Ktpl\ElasticSearch\Block\Options
 */
class GoogleSitelinks extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    )
    {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Is enabled?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isGoogleSitelinksEnabled();
    }

    /**
     * Store base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->getUrl();
    }

    /**
     * Search target url
     *
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->getUrl(
            'catalogsearch/result/index',
            [
                '_query' => [
                    'q' => '{search_term_string}'
                ]
            ]
        );
    }
}
