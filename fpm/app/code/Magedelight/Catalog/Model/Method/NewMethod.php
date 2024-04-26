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
namespace Magedelight\Catalog\Model\Method;

use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;

class NewMethod extends AbstractMethod
{
    const CODE = 'created_at';

    const NAME = 'New Arrival';

    public function __construct(
        Context $context,
        FlatState $flatState
    ) {
        $this->flatState = $flatState;
        parent::__construct($context);
    }

    /**
     * @param $collection
     * @param $currDir
     *
     * @return $this
     */
    public function apply($collection, $currDir)
    {
        $new = false;
        $orders = $collection->getSelect()->getPart(\Zend_Db_Select::ORDER);
        foreach ($orders as $k => $v) {
            if (is_object($v)) {
                continue;
            } elseif (false !== strpos($v[0], 'created_at')) {
                $new = $k;
            }
        }
        if (!$new) {
            if ($this->flatState->isAvailable()) {
                $collection->getSelect()->order('created_at '.$currDir);
            } else {
                $collection->addAttributeToSort('created_at', $currDir);
            }
        }
        return $this;
    }
}
