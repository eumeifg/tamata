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

if (!class_exists('Magento\CatalogSearch\Model\Indexer\Scope\State')) {

    /**
     * Class StateMediatorParent
     *
     * @package Ktpl\SearchElastic\Model\Indexer\Scope
     */
    class StateMediatorParent
    {
        /**
         * Get state mediator parent
         *
         * @return bool
         */
        public function get()
        {
            return true;
        }
    }
}