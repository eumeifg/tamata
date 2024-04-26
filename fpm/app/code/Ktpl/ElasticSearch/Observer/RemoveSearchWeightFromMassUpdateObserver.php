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

namespace Ktpl\ElasticSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RemoveSearchWeightFromMassUpdateObserver
 *
 * @package Ktpl\ElasticSearch\Observer
 */
class RemoveSearchWeightFromMassUpdateObserver implements ObserverInterface
{
    /**
     * Set form excluded field list
     *
     * @param Observer $observer
     * @return $this|voids
     */
    public function execute(Observer $observer)
    {
        $block  = $observer->getEvent()->getObject();
        $list   = $block->getFormExcludedFieldList();
        $list[] = 'search_weight';

        $block->setFormExcludedFieldList($list);

        return $this;
    }
}
