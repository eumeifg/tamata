<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Model\Config;

use Magento\Framework\Module\Dir;

class SchemaLocator extends \Magento\Email\Model\Template\Config\SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    protected $schema = null;
    
    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magedelight_Backend') . '/email_templates.xsd';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->_schema;
    }
    
    public function getPerFileSchema()
    {
        return $this->_schema;
    }
}
