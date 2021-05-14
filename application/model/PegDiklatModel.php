<?php namespace app\model;

class PegDiklatModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegDiklat';
    protected $primaryKey = 'pegDiklatId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegDiklatId' ];
    protected $appends  = ['tglMulaiFormat', 'tglSelesaiFormat', 'tglSTTPFormat'];

    public function getTglMulaiFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tglMulai));
    }
    public function getTglSelesaiFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tglSelesai));
    }
    public function getTglSTTPFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tglSTTP));
    }
}
