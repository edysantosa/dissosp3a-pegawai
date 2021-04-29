<?php namespace app\model;

class JenisPendidikanModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'JenisPendidikan';
    protected $primaryKey = 'jenisPendidikanId';

    protected $hidden   = [];
    protected $guarded  = [ 'jenisPendidikanId' ];
}
