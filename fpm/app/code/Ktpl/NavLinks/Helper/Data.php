<?php
/*
 * Copyright © 2018 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\NavLinks\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    const BEFORE_MENU_ITEMS = 'navlinks/before_menu/items';
    const AFTER_MENU_ITEMS = 'navlinks/after_menu/items';

    /**
     * Constructor
     * @param \Magento\Framework\App\Helper\Context $context
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
    }

    /**
     * Get config field value
     * @param type $field
     * @param type $storeId
     * @return type
     */
    public function getConfigValue($field, $storeId = null) {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * Get Menu Items in array format
     * @param string $menuType
     * @return array
     */
    public function getMenuItems($menuType = self::BEFORE_MENU_ITEMS)
    {
        $value = $this->getConfigValue($menuType);
        $menuItems = [];
        if($value){
            $menuItems = $this->serializer->unserialize($value);
        }
        $menuData = $this->getChildren($menuItems);
        return $menuData;
    }

    /**
     * Get children of current menu section
     * @param array $subMenus
     * @param string $field
     * @param int|null $value
     * @param type $formattedMenuData
     * @return array
     */
    protected function getChildren($subMenus, $field = 'parent', $value = '', $formattedMenuData = [])
    {
        foreach($subMenus as $menu) {
            if ( $menu[$field] === $value ) {
                $formattedMenuData[$menu['sortorder']] =  $this->formatMenuData($menu);
                if($this->hasChildren($subMenus, 'parent', $menu['sortorder'])) {
                    $children = $this->getChildren($subMenus, 'parent', $menu['sortorder']);
                    uasort($formattedMenuData, function($a, $b) {
                        return $a['sortorder'] - $b['sortorder'];
                    });
                    $formattedMenuData[$menu['sortorder']]['children'] = $children;
                }
            }
        }
        return $formattedMenuData;
    }

    /**
     * Arrange configuration array in Menu node array
     * @param array $menu
     * @return array
     */
    protected function formatMenuData($menu)
    {
        $formattedMenu = [];
        $formattedMenu['id'] = strtolower($menu['name']);
        $formattedMenu['name'] = $menu['name'];
        $formattedMenu['url'] = $menu['url'];
        $formattedMenu['sortorder'] = $menu['sortorder'];
        $formattedMenu['class'] = $menu['icon_class'];
        return $formattedMenu;
    }

    /**
     * Check if current menu has children
     * @param array $subMenus
     * @param string $field
     * @param int|null $value
     * @return boolean
     */
    protected function hasChildren($subMenus = [], $field = 'parent', $value = '')
    {
        foreach($subMenus as $menu)
        {
            if ( $menu[$field] === $value ) {
                return true;
            }
        }
        return false;
    }
}