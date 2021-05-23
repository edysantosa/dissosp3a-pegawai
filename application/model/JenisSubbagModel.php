<?php namespace app\model;

class JenisSubbagModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'JenisSubbag';
    protected $primaryKey = 'jenisSubbagId';

    protected $hidden   = [];
    protected $guarded  = [ 'jenisSubbagId' ];
}
