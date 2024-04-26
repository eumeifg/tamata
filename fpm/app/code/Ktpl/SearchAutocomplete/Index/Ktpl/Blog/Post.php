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

namespace Ktpl\SearchAutocomplete\Index\Ktpl\Blog;

use Ktpl\SearchAutocomplete\Index\AbstractIndex;

/**
 * Class Post
 *
 * @package Ktpl\SearchAutocomplete\Index\Ktpl\Blog
 */
class Post extends AbstractIndex
{
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

        /** @var \Ktpl\Blog\Model\Post $post */
        foreach ($this->getCollection() as $post) {
            $items[] = [
                'name' => $post->getName(),
                'url' => $post->getUrl(),
            ];
        }

        return $items;
    }
}
