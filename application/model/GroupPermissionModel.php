<?php namespace app\model;

class GroupPermissionModel extends \sys\Model
{
    public $timestamps = false;
    public static $snakeAttributes = false;
    
    protected $table = 'GroupPermission';
    protected $primaryKey = 'groupPermissionId';
    protected $guarded  = [ 'groupPermissionId' ];
}
