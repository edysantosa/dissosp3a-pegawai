<?php namespace app\model;

class PegRiwayatPendidikanModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegRiwayatPendidikan';
    protected $primaryKey = 'pegRiwayatPendidikanId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegRiwayatPendidikanId' ];
    protected $appends  = ['tglIjasahFormat'];

    public function getTglIjasahFormatattribute()
    {
        return date("d-m-Y", strtotime($this->tglIjasah));
    }

    public function jenisPendidikan()
    {
        return $this->hasOne('\app\model\JenisPendidikanModel', 'jenisPendidikanId', 'jenisPendidikanId');
    }
}
