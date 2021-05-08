<?php namespace app\model;

class PegGajiBerkalaModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegGajiBerkala';
    protected $primaryKey = 'pegGajiBerkalaId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegGajiBerkalaId' ];
    protected $appends = ['tglSKFormat', 'tglMulaiFormat'];

    public function getTglSKFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tglSK));
    }
    public function getTglMulaiFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tglMulai));
    }
}
