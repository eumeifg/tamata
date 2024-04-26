<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchMysql
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchMysql\Adapter;

use Magento\Framework\Search\RequestInterface;

/**
 * Class Mapper
 *
 * @package Ktpl\SearchMysql\Adapter
 */
class Mapper extends \Magento\Framework\Search\Adapter\Mysql\Mapper
{
    /**
     * Build query
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\DB\Select
     * @throws \Zend_Db_Exception
     */
    public function buildQuery(RequestInterface $request)
    {
        $select = parent::buildQuery($request);

        if (is_array($request->getFrom())) {
            $select->limit($request->getSize(), 0);
        }

        return $select;
    }
}
