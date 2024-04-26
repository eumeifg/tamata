<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Block\Sellerhtml\Widget\Grid\Column\Renderer;

/**
 * Description of Store
 *
 * @author Rocket Bazaar team
 */
class Store extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Store constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->systemStore = $systemStore;
    }

    /**
     * Retrieve System Store model
     *
     * @return \Magento\Store\Model\System\Store
     */
    protected function _getStoreModel()
    {
        return $this->systemStore;
    }
    
    /**
     *
     * @param Array|int $origStores
     * @return string
     */
    public function render($origStores)
    {
        $out = '';
        if (!is_array($origStores)) {
            $origStores = [$origStores];
        }

        if (empty($origStores)) {
            return '';
        } elseif (in_array(0, $origStores) && count($origStores) == 1) {
            return __('All Store Views');
        }

        $data = $this->_getStoreModel()->getStoresStructure(false, $origStores);

        foreach ($data as $website) {
            $out .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $out .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $out .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }

        return $out;
    }
    
    /**
     *
     * @param Array|int $origWebsiteId
     * @return string
     */
    public function renderWebsite($origWebsiteId)
    {
        if (empty($origWebsiteId)) {
            return '';
        } else {
            return $this->_getStoreModel()->getWebsiteName($origWebsiteId[0]);
        }
    }
}
