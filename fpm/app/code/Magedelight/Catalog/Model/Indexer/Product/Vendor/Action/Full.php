<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\Indexer\Product\Vendor\Action;

class Full extends \Magedelight\Catalog\Model\Indexer\Product\Vendor\AbstractAction
{

    /**
     * @param type $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids = null)
    {
        $this->logger->addDebug('execute-full');
        try {
            $this->reindexAll();
        } catch (\Exception $e) {
            $this->logger->addDebug($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
