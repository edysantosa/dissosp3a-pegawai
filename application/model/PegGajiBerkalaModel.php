<?php namespace app\model;

class PegGajiBerkalaModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegGajiBerkala';
    protected $primaryKey = 'pegGajiBerkalaId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegGajiBerkalaId' ];
}
