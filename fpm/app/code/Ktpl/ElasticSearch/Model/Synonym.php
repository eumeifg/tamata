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

use Magento\Framework\Model\AbstractModel;
use Ktpl\ElasticSearch\Api\Data\SynonymInterface;

/**
 * Class Synonym
 *
 * @package Ktpl\ElasticSearch\Model
 */
class Synonym extends AbstractModel implements SynonymInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Ktpl\ElasticSearch\Model\ResourceModel\Synonym');
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get term
     *
     * @return string
     */
    public function getTerm()
    {
        return $this->getData(self::TERM);
    }

    /**
     * Set term
     *
     * @param string $input
     * @return SynonymInterface|Synonym
     */
    public function setTerm($input)
    {
        return $this->setData(self::TERM, $input);
    }

    /**
     * Get synonyms
     *
     * @return string
     */
    public function getSynonyms()
    {
        return $this->getData(self::SYNONYMS);
    }

    /**
     * Set synonyms
     *
     * @param string $input
     * @return SynonymInterface|Synonym
     */
    public function setSynonyms($input)
    {
        return $this->setData(self::SYNONYMS, $input);
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set store id
     *
     * @param int $input
     * @return SynonymInterface|Synonym
     */
    public function setStoreId($input)
    {
        return $this->setData(self::STORE_ID, $input);
    }
}
