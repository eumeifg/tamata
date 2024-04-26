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

namespace Ktpl\ElasticSearch\Block\Index;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Service\IndexServiceInterface;

/**
 * Class Base
 *
 * @package Ktpl\ElasticSearch\Block\Index
 */
class Base extends Template
{
    /**
     * @var IndexServiceInterface
     */
    private $indexService;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Base constructor.
     *
     * @param IndexServiceInterface $indexService
     * @param ObjectManagerInterface $objectManager
     * @param Context $context
     */
    public function __construct(
        IndexServiceInterface $indexService,
        ObjectManagerInterface $objectManager,
        Context $context
    )
    {
        $this->indexService = $indexService;
        $this->objectManager = $objectManager;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $data
     * @param null $allowableTags
     * @param bool $allowHtmlEntities
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        $data = preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $data);
        $data = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $data);
        $data = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $data);
        $data = str_replace('>', '> ', $data); #adding space after tag <h1>..</h1><p>...</p>

        return parent::stripTags($data, $allowableTags, $allowHtmlEntities);
    }

    /**
     * Truncate text
     *
     * @param string $string
     * @return string
     */
    public function truncate($string)
    {
        if (strlen($string) > 512) {
            $string = strip_tags($string);
            $string = substr($string, 0, 512) . '...';
        }

        return $string;
    }

    /**
     * Get object manager instance
     *
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Return pager html for current collection.
     *
     * @return string
     */
    public function getPager()
    {
        $pager = $this->getChildBlock('pager');

        if (!$pager) {
            return '';
        }

        if (!$pager->getCollection()) {
            $pager->setCollection($this->getCollection());
        }

        return $pager->toHtml();
    }

    /**
     * Get collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->indexService->getSearchCollection($this->getIndex());
    }
}
