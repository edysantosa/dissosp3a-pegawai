<?php namespace app\model;

class JenisBahasaModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'JenisBahasa';
    protected $primaryKey = 'jenisBahasaId';

    protected $hidden   = [];
    protected $guarded  = [ 'jenisBahasaId' ];
}
