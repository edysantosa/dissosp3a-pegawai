<?php namespace app\model;

class UserModel extends \sys\Model
{
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $table = 'User';
    protected $primaryKey = 'userId';
    protected $hidden = [ 'password' ];
    protected $guarded  = [ 'userId' ];

    public function group()
    {
        return $this->hasOne('\app\model\GroupModel', 'groupId', 'groupId');
    }
}
