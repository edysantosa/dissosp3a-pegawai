<?php namespace app\model;

class PegawaiModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'Pegawai';
    protected $primaryKey = 'pegawaiId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegawaiId' ];


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
