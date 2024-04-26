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
namespace Magedelight\Abandonedcart\Model\Config\Source;

class Websites implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function toOptionArray()
    {
        $websites = $this->storeManager->getWebsites();
        $options = [];
        foreach ($websites as $websiteId => $website) {
            $options[] = [
                   'value'=>$websiteId,
                   'label'=>$website->getName()
            ];
        }
        return $options;
    }
}
