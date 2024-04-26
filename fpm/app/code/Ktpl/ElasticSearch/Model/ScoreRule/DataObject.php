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

namespace Ktpl\ElasticSearch\Model\ScoreRule;

use Magento\Framework\Model\AbstractModel;

/**
 * Class DataObject
 *
 * @package Ktpl\ElasticSearch\Model\ScoreRule
 */
class DataObject extends AbstractModel
{
    /**
     * DataObject constructor.
     */
    public function __construct()
    {
    }

    /**
     * Load data object
     *
     * @param int $modelId
     * @param null $field
     * @return $this|AbstractModel
     */
    public function load($modelId, $field = null)
    {
        return $this;
    }
}
