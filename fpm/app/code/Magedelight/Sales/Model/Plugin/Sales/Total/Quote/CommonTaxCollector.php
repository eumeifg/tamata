<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\Plugin\Sales\Total\Quote;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;

class CommonTaxCollector
{
    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
    }

    /*
     * Set Product vendor to session variable
     */

    public function afterMapItem(
        \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $subject,
        $result,
        QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        AbstractItem $item
    ) {
        $itemObject = $result;
        $itemObject->setVendorId($item->getVendorId());
        return $itemObject;
    }
}
