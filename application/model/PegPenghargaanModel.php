<?php namespace app\model;

class PegPenghargaanModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegPenghargaan';
    protected $primaryKey = 'pegPenghargaanId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegPenghargaanId' ];
    protected $appends  = ['tglPiagamFormat'];

    public function getTglPiagamFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tglPiagam));
    }
}
