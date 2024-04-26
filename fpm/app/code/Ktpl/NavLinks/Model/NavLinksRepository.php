<?php
/*
 * php version 7.2.17
 */
namespace Ktpl\NavLinks\Model;

use Ktpl\NavLinks\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;

class NavLinksRepository {

    /**
     * @var helperData
     */
    protected $helperData;

    const BEFORE_MENU_ITEMS = 'navlinks/before_menu/items';
    const AFTER_MENU_ITEMS = 'navlinks/after_menu/items';

    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMenuItems($param)
    {
        if ($param == 0) {
            $menuType = self::BEFORE_MENU_ITEMS;
        } elseif ($param == 1) {
            $menuType = self::AFTER_MENU_ITEMS;
        } else {
            throw new NoSuchEntityException(__('The menu with the "%1" ID doesn\'t exist.', $param));
        }

        $menuData = $this->helperData->getMenuItems($menuType);
        if (!$menuData) {
            throw new NoSuchEntityException(__('The menu with the "%1" ID doesn\'t exist.', $param));
        }

        return $menuData;
    }
}