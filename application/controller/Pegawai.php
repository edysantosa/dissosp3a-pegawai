<?php namespace app\controller;

use \Exception;
use \app\model\PegawaiModel;
use \app\helper\GetSetHelper;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

use \app\helper\LoggingHelper as Logger;

class Pegawai extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->view
            ->addCss($this->url . '/assets/dist/css/pegawai.css')
            ->addJs($this->url . '/assets/dist/js/pegawai.js')

            ->render('pegawai.twig');
    }

    public function add()
    {
        return $this->view
            ->addCss($this->url . '/assets/dist/css/pegawai-edit.css')
            ->addJs($this->url . '/assets/dist/js/pegawai-edit.js')

            ->render('pegawaiEdit.twig');
    }

    public function edit($id)
    {
        $post = $this->request->getParsedBody();
        $pegawai = PegawaiModel::where('pegawaiId', $id)->first();

        if (!$pegawai) {
            throw new \Slim\Exception\NotFoundException($this->request, $this->response);
        }

        return $this->view
            ->addCss($this->url . '/assets/dist/css/pegawai-edit.css')
            ->addJs($this->url . '/assets/dist/js/pegawai-edit.js')

            ->render('pegawaiEdit.twig', ['pegawai'   => $pegawai ]);
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

        $q = PegawaiModel::with(['statusKepeg', 'pangkatTerakhir', 'pangkatTerakhir.pangkat', 'jabatanTerakhir'])->where('status', '<>', 0);
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
                    $q->orWhere('Pegawai.nip', 'like', '%'.$search.'%');
                    $q->orWhere('Pegawai.nama', 'like', '%'.$search.'%');
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

    public function submit()
    {
        try {
            if (strtolower($this->request->getMethod()) != "post") {
                throw new Exception('Request method invalid');
            }

            $post = new GetSetHelper($this->request->getParsedBody());

            $task = $post->get('task', '');
            $ids = $post->get('ids', []);

            switch ($task) {
                case 'delete':
                    foreach ($ids as $id) {
                        $pegawai = PegawaiModel::where('pegawaiId', $id)->first();
                        $pegawai->status = 0;
                        $pegawai->save();

                        // Log
                        Logger::add(2,
                            $this->session->user['userId'],
                            'Hapus data pegawai: ' . $pegawai->nama
                        );
                    }
                    $message = 'Pegawai dihapus';
                    break;

                case 'save':
                    $pegawai = new PegawaiModel;
                    $pegawai->nama = $post->get('nama', '');
                    $pegawai->nip = $post->get('nip', '');
                    $pegawai->tempatLahir = $post->get('tempat-lahir', '');
                    $pegawai->status = 1;
                    $pegawai->save();
                    $message = 'Data pegawai tersimpan';

                    // Log
                    Logger::add(2,
                        $this->session->user['userId'],
                        'Tambah data pegawai: ' . $post['nama']
                    );
                    break;

                case 'update':
                    $pegawai = PegawaiModel::where('pegawaiId', $post->get('id'))->first();
                    // Log
                    Logger::add(2,
                        $this->session->user['userId'],
                        'Update data pegawai: ' . $pegawai->nama . ' -> ' .$post->get('nama')
                    );
                    $pegawai->nama = $post->get('nama', '');
                    $pegawai->nip = $post->get('nip', '');
                    $pegawai->tempatLahir = $post->get('tempat-lahir', '');
                    $pegawai->save();
                    $message = 'Data pegawai terupdate';
                    break;
                
                default:
                    throw new Exception('Invalid request');
                    break;
            }

            return $this->response->withJson([
                'message' => $message
            ]);
        } catch (Exception $err) {
            return $this->response->withStatus(500)->withJson([
                'message' => $err->getMessage()
            ]);
        }
    }
}
