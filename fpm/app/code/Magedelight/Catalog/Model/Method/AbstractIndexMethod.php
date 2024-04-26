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

use Magedelight\Catalog\Api\MethodInterface;

abstract class AbstractIndexMethod extends AbstractMethod
{
    protected $_indexHelper;
    
    public function __construct(
        \Magedelight\Catalog\Helper\Index $indexHelper,
        Context $context
    ) {
        parent::__construct($context);
        $this->_indexHelper = $indexHelper;
    }

    abstract public function getColumnSelect();
    
    public function apply($collection, $currDir)
    {
        $select = $collection->getSelect();
        $col = $select->getPart('columns');
        $col[] = ['', $this->getColumnSelect(), static::CODE];
        $select->setPart('columns', $col);
        $collection->getSelect()->order(static::CODE . ' ' . $currDir);

        return $this;
    }
}
