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
namespace Magedelight\Catalog\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * Grid column widget for rendering action grid cells
 *
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $jsonEncoder, $data);
    }

    /**
     * Renders column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $websiteIds = explode(",", $row->getWebsites());
        $counter = 0;
        foreach ($websiteIds as $websiteId) {
            $counter++;
            $website = $this->storeManager->getWebsite($websiteId);

            $html .= $website->getName();
            $html .= '<br/>';
            $html .= '<ul>';
            foreach ($website->getGroups() as $group) {
                $storeIds = [];
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $html .= '<li>';
                    $html .= $this->_attributesToHtml(
                        $this->_prepareAttributes($row, $website, $store),
                        $website,
                        $store
                    );
                    $html .= '</li>';
                }
            }
            $html .= '</ul>';
            if ($counter < count($websiteIds)) {
                $html .= '<br/><hr></hr>';
            }
        }
        return $html;
    }

    /**
     * Prepare attributes
     *
     * @param $row
     * @param $website
     * @param $store
     * @return array
     */
    protected function _prepareAttributes($row, $website, $store)
    {
        $attributes = [];
        if ($this->getColumn()->getDataAttribute()) {
            foreach ($this->getColumn()->getDataAttribute() as $key => $attr) {
                $attr['vendorOffers']['url'] = $this->getUrl(
                    $attr['vendorOffers']['url'],
                    [
                            'id' => $row->getId(),
                            'store_id' => $store->getId(),
                            'website_id' => $website->getId(),
                            'popup'=>1
                        ]
                );
                $attr['vendorOffers']['title'] = $row->getVendorSku();
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }
        return $attributes;
    }

    /**
     * Attributes list to html
     *
     * @param array $attributes
     * @return string
     */
    protected function _attributesToHtml($attributes, $website, $store)
    {
        $html = '<a href="javascript:void(0)"';
        foreach ($attributes as $attributeKey => $attributeValue) {
            if ($attributeValue === null || $attributeValue == '') {
                continue;
            }
            $html .= $attributeKey . '="' . $this->escapeHtml($attributeValue) . '" ';
        }
        $html .= '>' . __('Edit') . ' - (' . $store->getName() . ')</a>';
        return $html;
    }
}
