<?php namespace app\model;

class GroupModel extends \sys\Model
{
    public $timestamps = false;
    public static $snakeAttributes = false;
    
    protected $table = 'Group';
    protected $primaryKey = 'groupId';
    protected $guarded  = [ 'groupId' ];
}
