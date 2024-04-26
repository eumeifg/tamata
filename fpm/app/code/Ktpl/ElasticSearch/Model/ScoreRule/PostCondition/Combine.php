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

namespace Ktpl\ElasticSearch\Model\ScoreRule\PostCondition;

use Magento\Rule\Model\Condition\Context;

/**
 * Class Combine
 *
 * @package Ktpl\ElasticSearch\Model\ScoreRule\PostCondition
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var RequestCondition
     */
    private $requestCondition;

    /**
     * Combine constructor.
     *
     * @param RequestCondition $requestCondition
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        RequestCondition $requestCondition,
        Context $context,
        array $data = []
    )
    {
        $data['prefix'] = 'post_conditions';

        $this->requestCondition = $requestCondition;

        parent::__construct($context, $data);

        $this->setType('Ktpl\ElasticSearch\Model\ScoreRule\PostCondition\Combine');
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $searchRequestAttributes = [];

        foreach ($this->requestCondition->getAttributeOption() as $code => $label) {
            $searchRequestAttributes[] = [
                'value' => RequestCondition::class . '|' . $code,
                'label' => $label,
            ];
        }


        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Ktpl\ElasticSearch\Model\ScoreRule\PostCondition\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Search Request'), 'value' => $searchRequestAttributes],
            ]
        );
        return $conditions;
    }
}
