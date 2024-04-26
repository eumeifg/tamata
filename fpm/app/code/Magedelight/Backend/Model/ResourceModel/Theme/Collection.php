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
namespace Magedelight\Backend\Model\ResourceModel\Theme;

/**
 * Theme collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements
    \Magento\Framework\View\Design\Theme\Label\ListInterface,
    \Magento\Framework\View\Design\Theme\ListInterface
{
    /**
     * Default page size
     */
    const DEFAULT_PAGE_SIZE = 6;

    /**
     * @var string
     */
    protected $_idFieldName = 'theme_id';

    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Theme\Model\Theme::class, \Magento\Theme\Model\ResourceModel\Theme::class);
    }

    /**
     * Add title for parent themes
     *
     * @return $this
     */
    public function addParentTitle()
    {
        $this->getSelect()->joinLeft(
            ['parent' => $this->getMainTable()],
            'main_table.parent_id = parent.theme_id',
            ['parent_theme_title' => 'parent.theme_title']
        );
        return $this;
    }

    /**
     * Add area filter
     *
     * @param string $area
     * @return $this
     */
    public function addAreaFilter($area = \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE)
    {
        $this->getSelect()->where('main_table.area=?', $area);
        return $this;
    }

    /**
     * Add type filter in relations
     *
     * @param int $typeParent
     * @param int $typeChild
     * @return $this
     */
    public function addTypeRelationFilter($typeParent, $typeChild)
    {
        $this->getSelect()->join(
            ['parent' => $this->getMainTable()],
            'main_table.parent_id = parent.theme_id',
            ['parent_type' => 'parent.type']
        )->where(
            'parent.type = ?',
            $typeParent
        )->where(
            'main_table.type = ?',
            $typeChild
        );
        return $this;
    }

    /**
     * Add type filter
     *
     * @param string|array $type
     * @return $this
     */
    public function addTypeFilter($type)
    {
        $this->addFieldToFilter('main_table.type', ['in' => $type]);
        return $this;
    }

    /**
     * Filter visible themes in backend (physical and virtual only)
     *
     * @return $this
     */
    public function filterVisibleThemes()
    {
        $this->addTypeFilter(
            [
                \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL,
                \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL,
            ]
        );
        return $this;
    }

    /**
     * Return array for select field
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('theme_id', 'theme_title');
    }

    /**
     * Return array for grid column
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('theme_id', 'theme_title');
    }

    /**
     * Get theme from DB by area and theme_path
     *
     * @param string $fullPath
     * @return \Magento\Theme\Model\Theme
     */
    public function getThemeByFullPath($fullPath)
    {
        $this->_reset()->clear();
        list($area, $themePath) = explode('/', $fullPath, 2);
        $this->addFieldToFilter('area', $area);
        $this->addFieldToFilter('theme_path', $themePath);

        return $this->getFirstItem();
    }

    /**
     * Set page size
     *
     * @param int $size
     * @return $this
     */
    public function setPageSize($size = self::DEFAULT_PAGE_SIZE)
    {
        return parent::setPageSize($size);
    }

    /**
     * Update all child themes relations
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $themeModel
     * @return $this
     */
    public function updateChildRelations(\Magento\Framework\View\Design\ThemeInterface $themeModel)
    {
        $parentThemeId = $themeModel->getParentId();
        $this->addFieldToFilter('parent_id', ['eq' => $themeModel->getId()])->load();

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($this->getItems() as $theme) {
            $theme->setParentId($parentThemeId)->save();
        }
        return $this;
    }

    /**
     * Filter frontend physical theme.
     * All themes or per page if set page and page size (page size is optional)
     *
     * @param int $page
     * @param int $pageSize
     * @return $this
     */
    public function filterPhysicalThemes(
        $page = null,
        $pageSize = \Magento\Theme\Model\ResourceModel\Theme\Collection::DEFAULT_PAGE_SIZE
    ) {
        $this->addAreaFilter(
            \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE
        )->addTypeFilter(
            \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL
        );
        if ($page) {
            $this->setPageSize($pageSize)->setCurPage($page);
        }
        return $this;
    }

    /**
     * Filter theme customization
     *
     * @param string $area
     * @param int $type
     * @return $this
     */
    public function filterThemeCustomizations(
        $area = \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
        $type = \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
    ) {
        $this->addAreaFilter($area)->addTypeFilter($type);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLabels()
    {
        $labels = $this->loadRegisteredThemes();
        return $labels->toOptionArray();
    }

    /**
     * @return $this
     */
    public function loadRegisteredThemes()
    {
        $this->_reset()->clear();
        return $this->setOrder('theme_title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->filterVisibleThemes()->addAreaFilter(\Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE);
    }
}
