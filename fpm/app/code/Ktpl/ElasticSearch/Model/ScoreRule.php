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

namespace Ktpl\ElasticSearch\Model;

use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Ktpl\ElasticSearch\Model\ScoreRule\Rule;
use Ktpl\ElasticSearch\Model\ScoreRule\RuleFactory;

/**
 * Class ScoreRule
 *
 * @package Ktpl\ElasticSearch\Model
 */
class ScoreRule extends AbstractModel implements ScoreRuleInterface
{
    /**
     * @var Rule
     */
    private $rule;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * ScoreRule constructor.
     *
     * @param RuleFactory $ruleFactory
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        RuleFactory $ruleFactory,
        Context $context,
        Registry $registry
    ) {
        $this->ruleFactory = $ruleFactory;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Ktpl\ElasticSearch\Model\ResourceModel\ScoreRule::class);
    }

    /**
     * Get rule
     *
     * @return Rule
     */
    public function getRule()
    {
        if (!$this->rule) {
            $this->rule = $this->ruleFactory->create()
                ->setData(self::CONDITIONS_SERIALIZED, $this->getConditionsSerialized())
                ->setData(self::POST_CONDITIONS_SERIALIZED, $this->getPostConditionsSerialized());
        }

        return $this->rule;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set title
     *
     * @param string $value
     * @return ScoreRuleInterface|ScoreRule
     */
    public function setTitle($value)
    {
        return $this->setData(self::TITLE, $value);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set is active
     *
     * @param bool $value
     * @return ScoreRuleInterface|ScoreRule
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * Get is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set status
     *
     * @param string $value
     * @return ScoreRuleInterface|ScoreRule
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set active form
     *
     * @param string $value
     * @return ScoreRuleInterface|ScoreRule
     */
    public function setActiveFrom($value)
    {
        return $this->setData(self::ACTIVE_FROM, $value);
    }

    /**
     * Get active form
     *
     * @return string
     */
    public function getActiveFrom()
    {
        return $this->getData(self::ACTIVE_FROM);
    }

    /**
     * Set active to
     *
     * @param string $value
     * @return ScoreRuleInterface|ScoreRule
     */
    public function setActiveTo($value)
    {
        return $this->setData(self::ACTIVE_TO, $value);
    }

    /**
     * Get active to
     * @return string
     */
    public function getActiveTo()
    {
        return $this->getData(self::ACTIVE_TO);
    }

    /**
     * Set store ids
     *
     * @param array $value
     * @return ScoreRuleInterface|ScoreRule
     */
    public function setStoreIds($value)
    {
        if (is_array($value)) {
            $value = implode(',', array_filter($value));
        }

        return $this->setData(self::STORE_IDS, $value);
    }

    /**
     * Get store ids
     *
     * @return array
     */
    public function getStoreIds()
    {
        return explode(',', $this->getData(self::STORE_IDS));
    }

    /**
     * Set score factor
     *
     * @param string $value
     * @return ScoreRuleInterface|ScoreRule
     */
    public function setScoreFactor($value)
    {
        return $this->setData(self::SCORE_FACTOR, $value);
    }

    /**
     * Get score factor
     *
     * @return string
     */
    public function getScoreFactor()
    {
        return $this->getData(self::SCORE_FACTOR);
    }

    /**
     * Set Conditions Serialized
     *
     * @param string $value
     * @return ScoreRuleInterface|ScoreRule
     */
    public function setConditionsSerialized($value)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $value);
    }

    /**
     * Get Conditions Serialized
     *
     * @return string
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * Set Post Conditions Serialized
     *
     * @param string $value
     * @return ScoreRuleInterface|ScoreRule
     */
    public function setPostConditionsSerialized($value)
    {
        return $this->setData(self::POST_CONDITIONS_SERIALIZED, $value);
    }

    /**
     * Get Post Conditions Serialized
     *
     * @return string
     */
    public function getPostConditionsSerialized()
    {
        return $this->getData(self::POST_CONDITIONS_SERIALIZED);
    }
}
