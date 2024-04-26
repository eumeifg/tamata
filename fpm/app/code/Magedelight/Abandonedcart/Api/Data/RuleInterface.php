<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Api\Data;

interface RuleInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RULE_ID           = 'abandoned_cart_rule_id   ';
    const RULE_NAME         = 'rule_name';
    const STATUS            = 'status';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';
    
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param $id
     * @return RuleInterface
     */
    public function setId($id);

    /**
     * Get Rule Name
     *
     * @return string
     */
    public function getRuleName();

    /**
     * Set Rule Name
     *
     * @param $ruleName
     * @return mixed
     */
    public function setRuleName($ruleName);

    /**
     * Get Status
     *
     * @return bool|int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param $status
     * @return RuleInterface
     */
    public function setIsActive($status);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * set created at
     *
     * @param $createdAt
     * @return RuleInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return RuleInterface
     */
    public function setUpdatedAt($updatedAt);
}
