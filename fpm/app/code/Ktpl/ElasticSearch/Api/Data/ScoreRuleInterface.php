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

namespace Ktpl\ElasticSearch\Api\Data;

/**
 * Interface ScoreRuleInterface
 *
 * @package Ktpl\ElasticSearch\Api\Data
 */
interface ScoreRuleInterface
{
    const TABLE_NAME = 'ktpl_search_score_rule';
    const INDEX_TABLE_NAME = 'ktpl_search_score_rule_index';

    const ID = 'rule_id';

    const TITLE = 'title';
    const IS_ACTIVE = 'is_active';
    const STATUS = 'status';
    const ACTIVE_FROM = 'active_from';
    const ACTIVE_TO = 'active_to';
    const STORE_IDS = 'store_ids';

    const SCORE_FACTOR = 'score_factor';

    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const POST_CONDITIONS_SERIALIZED = 'post_conditions_serialized';

    /**
     * Get Id
     *
     * @return string
     */
    public function getId();

    /**
     * Set title
     *
     * @param string $value
     * @return $this
     */
    public function setTitle($value);

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set is active
     *
     * @param bool $value
     * @return $this
     */
    public function setIsActive($value);

    /**
     * Get is active
     * @return bool
     */
    public function isActive();

    /**
     * Set status
     *
     * @param string $value
     * @return $this
     */
    public function setStatus($value);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set active form
     *
     * @param string $value
     * @return $this
     */
    public function setActiveFrom($value);

    /**
     * Get active form
     *
     * @return string
     */
    public function getActiveFrom();

    /**
     * Set active to
     *
     * @param string $value
     * @return $this
     */
    public function setActiveTo($value);

    /**
     * Get active to
     *
     * @return string
     */
    public function getActiveTo();

    /**
     * Set store ids
     *
     * @param array $value
     * @return $this
     */
    public function setStoreIds($value);

    /**
     * Get store ids
     *
     * @return array
     */
    public function getStoreIds();

    /**
     * Set score factor
     *
     * @param string $value
     * @return $this
     */
    public function setScoreFactor($value);

    /**
     * Get store factor
     *
     * @return string
     */
    public function getScoreFactor();

    /**
     * Set conditions serialized
     *
     * @param string $value
     * @return $this
     */
    public function setConditionsSerialized($value);

    /**
     * Get conditions serialized
     *
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * Set post conditions serialized
     *
     * @param string $value
     * @return $this
     */
    public function setPostConditionsSerialized($value);

    /**
     * Get post conditions serialized
     *
     * @return string
     */
    public function getPostConditionsSerialized();

    /**
     * Get rule
     *
     * @return \Ktpl\ElasticSearch\Model\ScoreRule\Rule
     */
    public function getRule();
}
