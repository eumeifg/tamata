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

namespace Ktpl\SearchElastic\Controller\Adminhtml\Command;

use Ktpl\SearchElastic\Controller\Adminhtml\Command;

/**
 * Class Reset
 *
 * @package Ktpl\SearchElastic\Controller\Adminhtml\Command
 */
class Reset extends Command
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $success = true;
        $note = '';
        $message = '';

        if (class_exists('Elasticsearch\ClientBuilder')) {
            try {
                $this->engine->reset($note);
                $message .= __('Done.');
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $success = false;
            }
        } else {
            $success = false;
            $message = __('Elasticsearch library is not installed, please run the following :');
            $note = __('composer require elasticsearch/elasticsearch:~5.1');
        }

        $jsonData = json_encode([
            'message' => nl2br($message),
            'note' => $note,
            'success' => $success,
        ]);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Ktpl_SearchElastic::command_reset');
    }
}
