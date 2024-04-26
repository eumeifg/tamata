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
namespace Magedelight\Catalog\Model\Config\Source;

class Websites implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Registry $registry
    ) {
        $this->systemStore = $systemStore;
        $this->registry = $registry;
    }

    public function toOptionArray($productWebsites = [])
    {
        $websites = $this->systemStore->getWebsiteValuesForForm(true);

        if (!empty($productWebsites)) {
            foreach ($websites as $key => $website) {
                if ($website['value'] != 0 && !in_array($website['value'], $productWebsites)) {
                    unset($websites[$key]);
                }
            }
        }
        return $websites;
    }
}
