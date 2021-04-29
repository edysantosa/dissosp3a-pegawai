<?php namespace app\model;

class PegRiwayatJabatanModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegRiwayatJabatan';
    protected $primaryKey = 'pegRiwayatJabatanId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegRiwayatJabatanId' ];
}
