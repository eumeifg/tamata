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

namespace Ktpl\ElasticSearch\Model\ResourceModel\Stopword;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ktpl\ElasticSearch\Api\Data\StopwordInterface;

/**
 * Class Collection
 *
 * @package Ktpl\ElasticSearch\Model\ResourceModel\Stopword
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Ktpl\ElasticSearch\Model\Stopword', 'Ktpl\ElasticSearch\Model\ResourceModel\Stopword');
        $this->_idFieldName = StopwordInterface::ID;
    }
}
