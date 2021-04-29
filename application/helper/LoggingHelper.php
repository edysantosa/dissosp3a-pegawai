<?php namespace app\helper;

use \Exception;
use \Carbon\Carbon;
use \app\helper\GetSetHelper;
use \app\model\LogModel;

class LoggingHelper
{
    public static function add($userId, $activity, $from = null, $to = null)
    {
        $log             = new LogModel;
        $log->userId     = $userId;
        $log->activityDate      = Carbon::now();
        $log->activity   = $activity;

        if ($from) {
            $log->from = $from ;
        }
        if ($to) {
            $log->to   = $to ;
        }

        $log->save();
    }
}
