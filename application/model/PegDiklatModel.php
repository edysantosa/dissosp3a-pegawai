<?php namespace app\model;

class PegDiklatModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegDiklat';
    protected $primaryKey = 'pegDiklatId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegDiklatId' ];
}
