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
namespace Magedelight\Backend\App;

/**
 * Description of DefaultPath
 *
 * @author Rocket Bazaar Core Team
 */
class DefaultPath implements \Magento\Framework\App\DefaultPathInterface
{
    /**
     * @var array
     */
    protected $_parts;

    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(\Magedelight\Backend\App\ConfigInterface $config)
    {
        $pathParts = ['sellerhtml','seller','index','index'];//explode('/', $config->getValue('web/default/admin'));

        $this->_parts = [
            'area' => isset($pathParts[0]) ? $pathParts[0] : '',
            'module' => isset($pathParts[1]) ? $pathParts[1] : 'seller',
            'controller' => isset($pathParts[2]) ? $pathParts[2] : 'index',
            'action' => isset($pathParts[3]) ? $pathParts[3] : 'index',
        ];
    }

    /**
     * Retrieve default path part by code
     *
     * @param string $code
     * @return string
     */
    public function getPart($code)
    {
        return isset($this->_parts[$code]) ? $this->_parts[$code] : null;
    }
}
