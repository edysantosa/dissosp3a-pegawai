<?php namespace app\model;

class PegPenataranModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegDiklat';
    protected $primaryKey = 'pegDiklatId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegDiklatId' ];
}
