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

use Magento\Framework\Api\SearchResultsInterface;

interface RuleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get data list.
     *
     * @return \Magedelight\Abandonedcart\Api\Data\RuleInterface[]
     */
    public function getItems();
    
    /**
     * Set data list.
     *
     * @param \Magedelight\Abandonedcart\Api\Data\RuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
