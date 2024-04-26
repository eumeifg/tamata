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

namespace Ktpl\ElasticSearch\Api\Service;

/**
 * Interface ValidatorInterface
 *
 * @package Ktpl\ElasticSearch\Api\Service
 */
interface ValidatorInterface
{
    /**
     * Possible Validation Results
     */
    const SUCCESS = 1;
    const INFO = 2;
    const WARNING = 3;
    const FAILED = 4;

    const STATUS_CODE = 'status_code';
    const TEST_NAME = 'test_name';
    const MODULE_NAME = 'module_name';
    const MESSAGE = 'message';

    /**
     * Execute validator tests.
     *
     * @return string[]
     */
    public function validate();

    /**
     * Get validator module name.
     *
     * @return string
     */
    public function getModuleName();
}
