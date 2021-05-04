<?php namespace app\model;

class JenisAgamaModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'JenisAgama';
    protected $primaryKey = 'jenisAgamaId';

    protected $hidden   = [];
    protected $guarded  = [ 'jenisAgamaId' ];
}
