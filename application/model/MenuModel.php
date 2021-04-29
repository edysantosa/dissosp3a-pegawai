<?php namespace app\model;

class MenuModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'Menu';
    protected $primaryKey = 'menuId';

    protected $hidden   = [];
    protected $guarded  = [ 'menuId' ];


    public function parent()
    {

        return $this->hasOne('\app\model\MenuModel', 'menuId', 'parentId');
    }

    public function children()
    {
        return $this->hasMany('\app\model\MenuModel', 'parentId', 'menuId');
    }

    public static function tree()
    {

        // return static::with(implode('.', array_fill(0, 4, 'children')))->where('parentId', '=', 0)->get();
        return static::with('children')->where('parentId', '=', 0)
        ->get()->groupBy('groupName');
    }

    public function groupPermission()
    {
        return $this->hasOne('\app\model\GroupPermissionModel', 'menuId', 'menuId');
    }
}
