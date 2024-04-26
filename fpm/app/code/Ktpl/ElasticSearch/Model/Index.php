<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Model
 */
class Index extends AbstractModel implements IndexInterface
{
    /**
     * Index constructor.
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    )
    {
        $this->_init('Ktpl\ElasticSearch\Model\ResourceModel\Index');

        parent::__construct($context, $registry);
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * Set title
     *
     * @param string $input
     * @return IndexInterface|AbstractModel
     */
    public function setTitle($input)
    {
        return parent::setData(self::TITLE, $input);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return parent::getData(self::IDENTIFIER);
    }

    /**
     * Set identifier
     *
     * @param string $input
     * @return IndexInterface|AbstractModel
     */
    public function setIdentifier($input)
    {
        return parent::setData(self::IDENTIFIER, $input);
    }

    /**
     * Get position
     *
     * @return number
     */
    public function getPosition()
    {
        return parent::getData(self::POSITION);
    }

    /**
     * Set position
     *
     * @param number $input
     * @return IndexInterface|AbstractModel
     */
    public function setPosition($input)
    {
        return parent::setData(self::POSITION, $input);
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $data = unserialize(parent::getData(self::ATTRIBUTES_SERIALIZED));

        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * Set attributes
     *
     * @param array $input
     * @return IndexInterface|AbstractModel
     */
    public function setAttributes($input)
    {
        return parent::setData(self::ATTRIBUTES_SERIALIZED, serialize($input));
    }

    /**
     * Set properties
     *
     * @param array $input
     * @return IndexInterface|AbstractModel
     */
    public function setProperties($input)
    {
        return parent::setData(self::PROPERTIES_SERIALIZED, serialize($input));
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param string $input
     * @return IndexInterface|AbstractModel
     */
    public function setStatus($input)
    {
        return parent::setData(self::STATUS, $input);
    }

    /**
     * Get is active
     *
     * @return number
     */
    public function getIsActive()
    {
        return parent::getData(self::IS_ACTIVE);
    }

    /**
     * Set is active
     *
     * @param number $input
     * @return IndexInterface|AbstractModel
     */
    public function setIsActive($input)
    {
        return parent::setData(self::IS_ACTIVE, $input);
    }

    /**
     * Get property
     *
     * @param string $key
     * @return bool|mixed|string
     */
    public function getProperty($key)
    {
        $props = $this->getProperties();

        if (isset($props[$key])) {
            return $props[$key];
        }

        return false;
    }

    /**
     * Get properties
     *
     * @return array
     */
    public function getProperties()
    {
        $data = unserialize(parent::getData(self::PROPERTIES_SERIALIZED));

        if (is_array($data)) {
            return $data;
        }

        return [];
    }
}
