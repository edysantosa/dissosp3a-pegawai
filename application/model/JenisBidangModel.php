<?php namespace app\model;

class JenisBidangModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'JenisBidang';
    protected $primaryKey = 'jenisBidangId';

    protected $hidden   = [];
    protected $guarded  = [ 'jenisBidangId' ];
}
