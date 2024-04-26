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
namespace Magedelight\Abandonedcart\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magedelight\Abandonedcart\Api\Data\RuleInterface;

interface RuleRepositoryInterface
{

    /**
     * @param RuleInterface $data
     * @return mixed
     */
    public function save(RuleInterface $data);
    
    /**
     * @param $ruleId
     * @return mixed
     */
    public function getById($ruleId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Abandonedcart\Api\Data\DataSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param RuleInterface $data
     * @return mixed
     */
    public function delete(RuleInterface $data);

    /**
     * @param $ruleId
     * @return mixed
     */
    public function deleteById($ruleId);
}
