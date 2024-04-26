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

namespace Ktpl\ElasticSearch\Service;

use Ktpl\ElasticSearch\Api\Service\ValidatorInterface;

/**
 * Class AbstractValidator
 *
 * @package Ktpl\ElasticSearch\Service
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * Keyword used for validation methods.
     */
    const TEST_METHOD_KEY = 'test';
    /**
     * @var array
     */
    private $results = [];
    /**
     * @var string
     */
    private $currentTest = null;

    /**
     * Executes every method beginning with the 'test' keyword.
     *
     * {@inheritdoc}
     */
    public function validate()
    {
        foreach (get_class_methods($this) as $method) {
            $this->currentTest = ucfirst(preg_replace('/\B([A-Z])/', ' $1', $method));

            if (substr($method, 0, strlen(self::TEST_METHOD_KEY)) === self::TEST_METHOD_KEY) {
                try {
                    $nResult = count($this->results);
                    $result = call_user_func([$this, $method]);

                    if (is_array($result) && count($result) == 3) {
                        // returned array (old way)
                        if (!is_array($result[2])) {
                            $result[2] = [$result[2]];
                        }
                        if (count($result[2]) === 0) {
                            $result[2][] = '';
                        }

                        foreach ($result[2] as $message) {
                            $this->results[] = [
                                self::STATUS_CODE => $result[0],
                                self::MODULE_NAME => $this->getModuleName(),
                                self::TEST_NAME => $this->currentTest,
                                self::MESSAGE => $message,
                            ];
                        }
                    } elseif ($nResult === count($this->results)) {
                        //empty result + nothing were added. Add success.
                        $this->addSuccess('');
                    }
                } catch (\Exception $e) {
                    $this->addError($e->getMessage());
                }
            }
        }

        return $this->results;
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function getModuleName()
    {
        $classArray = explode('\\', get_class($this));

        return $classArray[0] . '_' . $classArray[1];
    }

    /**
     * Add success message
     *
     * @param string $message
     * @param array $args
     * @return $this
     */
    public function addSuccess($message, array $args = [])
    {
        $this->results[] = [
            self::STATUS_CODE => self::SUCCESS,
            self::MODULE_NAME => $this->getModuleName(),
            self::TEST_NAME => $this->currentTest,
            self::MESSAGE => $this->formatMessage($message, $args),
        ];

        return $this;
    }

    /**
     * Format message
     *
     * @param string $message
     * @param array $args
     * @return string
     */
    private function formatMessage($message, array $args)
    {
        $search = [];
        $replace = [];
        foreach ($args as $index => $value) {
            $search[] = '{' . $index . '}';
            $replace[] = $value;
        }

        return str_replace($search, $replace, $message);
    }

    /**
     * Add error message
     *
     * @param string $message
     * @param array $args
     * @return $this
     */
    public function addError($message, array $args = [])
    {
        $this->results[] = [
            self::STATUS_CODE => self::FAILED,
            self::MODULE_NAME => $this->getModuleName(),
            self::TEST_NAME => $this->currentTest,
            self::MESSAGE => $this->formatMessage($message, $args),
        ];

        return $this;
    }

    /**
     * Add warning message
     *
     * @param string $message
     * @param array $args
     * @return $this
     */
    public function addWarning($message, array $args = [])
    {
        $this->results[] = [
            self::STATUS_CODE => self::WARNING,
            self::MODULE_NAME => $this->getModuleName(),
            self::TEST_NAME => $this->currentTest,
            self::MESSAGE => $this->formatMessage($message, $args),
        ];

        return $this;
    }

    /**
     * Add info message
     *
     * @param string $message
     * @param array $args
     * @return $this
     */
    public function addInfo($message, array $args = [])
    {
        $this->results[] = [
            self::STATUS_CODE => self::INFO,
            self::MODULE_NAME => $this->getModuleName(),
            self::TEST_NAME => $this->currentTest,
            self::MESSAGE => $this->formatMessage($message, $args),
        ];

        return $this;
    }
}
