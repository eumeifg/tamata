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

namespace Ktpl\ElasticSearch\Model\Index;

use Ktpl\ElasticSearch\Api\Data\Index\DataMapperInterface;
use Ktpl\ElasticSearch\Api\Service\SynonymServiceInterface;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class DataMapper
 *
 * @package Ktpl\ElasticSearch\Model\Index
 */
class DataMapper implements DataMapperInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var SynonymServiceInterface
     */
    private $synonymService;

    /**
     * DataMapper constructor.
     *
     * @param Config $config
     * @param SynonymServiceInterface $synonymService
     */
    public function __construct(
        Config $config,
        SynonymServiceInterface $synonymService
    )
    {
        $this->config = $config;
        $this->synonymService = $synonymService;
    }

    /**
     * Map
     *
     * @param array $documents
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param string $indexIdentifier
     * @return array
     */
    public function map(array $documents, $dimensions, $indexIdentifier)
    {
        foreach ($documents as $id => $doc) {
            $documents[$id] = $this->recursiveMap($doc);
        }

        return $documents;
    }

    /**
     * Recursive map
     *
     * @param $data
     * @return array|string
     */
    public function recursiveMap($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->recursiveMap($value);
            }
        } elseif (is_string($data)) {
            $string = strtolower(strip_tags($data));

            $expressions = $this->config->getLongTailExpressions();

            foreach ($expressions as $expr) {
                $matches = null;
                preg_match_all($expr['match_expr'], $string, $matches);

                foreach ($matches[0] as $math) {
                    $math = preg_replace($expr['replace_expr'], $expr['replace_char'], $math);
                    $string .= ' ' . $math;
                }
            }

            $complexSynonyms = $this->synonymService->getComplexSynonyms(0);

            foreach ($complexSynonyms as $synonym) {
                if (strpos($string, $synonym->getTerm()) !== false) {
                    $string .= ' ' . $synonym->getSynonyms();
                }

                $terms = explode(',', $synonym->getSynonyms());
                foreach ($terms as $term) {
                    if (strpos($string, $term) !== false) {
                        $string .= ' ' . $synonym->getTerm();
                    }
                }
            }

            $string = preg_replace('/\s\s+/', ' ', $string);

            return ' ' . $string . '';
        }

        return $data;
    }
}
