<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Authorization
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Authorization\Acl;

class RootResource extends \Magento\Framework\Acl\RootResource
{

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->_identifier = $identifier;
    }
}
