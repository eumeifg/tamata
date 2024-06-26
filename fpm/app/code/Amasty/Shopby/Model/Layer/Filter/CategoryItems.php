<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Layer\Filter;

/**
 * Class CategoryItems
 * @package Amasty\Shopby\Model\Layer\Filter
 */
class CategoryItems implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var string
     */
    protected $startPath = '';

    /**
     * @var int
     */
    protected $countAllItems = 0;

    /**
     * @param null $path
     *
     * @return array
     */
    public function getItems($path = null)
    {
        if ($path === null) {
            $path = $this->startPath;
        }

        return isset($this->items[$path]) ? $this->items[$path] : [];
    }

    /**
     * @param string|null $path
     *
     * @return int
     */
    public function getItemsCount($path = null)
    {
        return count($this->getItems($path));
    }

    /**
     * @param string $startPath
     *
     * @return $this
     */
    public function setStartPath($startPath)
    {
        $this->startPath = $startPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartPath()
    {
        return $this->startPath;
    }

    /**
     * @param string $path
     * @param object $item
     *
     * @return $this
     */
    public function addItem($path, $item)
    {
        $this->items[$path][] = $item;
        return $this;
    }

    /**
     * Set count of all items
     * @param int $count
     *
     * @return $this
     */
    public function setCount($count)
    {
        $this->countAllItems = $count;
        return $this;
    }

    /**
     * Get count of all items
     *
     * @return int
     */
    public function getCount()
    {
        return $this->countAllItems;
    }

    /**
     * Get all items in one array
     * @return array
     */
    public function getAllItems()
    {
        $allItems = [];
        foreach ($this->items as $items) {
            // @codingStandardsIgnoreLine
            $allItems = array_merge($allItems, $items);
        }
        return $allItems;
    }

    /**
     * @param $itemId
     * @return array
     */
    public function getParentsAndChildrenByItemId($itemId)
    {
        $parents = [];
        $children = [];
        foreach ($this->items as $path => $items) {
            $currentPath = explode("/", $path);
            $hasChildren = false;
            if (in_array($itemId, $currentPath)) {
                $hasChildren = true;
            }
            foreach ($items as $item) {
                if ($hasChildren) {
                    $children[] = $item->getValue();
                }
                if ($item->getValue() ==  $itemId) {
                    $parents = $currentPath;
                }
            }
        }

        $result = array_merge($parents, $children);
        return $result;
    }

    /**
     * Retrieve count of collection loaded items
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}
