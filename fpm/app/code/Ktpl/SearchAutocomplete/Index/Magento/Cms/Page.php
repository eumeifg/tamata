<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Index\Magento\Cms;

use Magento\Cms\Helper\Page as PageHelper;
use Ktpl\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

/**
 * Class Page
 *
 * @package Ktpl\SearchAutocomplete\Index\Magento\Cms
 */
class Page extends AbstractIndex
{
    /**
     * @var PageHelper
     */
    private $pageHelper;

    /**
     * Page constructor.
     *
     * @param PageHelper $pageHelper
     */
    public function __construct(
        PageHelper $pageHelper
    )
    {
        $this->pageHelper = $pageHelper;
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];

        /** @var \Magento\Cms\Model\Page $page */
        foreach ($this->getCollection() as $page) {
            $items[] = $this->mapPage($page);
        }

        return $items;
    }

    /**
     * Map page
     *
     * @param \Magento\Cms\Model\Page $page
     * @return array
     */
    public function mapPage($page)
    {
        return [
            'name' => $page->getTitle(),
            'url' => $this->pageHelper->getPageUrl($page->getIdentifier()),
        ];
    }

    /**
     * Map data
     *
     * @param $data
     * @return mixed
     */
    public function map($data)
    {
        foreach ($data as $entityId => $itm) {
            $om = ObjectManager::getInstance();
            $entity = $om->create('Magento\Cms\Model\Page')->load($entityId);

            $map = $this->mapPage($entity);
            $data[$entityId]['autocomplete'] = $map;
        }

        return $data;
    }
}
