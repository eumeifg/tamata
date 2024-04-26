<?php

namespace Ktpl\GoogleMapAddress\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class AutocompleteConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Ktpl\GoogleMapAddress\Helper\Data
     */
    private $helper;

    /**
     * @param \Ktpl\GoogleMapAddress\Helper\Data $helper
     */
    public function __construct(
        \Ktpl\GoogleMapAddress\Helper\Data $helper,
        LayoutInterface $layout
    ) {
        $this->_layout = $layout;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config['google_map_address'] = [
            'active'        => $this->helper->getConfigValue('google_map/general/active'),
            'api_key'  =>    $this->helper->getConfigValue('google_map/general/google_api_key'),
            'map_content' => $this->helper->getConfigValue('google_map/general/active') ? $this->_layout->createBlock('Magento\Backend\Block\Template')->setTemplate('Ktpl_GoogleMapAddress::googlemapaddress.phtml')->toHtml() : ""
        ];
        return $config;
    }
}
