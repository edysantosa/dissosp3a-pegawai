<?php namespace app\model;

class PegSeminarModel extends \sys\Model
{
    public $timestamps = false;

    protected $table = 'PegSeminar';
    protected $primaryKey = 'pegSeminarId';

    protected $hidden   = [];
    protected $guarded  = [ 'pegSeminarId' ];
}
