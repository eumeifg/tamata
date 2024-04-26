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

namespace Ktpl\SearchAutocomplete\Index\Magefan\Blog;

use Ktpl\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

/**
 * Class Post
 *
 * @package Ktpl\SearchAutocomplete\Index\Magefan\Blog
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

        /** @var \Magefan\Blog\Model\Post $post */
        foreach ($this->getCollection() as $post) {
            $items[] = [
                'name' => $post->getTitle(),
                'url' => $post->getCanonicalUrl(),
            ];
        }

        return $items;
    }
}
