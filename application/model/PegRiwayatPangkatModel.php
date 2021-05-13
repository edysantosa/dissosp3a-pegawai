<?php namespace app\model;

class PegRiwayatPangkatModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegRiwayatPangkat';
    protected $primaryKey = 'pegRiwayatPangkatId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegRiwayatPangkatId' ];
    protected $appends  = ['tmtPangkatFormat', 'tglSKPangkatFormat'];

    public function getTmtPangkatFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tmtPangkat));
    }
    public function getTglSKPangkatFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tglSKPangkat));
    }

    public function pangkat()
    {
        return $this->hasOne('\app\model\JenisPangkatGolonganModel', 'jenisPangkatGolonganId', 'jenisPangkatGolonganId');
    }
}
