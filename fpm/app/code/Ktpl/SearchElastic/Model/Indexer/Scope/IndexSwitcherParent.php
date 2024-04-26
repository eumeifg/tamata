<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchElastic
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchElastic\Model\Indexer\Scope;

use Ktpl\ElasticSearch\Service\CompatibilityService;

if (CompatibilityService::is22() || CompatibilityService::is23()) {
    require_once('IndexSwitcherParentImplements.php');
} else {
    require_once('IndexSwitcherParentExtends.php');
}

/**
 * Class IndexSwitcherParent
 *
 * @package Ktpl\SearchElastic\Model\Indexer\Scope
 */
class IndexSwitcherParent extends IndexSwitcherParentMediator
{

}