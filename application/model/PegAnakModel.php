<?php namespace app\model;

class PegAnakModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegAnak';
    protected $primaryKey = 'pegAnakId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegAnakId' ];
    protected $appends = ['tglLahirFormat'];

    public function getTglLahirFormatAttribute()
    {
        return date("d-m-Y", strtotime($this->tglLahir));
    }
}
