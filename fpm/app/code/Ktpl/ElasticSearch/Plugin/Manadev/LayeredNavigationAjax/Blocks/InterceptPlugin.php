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

namespace Ktpl\ElasticSearch\Plugin\Manadev\LayeredNavigationAjax\Blocks;

use Magento\Framework\View\Element\Template;

/**
 * Class InterceptPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin\Manadev\LayeredNavigationAjax\Blocks
 */
class InterceptPlugin extends Template
{
    /**
     * Load block
     *
     * @param $subject
     * @param $block
     * @return string
     */
    public function beforeRefreshStayingInSameCategory($subject, $block)
    {
        if ($block == 'search.result'){
            $block = 'searchindex.result';
        }
        return $block;
    }
}
