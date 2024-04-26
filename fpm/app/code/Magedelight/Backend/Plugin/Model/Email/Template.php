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
namespace Magedelight\Backend\Plugin\Model\Email;

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Template
{

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $urlModel;

    public function __construct(
        \Magedelight\Backend\Model\UrlInterface $urlModel,
        StoreManagerInterface $storeManager
    ) {
        $this->urlModel = $urlModel;
        $this->storeManager = $storeManager;
    }
    
     /**
      * Generate URL for the specified store.
      *
      * @param Store $store
      * @param string $route
      * @param array $params
      * @return string
      */
    public function aroundGetUrl(\Magento\Email\Model\Template $subject, \Closure $proceed, Store $store, $route = '', $params = [])
    {
        if ($subject->getDesignConfig()->getArea() == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            $url = $this->urlModel->setScope($store);
            if ($this->storeManager->getStore()->getId() != $store->getId()) {
                $params['_scope_to_url'] = true;
            }
            return $url->getUrl($route, $params);
        } else {
            return $proceed($store, $route, $params);
        }
    }
}
