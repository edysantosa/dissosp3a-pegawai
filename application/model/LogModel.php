<?php namespace app\model;

class LogModel extends \sys\Model
{
    public $timestamps = false;
    protected $table = 'Log';

    public function user()
    {
        return $this->hasOne('\app\model\UserModel', 'userId', 'userId');
    }

    public function getElapsedTimeAttribute()
    {
        return \Carbon\Carbon::parse($this->activityDate)->diffForHumans();
    }
}
