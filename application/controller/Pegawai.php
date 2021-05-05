<?php namespace app\controller;

use \Exception;
use \app\model\PegawaiModel;
use \app\helper\GetSetHelper;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

use \app\helper\LoggingHelper as Logger;
use Respect\Validation\Validator as v;

// use finfo;

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

        if (is_null($pegawai->tglLahir)) {
            $pegawai->tglLahirFormat = '';
        } else {
            $pegawai->tglLahirFormat = date("d-m-Y", strtotime($pegawai->tglLahir));
        }
        if (is_null($pegawai->cpnsTglBKN)) {
            $pegawai->cpnsTglBKNFormat = '';
        } else {
            $pegawai->cpnsTglBKNFormat = date("d-m-Y", strtotime($pegawai->cpnsTglBKN));
        }
        if (is_null($pegawai->cpnsTglSK)) {
            $pegawai->cpnsTglSKFormat = '';
        } else {
            $pegawai->cpnsTglSKFormat = date("d-m-Y", strtotime($pegawai->cpnsTglSK));
        }
        if (is_null($pegawai->cpnsTMT)) {
            $pegawai->cpnsTMTFormat = '';
        } else {
            $pegawai->cpnsTMTFormat = date("d-m-Y", strtotime($pegawai->cpnsTMT));
        }
        if (is_null($pegawai->pnsTglSK)) {
            $pegawai->pnsTglSKFormat = '';
        } else {
            $pegawai->pnsTglSKFormat = date("d-m-Y", strtotime($pegawai->pnsTglSK));
        }
        if (is_null($pegawai->pnsTMT)) {
            $pegawai->pnsTMTFormat = '';
        } else {
            $pegawai->pnsTMTFormat = date("d-m-Y", strtotime($pegawai->pnsTMT));
        }

        return $this->view
            ->addCss($this->url . '/assets/dist/css/pegawai-edit.css')
            ->addJs($this->url . '/assets/dist/js/pegawai-edit.js')

            ->render('pegawaiEdit.twig', [
                'pegawai'    => $pegawai,
                'agama'      => \app\model\JenisAgamaModel::all(),
                'provinsi'   => \app\model\JenisProvinsiModel::all(),
                'pangkat'    => \app\model\JenisPangkatGolonganModel::all(),
                'jenisKepeg' => \app\model\JenisKepegawaianModel::all(),
            ]);
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
                        Logger::add(
                            $this->session->user['userId'],
                            'Hapus data pegawai: ' . $pegawai->nama
                        );
                    }
                    $message = 'Pegawai dihapus';
                    break;

                case 'save':
                    $pegawaiId = $this->savePegawai();
                    return $this->response->withJson([
                        'message' => 'Pelanggan tersimpan',
                        'pegawaiId' => $pegawaiId
                    ]);
                    break;

                case 'update':
                    return $this->savePegawai($post->get('id'));
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

    public function savePegawai($pegawaiId = null)
    {
        try {
            // $this->database->getConnection()->getPdo()->beginTransaction();
            $post = new GetSetHelper($this->request->getParsedBody());
            
            if ($pegawaiId) {
                $pegawai = PegawaiModel::where('pegawaiId', $pegawaiId)->first();
                $pegawaiOld = clone $pegawai;
            } else {
                $pegawai = new PegawaiModel;
            }

            $pegawai->jenisKepegawaianId = $post->get('jenis-kepegawaian', '');
            $pegawai->nama = $post->get('nama', '');
            $pegawai->nip = $post->get('nip', '');
            $pegawai->tempatLahir = $post->get('tempat-lahir', '');
            $pegawai->tglLahir = $post->get('tglLahir', null);

            $pegawai->gelarDepan = $post->get('gelar-depan', null);
            $pegawai->gelarBelakang = $post->get('gelar-belakang', null);
            $pegawai->jk = $post->get('jk', null);
            $pegawai->jenisAgamaId = $post->get('agama', null);
            $pegawai->email = $post->get('email', null);
            $pegawai->golonganDarah = $post->get('golongan-darah', null);
            $pegawai->alamat = $post->get('alamat', null);
            $pegawai->kelurahan = $post->get('kelurahan', null);
            $pegawai->kecamatan = $post->get('kecamatan', null);
            $pegawai->jenisProvinsiId = $post->get('provinsi', null);
            $pegawai->kodePos = $post->get('kode-pos', null);
            $pegawai->statusPernikahan = $post->get('status-pernikahan', null);
            $pegawai->noBPJS = $post->get('no-bpjs', null);
            $pegawai->noTaspen = $post->get('no-taspen', null);
            $pegawai->noKaris = $post->get('no-karis', null);
            $pegawai->noKaris = $post->get('no-karis', null);
            $pegawai->noNPWP = $post->get('no-npwp', null);
            $pegawai->cpnsNoBKN = $post->get('cpns-no-bkn', null);
            $pegawai->cpnsTglBKN = $post->get('cpnsTglBKN', null);
            $pegawai->cpnsDitetapkanOleh = $post->get('cpns-ditetapkan-oleh', null);
            $pegawai->cpnsPangkatGolonganId = $post->get('cpns-pangkat-golongan', null);
            $pegawai->cpnsNoSK = $post->get('cpns-no-sk', null);
            $pegawai->cpnsTglSK = $post->get('cpnsTglSK', null);
            $pegawai->cpnsTMT = $post->get('cpnsTMT', null);
            $pegawai->pnsDitetapkanOleh = $post->get('pns-ditetapkan-oleh', null);
            $pegawai->pnsPangkatGolonganId = $post->get('pns-pangkat-golongan', null);
            $pegawai->pnsNoSK = $post->get('pns-no-sk', null);
            $pegawai->pnsTglSK = $post->get('pnsTglSK', null);
            $pegawai->pnsTMT = $post->get('pnsTMT', null);




            $pegawai->status = 1;
            $pegawai->save();



            /** UPLOAD GAMBAR DAN FILE **/
            $uploadedFiles = $this->request->getUploadedFiles();
            $imagePath = __DIR__ . '/../../public/assets/pegawai/photos/';
            $documentPath = __DIR__ . '/../../public/assets/pegawai/documents/';
            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0777, true);
            }
            if (!file_exists($documentPath)) {
                mkdir($documentPath, 0777, true);
            }

            // Foto Pegawai
            $uploadedFile = $uploadedFiles['pegawai-image'];
            if ($uploadedFile->getError() != UPLOAD_ERR_NO_FILE) {
                $uplSuccess = $uploadedFile->getError() === UPLOAD_ERR_OK;
                $uplValid = v::size(null, '2MB')->anyOf(v::mimetype('image/jpg'), v::mimetype('image/jpeg'), v::mimetype('image/png'))->validate($uploadedFile->file);
                if ($uplSuccess && $uplValid) {
                    $filename = $this->moveUploadedFile($imagePath, $uploadedFile);
                    $pegawai->foto = $filename;
                    $pegawai->save();                    
                } else {
                    $message = 'Gagal upload gambar, error pada aplikasi';
                    if (!$uplValid) {
                        $message = 'Format gambar harus berupa JPG atau PNG, ukuran maksimum 2MB';
                    }
                    throw new Exception($message);
                }
            }

            // Dokumen PDF
            $uplDocs['dokSKCPNS'] = $uploadedFiles['file-sk-cpns'];
            $uplDocs['dokSKPNS'] = $uploadedFiles['file-sk-pns'];

            foreach ($uplDocs as $key => $doc) {
                if ($doc->getError() != UPLOAD_ERR_NO_FILE) {
                    $uplSuccess = $doc->getError() === UPLOAD_ERR_OK;
                    $uplValid = v::size(null, '2MB')->mimetype('application/pdf')->validate($doc->file);
                    if ($uplSuccess && $uplValid) {
                        $filename = $this->moveUploadedFile($documentPath, $doc);
                        $pegawai->$key = $filename;
                        $pegawai->save();
                    } else {
                        $message = 'Gagal upload dokumen SK, error pada aplikasi';
                        if (!$uplValid) {
                            $message = 'File dokumen harus berupa PDF, ukuran maksimum 2MB';
                        }
                        throw new Exception($message);
                    }
                }
            }
            /** END UPLOAD GAMBAR DAN FILE **/


            // Log
            if ($pegawaiId) {
                Logger::add($this->session->user['userId'], 'Update data pegawai: ' . $pegawai->nama . ' -> ' .$post->get('nama'), json_encode($pegawaiOld), json_encode($pegawai));
            } else {
                Logger::add(
                    $this->session->user['userId'],
                    'Tambah data pegawai: ' . $post['nama']
                );
            }

            // $this->database->getConnection()->getPdo()->commit();

            return $this->response->withStatus(500)->withJson([
                'message' => 'Data pegawai tersimpan',
                'pegawaiId' => $pegawai->pegawaiId
            ]);
        } catch (Exception $err) {
            // $this->database->getConnection()->getPdo()->rollBack();
            return $this->response->withStatus(500)->withJson([
                'message' => $err->getMessage()
            ]);
        }
    }

    private function moveUploadedFile($directory, $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        // RESIZE GAMBAR
        // $gambar = ImageWorkshop::initFromPath($uploadedFile->file);
        // $gambar->cropMaximumInPercent(0, 0, 'MM');
        // $gambar->resizeInPixel(400, 400, true);
        // $gambar->save($directory, $filename, true, null, 100);

        return $filename;
    }


    public function test()
    {
        // var_dump(v::numericVal()->max(10)->validate(51));
        $x = 'dokSKCPNS';

        $pegawai = PegawaiModel::where('pegawaiId', 1)->first();
        // $pegawai->dokSKCPNS = 'asdasd';
        $pegawai->$x = 'asdasd';
        $pegawai->save();
    }
}
