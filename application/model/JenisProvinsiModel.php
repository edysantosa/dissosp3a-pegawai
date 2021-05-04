<?php namespace app\model;

class JenisProvinsiModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'JenisProvinsi';
    protected $primaryKey = 'jenisProvinsiId';

    protected $hidden   = [];
    protected $guarded  = [ 'jenisProvinsiId' ];
}
