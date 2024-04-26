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

namespace Ktpl\SearchAutocomplete\Index\Amasty\Blog;

use Ktpl\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

/**
 * Class Post
 *
 * @package Ktpl\SearchAutocomplete\Index\Amasty\Blog
 */
class Post extends AbstractIndex
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Post constructor.
     */
    public function __construct()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * Get size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->collection->getSize();
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];

        /** @var \Amasty\Blog\Model\Posts $post */
        foreach ($this->getCollection() as $post) {
            $items[] = $this->mapPost($post);
        }

        return $items;
    }

    /**
     * Map post
     *
     * @param \Amasty\Blog\Model\Posts $post
     * @return array
     */
    public function mapPost($post)
    {
        return [
            'name' => $post->getTitle(),
            'url' => $post->getPostUrl(),
        ];
    }

    public function map($data)
    {
        foreach ($data as $entityId => $itm) {
            $om = ObjectManager::getInstance();
            $entity = $om->create('Amasty\Blog\Model\Posts')->load($entityId);

            $map = $this->mapPost($entity);
            $data[$entityId]['autocomplete'] = $map;
        }

        return $data;
    }
}
