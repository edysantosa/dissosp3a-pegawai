<?php namespace app\model;

class PegRiwayatPangkatModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegRiwayatPangkat';
    protected $primaryKey = 'pegRiwayatPangkatId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegRiwayatPangkatId' ];

    public function pangkat()
    {
        return $this->hasOne('\app\model\JenisPangkatGolonganModel', 'jenisPangkatGolonganId', 'jenisPangkatGolonganId');
    }
}
