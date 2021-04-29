<?php namespace app\model;

class PegBahasaModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegBahasa';
    protected $primaryKey = 'pegBahasaId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegBahasaId' ];


    public function jenisBahasa()
    {
        return $this->hasOne('\app\model\JenisBahasaModel', 'jenisBahasaId', 'jenisBahasaId');
    }
}
