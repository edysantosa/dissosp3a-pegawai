<?php namespace app\model;

class PegSatyalancanaModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegSatyalancana';
    protected $primaryKey = 'pegSatyalancanaId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegSatyalancanaId' ];
}
