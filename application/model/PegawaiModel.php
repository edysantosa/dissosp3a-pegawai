<?php namespace app\model;

class PegawaiModel extends \sys\Model
{
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $table = 'Pegawai';
    protected $primaryKey = 'pegawaiId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegawaiId' ];
    protected $appends = ['tglLahirFormat', 'cpnsTglBKNFormat', 'cpnsTglSKFormat', 'cpnsTMTFormat', 'pnsTglSKFormat', 'pnsTMTFormat'];

    public function getTglLahirFormatAttribute()
    {
        if (is_null($this->tglLahir)) {
            return '';
        } else {
            return date("d-m-Y", strtotime($this->tglLahir));
        }
    }
    public function getcpnsTglBKNFormatAttribute()
    {
        if (is_null($this->cpnsTglBKN)) {
            return '';
        } else {
            return date("d-m-Y", strtotime($this->cpnsTglBKN));
        }
    }
    public function getCpnsTglSKFormatAttribute()
    {
        if (is_null($this->cpnsTglSK)) {
            return '';
        } else {
            return date("d-m-Y", strtotime($this->cpnsTglSK));
        }
    }
    public function getCpnsTMTFormatAttribute()
    {
        if (is_null($this->cpnsTMT)) {
            return '';
        } else {
            return date("d-m-Y", strtotime($this->cpnsTMT));
        }
    }
    public function getPnsTglSKFormatAttribute()
    {
        if (is_null($this->pnsTglSK)) {
            return '';
        } else {
            return date("d-m-Y", strtotime($this->pnsTglSK));
        }
    }
    public function getPnsTMTFormatAttribute()
    {
        if (is_null($this->pnsTMT)) {
            return '';
        } else {
            return date("d-m-Y", strtotime($this->pnsTMT));
        }
    }


    public function statusKepeg()
    {
        return $this->hasOne('\app\model\JenisKepegawaianModel', 'jenisKepegawaianId', 'jenisKepegawaianId');
    }

    public function pangkat()
    {
        return $this->hasMany('\app\model\PegRiwayatPangkatModel', 'pegawaiId', 'pegawaiId');
    }
    public function pangkatTerakhir()
    {
        return $this->hasOne('\app\model\PegRiwayatPangkatModel', 'pegawaiId', 'pegawaiId')->latest('tglSKPangkat');
    }


    public function jabatan()
    {
        return $this->hasMany('\app\model\PegRiwayatJabatanModel', 'pegawaiId', 'pegawaiId');
    }
    public function jabatanTerakhir()
    {
        return $this->hasOne('\app\model\PegRiwayatJabatanModel', 'pegawaiId', 'pegawaiId')->latest('tglSKJabatan');
    }

    public function bahasa()
    {
        return $this->hasMany('\app\model\PegBahasaModel', 'pegawaiId', 'pegawaiId');
    }

    public function diklat()
    {
        return $this->hasMany('\app\model\PegDiklatModel', 'pegawaiId', 'pegawaiId');
    }

    public function gajiBerkala()
    {
        return $this->hasMany('\app\model\PegGajiBerkalaModel', 'pegawaiId', 'pegawaiId');
    }

    public function penataran()
    {
        return $this->hasMany('\app\model\PegPenataranModel', 'pegawaiId', 'pegawaiId');
    }

    public function riwayatJabatan()
    {
        return $this->hasMany('\app\model\PegRiwayatJabatanModel', 'pegawaiId', 'pegawaiId');
    }

    public function riwayatPendidikan()
    {
        return $this->hasMany('\app\model\PegRiwayatPendidikanModel', 'pegawaiId', 'pegawaiId');
    }

    public function satyalancana()
    {
        return $this->hasMany('\app\model\PegSatyalancanaModel', 'pegawaiId', 'pegawaiId');
    }

    public function seminar()
    {
        return $this->hasMany('\app\model\PegSeminarModel', 'pegawaiId', 'pegawaiId');
    }
}
