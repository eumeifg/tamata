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

if (class_exists('Magento\CatalogSearch\Model\Indexer\Scope\State')) {

    /**
     * Class StateMediatorParent
     *
     * @package Ktpl\SearchElastic\Model\Indexer\Scope
     */
    class StateMediatorParent
    {
        /**
         * @var \Magento\CatalogSearch\Model\Indexer\Scope\State
         */
        private $state;

        /**
         * StateMediatorParent constructor.
         *
         * @param \Magento\CatalogSearch\Model\Indexer\Scope\State $state
         */
        public function __construct(\Magento\CatalogSearch\Model\Indexer\Scope\State $state)
        {
            $this->state = $state;
        }

        /**
         * Get state
         *
         * @return \Magento\CatalogSearch\Model\Indexer\Scope\State
         */
        public function get()
        {
            return $this->state;
        }
    }
}
