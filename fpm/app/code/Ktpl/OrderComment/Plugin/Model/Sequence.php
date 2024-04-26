<?php

namespace Ktpl\OrderComment\Plugin\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Class Sequence
 * @package Ktpl\OrderComment\Plugin\Model
 */
class Sequence
{
    private $connection;

    public function __construct(
        ResourceConnection $connection
    ) {
        $this->connection = $connection;
    }

    public function aroundGetNextValue(\Magento\SalesSequence\Model\Sequence $subject, callable $proceed)
    {
        return $this->getIncrementIds();
    }
    // this method ensure and check for random number in order table

    public function getIncrementIds()
    {
        $connection =  $this->connection->getConnection();
        $sql = "SELECT temp.rnd FROM sales_order c,(SELECT CONCAT(LEFT(MD5(CONCAT(RAND(),'_',UUID())),5),'_',RIGHT(MD5(CONCAT(UNIX_TIMESTAMP(),'_',RAND())),4)) AS rnd FROM sales_order LIMIT 0,10) AS temp WHERE c.increment_id NOT IN (temp.rnd) LIMIT 1";
        $result = $connection->fetchAll($sql);
        return $result[0]['rnd'];
    }
}