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

namespace Ktpl\ElasticSearch\Index\Magento\Cms\Page;

use Ktpl\ElasticSearch\Api\Data\Index\DataMapperInterface;
use Ktpl\ElasticSearch\Service\ContentService;

/**
 * Class DataMapper
 *
 * @package Ktpl\ElasticSearch\Index\Magento\Cms\Page
 */
class DataMapper implements DataMapperInterface
{
    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * DataMapper constructor.
     *
     * @param ContentService $contentService
     */
    public function __construct(
        ContentService $contentService
    ) {
        $this->contentService = $contentService;
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
        $storeId = current($dimensions)->getValue();

        foreach ($documents as $id => $doc) {
            foreach ($doc as $key => $value) {
                $documents[$id][$key] .= $this->contentService->processHtmlContent($storeId, $value);
            }
        }

        return $documents;
    }
}
