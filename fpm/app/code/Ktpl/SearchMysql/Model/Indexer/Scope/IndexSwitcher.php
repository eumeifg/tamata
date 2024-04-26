<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchMysql
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchMysql\Model\Indexer\Scope;

use Ktpl\ElasticSearch\Service\CompatibilityService;

if (CompatibilityService::is22() || CompatibilityService::is23()) {

    /**
     * Class ParentClass
     *
     * @package Ktpl\SearchMysql\Model\Indexer\Scope
     */
    class ParentClass extends \Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher
    {

    }
} else {

    /**
     * Class ParentClass
     *
     * @package Ktpl\SearchMysql\Model\Indexer\Scope
     */
    class ParentClass
    {

    }
}

/**
 * Class IndexSwitcher
 *
 * @package Ktpl\SearchMysql\Model\Indexer\Scope
 */
class IndexSwitcher extends ParentClass
{
}