<?php

namespace App\Service;

use App\Document\UserActionLog;
use Doctrine\ODM\MongoDB\DocumentManager;

class LogMongodb
{
    public function logUserAction(DocumentManager $dm, string $username, string $action, bool $success)
    {
        $log = new UserActionLog($username, $action, $success);
        $dm->persist($log);
        $dm->flush();
    }
}