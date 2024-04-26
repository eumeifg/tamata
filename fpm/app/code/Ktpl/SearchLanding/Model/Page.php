<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchLanding
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchLanding\Model;

use Magento\Framework\Model\AbstractModel;
use Ktpl\SearchLanding\Api\Data\PageInterface;

/**
 * Class Page
 *
 * @package Ktpl\SearchLanding\Model
 */
class Page extends AbstractModel implements PageInterface
{
    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get query text
     *
     * @return string
     */
    public function getQueryText()
    {
        return $this->getData(self::QUERY_TEXT);
    }

    /**
     * Set query text
     *
     * @param string $value
     * @return PageInterface|Page
     */
    public function setQueryText($value)
    {
        return $this->setData(self::QUERY_TEXT, $value);
    }

    /**
     * Get url key
     *
     * @return string
     */
    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * Set url key
     *
     * @param string $value
     * @return PageInterface|Page
     */
    public function setUrlKey($value)
    {
        return $this->setData(self::URL_KEY, $value);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set title
     *
     * @param string $value
     * @return PageInterface|Page
     */
    public function setTitle($value)
    {
        return $this->setData(self::TITLE, $value);
    }

    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Set meta description
     *
     * @param string $value
     * @return PageInterface|Page
     */
    public function setMetaDescription($value)
    {
        return $this->setData(self::META_DESCRIPTION, $value);
    }

    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Set meta keywords
     *
     * @param string $value
     * @return PageInterface|Page
     */
    public function setMetaKeywords($value)
    {
        return $this->setData(self::META_KEYWORDS, $value);
    }

    /**
     * Get layout update
     *
     * @return string
     */
    public function getLayoutUpdate()
    {
        return $this->getData(self::LAYOUT_UPDATE);
    }

    /**
     * Set layout update
     *
     * @param string $value
     * @return PageInterface|Page
     */
    public function setLayoutUpdate($value)
    {
        return $this->setData(self::LAYOUT_UPDATE, $value);
    }

    /**
     * Get store ids
     *
     * @return array|string
     */
    public function getStoreIds()
    {
        return array_filter(explode(',', $this->getData(self::STORE_IDS)));
    }

    /**
     * Set store ids
     *
     * @param string $value
     * @return PageInterface|Page
     */
    public function setStoreIds($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        return $this->setData(self::STORE_IDS, $value);
    }

    /**
     * Get is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set is active
     *
     * @param bool $value
     * @return PageInterface|Page
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Page::class);
    }
}
