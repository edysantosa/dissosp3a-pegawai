<?php namespace app\model;

class JenisPangkatGolonganModel extends \sys\Model
{
    public $timestamps = false;
    
    protected $table = 'JenisPangkatGolongan';
    protected $primaryKey = 'jenisPangkatGolonganId';

    protected $hidden   = [];
    protected $guarded  = [ 'jenisPangkatGolonganId' ];
}
