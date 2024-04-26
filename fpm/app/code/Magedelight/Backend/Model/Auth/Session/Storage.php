<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Model\Auth\Session;

class Storage extends \Magento\Framework\Session\Storage
{
    /**
     * @param \Magedelight\Vendor\Model\Config\Share $configShare
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $namespace
     * @param array $data
     */
    public function __construct(
        \Magedelight\Vendor\Model\Config\Share $configShare,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $namespace = 'seller',
        array $data = []
    ) {
        parent::__construct($namespace, $data);
    }
}
