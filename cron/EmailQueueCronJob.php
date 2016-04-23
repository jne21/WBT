<?php

class EmailQueueCronJob extends common\CronJobAbstract
{
    const TTL = 1200; // 20 minutes

    function execute()
    {
        EmailQueue::sendPacket();
    }

    function getTTL()
    {
        return self::TTL;
    }
}
