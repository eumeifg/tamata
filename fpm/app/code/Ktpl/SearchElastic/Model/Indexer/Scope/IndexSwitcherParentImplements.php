<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchElastic
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchElastic\Model\Indexer\Scope;

use Ktpl\ElasticSearch\Service\CompatibilityService;

if (CompatibilityService::is22() || CompatibilityService::is23()) {

    /**
     * Class IndexSwitcherParentMediator
     *
     * @package Ktpl\SearchElastic\Model\Indexer\Scope
     */
    class IndexSwitcherParentMediator implements \Magento\CatalogSearch\Model\Indexer\IndexSwitcherInterface
    {
        /**
         * @var IndexSwitcher
         */
        private $switcher;

        /**
         * IndexSwitcherParentMediator constructor.
         *
         * @param IndexSwitcher $switcher
         */
        public function __construct(IndexSwitcher $switcher)
        {
            $this->switcher = $switcher;
        }

        /**
         * Switch index
         *
         * @param array $dimensions
         */
        public function switchIndex(array $dimensions)
        {
            $this->switcher->switchIndex($dimensions);
        }
    }
}