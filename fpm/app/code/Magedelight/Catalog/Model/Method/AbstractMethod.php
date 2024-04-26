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

abstract class AbstractMethod extends \Magento\Framework\DataObject implements MethodInterface
{
    const CODE = '';
    const NAME = '';
    const ENABLED = true;

    protected $_objectManager;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_request;
    protected $_resource;
    protected $_productMetaData;
    protected $_moduleManager;

    public function __construct(
        Context $context
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_request = $context->getRequest();
        $this->_resource = $context->getResource();
        $this->_objectManager = $context->getObjectManager();
        $this->_productMetaData = $context->getProductMetaData();
        $this->_moduleManager = $context->getModuleManager();
        $this->_storeManager = $context->getStoreManager();
    }

    abstract public function apply($collection, $direction);
}
