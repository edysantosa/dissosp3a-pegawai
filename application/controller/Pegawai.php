<?php namespace app\controller;

use \Exception;
use \app\model\PegawaiModel;
use \app\helper\GetSetHelper;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class Pegawai extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->view
            ->addCss($this->url . '/assets/dist/css/driver.css')
            ->addJs($this->url . '/assets/dist/js/driver.js')

            ->render('driver.twig');
    }

    public function loadData()
    {
        $get = new GetSetHelper($this->request->getQueryParams());
        $url = $this->request->getUri()->getBaseUrl();

        $start  = $get->get('start', null);
        $length = $get->get('length', null);
        $sort   = $get->get('order', null);
        $search = $get->get('search', null);
        $result = ['status' => true, 'draw' => $get->get('draw', null), 'data' => []];

        $q = DriverModel::where('status', '<>', 0);
        $result['recordsTotal'] = $q->count();

        if ($sort) {
            $sort = explode("-", $sort);
            $field = $sort[0];
            $sortType = $sort[1];
            $q->orderBy($field, $sortType);
        }

        if ($search['value']) {
            $searches = explode(',', $search['value']);
            $q->where(function ($q) use ($searches) {
                foreach ($searches as $search) {
                    $search = trim($search);
                    $q->orWhere('Driver.name', 'like', '%'.$search.'%');
                    $q->orWhere('Driver.phone', 'like', '%'.$search.'%');
                }
            });
        }
        $result['recordsFiltered'] = $q->count();

        $tableData = $q->take($length)->skip($start)->get();
        $result['data'] = $tableData;
        foreach ($tableData as $tData) {
            $tData->sequence = ++$start;
        }
        
        return $this->response->withJson($result);
    }
}
