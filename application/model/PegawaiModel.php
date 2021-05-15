<?php namespace app\model;

class PegawaiModel extends \sys\Model
{
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $table = 'Pegawai';
    protected $primaryKey = 'pegawaiId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegawaiId' ];
    protected $appends = ['tglLahirFormat', 'cpnsTglBKNFormat', 'cpnsTglSKFormat', 'cpnsTMTFormat', 'pnsTglSKFormat', 'pnsTMTFormat', 'tglLahirAyahFormat', 'tglLahirIbuFormat'];

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
    public function getTglLahirAyahFormatAttribute()
    {
        if (is_null($this->tglLahirAyah)) {
            return '';
        } else {
            return date("d-m-Y", strtotime($this->tglLahirAyah));
        }
    }
    public function getTglLahirIbuFormatAttribute()
    {
        if (is_null($this->tglLahirIbu)) {
            return '';
        } else {
            return date("d-m-Y", strtotime($this->tglLahirIbu));
        }
    }


    public function statusKepeg()
    {
        return $this->hasOne('\app\model\JenisKepegawaianModel', 'jenisKepegawaianId', 'jenisKepegawaianId');
    }
    
    public function agama()
    {
        return $this->hasOne('\app\model\JenisAgamaModel', 'jenisAgamaId', 'jenisAgamaId');
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



    public function gajiBerkala()
    {
        return $this->hasMany('\app\model\PegGajiBerkalaModel', 'pegawaiId', 'pegawaiId');
    }

    public function bahasa()
    {
        return $this->hasMany('\app\model\PegBahasaModel', 'pegawaiId', 'pegawaiId');
    }

    public function pendidikan()
    {
        return $this->hasMany('\app\model\PegRiwayatPendidikanModel', 'pegawaiId', 'pegawaiId');
    }

    public function diklat()
    {
        return $this->hasMany('\app\model\PegDiklatModel', 'pegawaiId', 'pegawaiId');
    }

    public function penghargaan()
    {
        return $this->hasMany('\app\model\PegPenghargaanModel', 'pegawaiId', 'pegawaiId');
    }

    public function anak()
    {
        return $this->hasMany('\app\model\PegAnakModel', 'pegawaiId', 'pegawaiId');
    }
}
