<?php namespace app\model;

class PegRiwayatJabatanModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegRiwayatJabatan';
    protected $primaryKey = 'pegRiwayatJabatanId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegRiwayatJabatanId' ];
    protected $appends = ['tmtJabatanFormat', 'tmtEselonFormat', 'tglSKJabatanFormat'];

    public function getTmtJabatanFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tmtJabatan));
    }
    public function getTmtEselonFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tmtEselon));
    }
    public function getTglSKJabatanFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tglSKJabatan));
    }
}
