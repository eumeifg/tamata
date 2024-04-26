<?php

namespace CAT\ProductFeed\Cron;

use CAT\ProductFeed\Model\FbFeedGenerate;
/**
 * FbFeed class
 */
class FbFeed {

    /**
     * $fbFeedGenerate variable
     *
     * @var FbFeedGenerate
     */
    protected $fbFeedGenerate;

    /**
     * __construct function
     *
     * @param FbFeedGenerate $fbFeedGenerate
     */
    public function __construct(
        FbFeedGenerate $fbFeedGenerate
    ) {
        $this->fbFeedGenerate = $fbFeedGenerate;
    }

    public function execute()
    {
        $logger = $this->fbFeedGenerate->getLogger();
        try {
            $startTime = microtime(true);
            $this->fbFeedGenerate->generate(0);
            $endTime = microtime(true);
            $timeTaken = ($endTime - $startTime);
            $logger->info('Feed Total Time : '. round($timeTaken/60, 2)."mins");
        } catch (\Exception $e) {
            $logger->info('Error : '.$e->getMessage());
        }
    }
}