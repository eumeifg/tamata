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

namespace Ktpl\ElasticSearch\Model\ScoreRule\Condition;

use Magento\Rule\Model\Condition\Context;

/**
 * Class Combine
 *
 * @package Ktpl\ElasticSearch\Model\ScoreRule\Condition
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var ProductCondition
     */
    protected $productCondition;

    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param ProductCondition $productCondition
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductCondition $productCondition,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->productCondition = $productCondition;

        $this->setType('Ktpl\ElasticSearch\Model\ScoreRule\Condition\Combine');
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
            } else {
                $pAttributes[] = [
                    'value' => ProductCondition::class . '|' . $code,
                    'label' => $label,
                ];
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Ktpl\ElasticSearch\Model\ScoreRule\Condition\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $pAttributes],
            ]
        );
        return $conditions;
    }

    /**
     * Collect validated attributes
     *
     * @param Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
