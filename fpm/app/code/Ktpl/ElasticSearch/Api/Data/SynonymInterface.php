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

namespace Ktpl\ElasticSearch\Api\Data;

/**
 * Interface SynonymInterface
 *
 * @package Ktpl\ElasticSearch\Api\Data
 */
interface SynonymInterface
{
    const TABLE_NAME = 'ktpl_search_synonym';

    const ID       = 'synonym_id';
    const TERM     = 'term';
    const SYNONYMS = 'synonyms';
    const STORE_ID = 'store_id';

    /**
     * Get Id
     *
     * @return int
     */
    public function getId();

    /**
     * Get term
     *
     * @return string
     */
    public function getTerm();

    /**
     * Set term
     *
     * @param string $input
     * @return $this
     */
    public function setTerm($input);

    /**
     * Get synonyms
     *
     * @return string
     */
    public function getSynonyms();

    /**
     * Set synonyms
     *
     * @param string $input
     * @return $this
     */
    public function setSynonyms($input);

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $input
     * @return $this
     */
    public function setStoreId($input);
}
