<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Api\Data;

/**
 * @api
 */
interface CommissionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Magedelight\Commissions\Api\Data\CommissionInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Magedelight\Commissions\Api\Data\CommissionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
