<?php namespace app\model;

class JenisKepegawaianModel extends \sys\Model
{
    public $timestamps = false;
    
    protected $table = 'JenisKepegawaian';
    protected $primaryKey = 'jenisKepegawaianId';

    protected $hidden   = [];
    protected $guarded  = [ 'jenisKepegawaianId' ];
}
