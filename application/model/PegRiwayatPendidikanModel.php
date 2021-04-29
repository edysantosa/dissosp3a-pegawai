<?php namespace app\model;

class PegRiwayatPendidikanModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegRiwayatPendidikan';
    protected $primaryKey = 'pegRiwayatPendidikanId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegRiwayatPendidikanId' ];

    public function jenisPendidikan()
    {
        return $this->hasOne('\app\model\JenisPendidikanModel', 'jenisPendidikanId', 'jenisPendidikanId');
    }
}
