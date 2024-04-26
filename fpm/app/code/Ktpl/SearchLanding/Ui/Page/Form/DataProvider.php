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

namespace Ktpl\SearchLanding\Ui\Page\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Ktpl\SearchLanding\Api\Repository\PageRepositoryInterface;

/**
 * Class DataProvider
 *
 * @package Ktpl\SearchLanding\Ui\Page\Form
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * DataProvider constructor.
     *
     * @param PageRepositoryInterface $pageRepository
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->pageRepository = $pageRepository;
        $this->collection = $this->pageRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $result = [];

        foreach ($this->pageRepository->getCollection() as $page) {
            $pageData = $page->getData();

            $result[$page->getId()] = $pageData;
        }

        return $result;
    }
}
