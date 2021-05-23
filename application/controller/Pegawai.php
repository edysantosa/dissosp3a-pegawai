<?php namespace app\controller;

use \Exception;
use \app\model\PegawaiModel;
use \app\model\PegGajiBerkalaModel;
use \app\model\PegRiwayatJabatanModel;
use \app\model\PegRiwayatPangkatModel;
use \app\model\PegRiwayatPendidikanModel;
use \app\model\PegDiklatModel;
use \app\model\PegPenghargaanModel;
use \app\model\PegBahasaModel;
use \app\model\PegAnakModel;
use \app\helper\GetSetHelper;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

use \app\helper\LoggingHelper as Logger;
use Respect\Validation\Validator as v;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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

            ->render('pegawai.twig', [
                'agama'           => \app\model\JenisAgamaModel::all(),
                'provinsi'        => \app\model\JenisProvinsiModel::all(),
                'pangkat'         => \app\model\JenisPangkatGolonganModel::all(),
                'jenisKepeg'      => \app\model\JenisKepegawaianModel::all(),
                'jenisBahasa'     => \app\model\JenisBahasaModel::all(),
                'jenisPendidikan' => \app\model\JenisPendidikanModel::all(),
                'jenisBidang'     => \app\model\JenisBidangModel::all(),
                'jenisSubbag'     => \app\model\JenisSubbagModel::all(),
            ]);
    }

    public function add()
    {
        return $this->view
            ->addCss($this->url . '/assets/dist/css/pegawai-edit.css')
            ->addJs($this->url . '/assets/dist/js/pegawai-edit.js')

            ->render('pegawaiEdit.twig', [
                'agama'      => \app\model\JenisAgamaModel::all(),
                'provinsi'   => \app\model\JenisProvinsiModel::all(),
                'pangkat'    => \app\model\JenisPangkatGolonganModel::all(),
                'jenisKepeg' => \app\model\JenisKepegawaianModel::all(),
                'jenisBahasa' => \app\model\JenisBahasaModel::all(),
                'jenisPendidikan' => \app\model\JenisPendidikanModel::all(),
                'jenisBidang'     => \app\model\JenisBidangModel::all(),
                'jenisSubbag'     => \app\model\JenisSubbagModel::all(),
            ]);
    }

    public function edit($id)
    {
        $post = $this->request->getParsedBody();
        $pegawai = PegawaiModel::with(['gajiBerkala', 'pangkat'])->where('pegawaiId', $id)->first();
        // echo '<pre>';
        // var_dump($pegawai->toArray());
        // die();
        if (!$pegawai) {
            throw new \Slim\Exception\NotFoundException($this->request, $this->response);
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
                'jenisBahasa' => \app\model\JenisBahasaModel::all(),
                'jenisPendidikan' => \app\model\JenisPendidikanModel::all(),
                'jenisBidang'     => \app\model\JenisBidangModel::all(),
                'jenisSubbag'     => \app\model\JenisSubbagModel::all(),
            ]);
    }

    public function view($id)
    {
        $post = $this->request->getParsedBody();
        $pegawai = PegawaiModel::with(['statusKepeg', 'agama'])->where('pegawaiId', $id)->first();
        // echo '<pre>';
        // var_dump($pegawai->toArray());
        // die();
        if (!$pegawai) {
            throw new \Slim\Exception\NotFoundException($this->request, $this->response);
        }

        return $this->view
            ->addCss($this->url . '/assets/dist/css/pegawai-view.css')
            ->addJs($this->url . '/assets/dist/js/pegawai-view.js')
            ->render('pegawaiView.twig', [
                'pegawai'    => $pegawai,
                'agama'      => \app\model\JenisAgamaModel::all(),
                'provinsi'   => \app\model\JenisProvinsiModel::all(),
                'pangkat'    => \app\model\JenisPangkatGolonganModel::all(),
                'jenisKepeg' => \app\model\JenisKepegawaianModel::all(),
                'jenisBahasa' => \app\model\JenisBahasaModel::all(),
                'jenisPendidikan' => \app\model\JenisPendidikanModel::all(),
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

        // Filters
        if ($get->get('status-kepeg', null)) {
            $q->where('Pegawai.jenisKepegawaianId', $get->get('status-kepeg'));
        }
        if ($get->get('agama', null)) {
            $q->whereIn('Pegawai.jenisAgamaId', $get->get('agama'));
        }
        if ($get->get('jk', null)) {
            $q->where('Pegawai.jk', $get->get('jk'));
        }
        if ($get->get('status-pernikahan', null)) {
            $q->whereIn('Pegawai.statusPernikahan', $get->get('status-pernikahan'));
        }
        if ($get->get('bidang', null)) {
            $q->whereIn('Pegawai.jenisBidangId', $get->get('bidang'));
        }
        if ($get->get('subbag', null)) {
            $q->whereIn('Pegawai.jenisSubbagId', $get->get('subbag'));
        }
        if ($get->get('tgl-lahir', null)) {
            $dateFrom = $get->get('tglLahirFrom');
            $dateTo = $get->get('tglLahirTo');
            $q->where(function ($q) use ($dateFrom, $dateTo) {
                $q->whereDate('tglLahir', '>=', $dateFrom);
                $q->whereDate('tglLahir', '<=', $dateTo);
            });
        }

        if ($get->get('pendidikan', null)) {
            $pendidikan = $get->get('pendidikan');
            // $q->whereHas('pendidikanTerakhir', function ($query) use ($pendidikan) {
            //     $query->where('jenisPendidikanId', $pendidikan);
            // });
            $q->whereHas('pendidikan', function (EloquentBuilder $query) use ($pendidikan) {
                $query
                ->whereIn('jenisPendidikanId', $pendidikan)
                ->whereIn('tglIjasah', function (QueryBuilder $query) {
                    $query
                        ->selectRaw('max(tglIjasah)')
                        ->from('PegRiwayatPendidikan')
                        ->whereColumn('pegawaiId', 'Pegawai.pegawaiId');
                });
            });
        }
        if ($get->get('pangkat', null)) {
            $pangkat = $get->get('pangkat');
            $q->whereHas('pangkat', function (EloquentBuilder $query) use ($pangkat) {
                $query
                ->whereIn('jenisPangkatGolonganId', $pangkat)
                ->whereIn('tglSKPangkat', function (QueryBuilder $query) {
                    $query
                        ->selectRaw('max(tglSKPangkat)')
                        ->from('PegRiwayatPangkat')
                        ->whereColumn('pegawaiId', 'Pegawai.pegawaiId');
                });
            });
        }
        if ($get->get('jenis-jabatan', null)) {
            $jenisjbt = $get->get('jenis-jabatan');
            $q->whereHas('jabatan', function (EloquentBuilder $query) use ($jenisjbt) {
                $query
                ->whereIn('jenisJabatan', $jenisjbt)
                ->whereIn('tglSKJabatan', function (QueryBuilder $query) {
                    $query
                        ->selectRaw('max(tglSKJabatan)')
                        ->from('PegRiwayatJabatan')
                        ->whereColumn('pegawaiId', 'Pegawai.pegawaiId');
                });
            });
        }
        if ($get->get('eselon', null)) {
            $eselon = $get->get('eselon');
            $q->whereHas('jabatan', function (EloquentBuilder $query) use ($eselon) {
                $query
                ->whereIn('eselon', $eselon)
                ->whereIn('tglSKJabatan', function (QueryBuilder $query) {
                    $query
                        ->selectRaw('max(tglSKJabatan)')
                        ->from('PegRiwayatJabatan')
                        ->whereColumn('pegawaiId', 'Pegawai.pegawaiId');
                });
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
        // $post = new GetSetHelper($this->request->getParsedBody());
        // return $this->response->withStatus(500)->withJson([
        //     'pnsTglSK' => $post->get('pnsTglSK', null)
        // ]);
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

            $pegawai->jenisBidangId = $post->get('bidang', null);
            $pegawai->jenisSubbagId = null;
            if ($pegawai->jenisBidangId == 1) {
                $pegawai->jenisSubbagId = $post->get('subbag', null);
            }


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
            $pegawai->cpnsTglBKN = $post->get('cpnsTglBKN', null) ?: null;
            $pegawai->cpnsDitetapkanOleh = $post->get('cpns-ditetapkan-oleh', null);
            $pegawai->cpnsPangkatGolonganId = $post->get('cpns-pangkat-golongan', null);
            $pegawai->cpnsNoSK = $post->get('cpns-no-sk', null);
            $pegawai->cpnsTglSK = $post->get('cpnsTglSK', null) ?: null;
            $pegawai->cpnsTMT = $post->get('cpnsTMT', null);
            $pegawai->pnsDitetapkanOleh = $post->get('pns-ditetapkan-oleh', null);
            $pegawai->pnsPangkatGolonganId = $post->get('pns-pangkat-golongan', null);
            $pegawai->pnsNoSK = $post->get('pns-no-sk', null);
            $pegawai->pnsTglSK = $post->get('pnsTglSK', null) ?: null;
            $pegawai->pnsTMT = $post->get('pnsTMT', null);
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


            /**
             * Riwayat Gaji Berkala
             */
            $gbkId       = $post->get('gbk-id', []);
            $gbkDelete   = $post->get('gbk-delete', []);
            $gbkNoSK     = $post->get('gbk-no-sk', []);
            $gbkTglSK    = $post->get('gbkTglSK', []);
            $gbkTglMulai = $post->get('gbkTglMulai', []);
            $gbkTahun    = $post->get('gbk-tahun', []);
            $gbkBulan    = $post->get('gbk-bulan', []);
            $gbkGaji     = $post->get('gbk-gaji', []);
            $gbkDok      = $uploadedFiles['gbk-dok'];

            foreach ($gbkId as $key => $id) {
                if (empty(trim($gbkNoSK[$key])) && $id == 0) {
                    continue;
                }

                if ($id == 0) {
                    $gbk = new PegGajiBerkalaModel;
                } else {
                    $gbk = PegGajiBerkalaModel::where('pegGajiBerkalaId', $id)->first();
                }
                if (!$gbk) {
                    throw new Exception('Data gaji berkala tidak ditemukan');
                }
                if ($gbkDelete[$key] == 1) {
                    $gbk->delete();
                    continue;
                }

                $gbk->pegawaiId = $pegawai->pegawaiId;
                $gbk->noSK = $gbkNoSK[$key];
                $gbk->tglSK = $gbkTglSK[$key] ?: null;
                $gbk->tglMulai = $gbkTglMulai[$key] ?: null;
                $gbk->masaKerjaTahun = $gbkTahun[$key];
                $gbk->masaKerjaBulan = $gbkBulan[$key];
                $gbk->gajiPokok = $gbkGaji[$key];

                // Dokumen PDF
                if ($gbkDok[$key]->getError() != UPLOAD_ERR_NO_FILE) {
                    $uplSuccess = $gbkDok[$key]->getError() === UPLOAD_ERR_OK;
                    $uplValid = v::size(null, '2MB')->mimetype('application/pdf')->validate($gbkDok[$key]->file);
                    if ($uplSuccess && $uplValid) {
                        $filename = $this->moveUploadedFile($documentPath, $gbkDok[$key]);
                        $gbk->dokumen = $filename;
                    } else {
                        $message = 'Gagal upload dokumen SK Gaji Berkala, error pada aplikasi';
                        if (!$uplValid) {
                            $message = 'File dokumen harus berupa PDF, ukuran maksimum 2MB';
                        }
                        throw new Exception($message);
                    }
                }

                $gbk->save();
            }


            /**
             * Riwayat Jabatan
             */
            $rjbId         = $post->get('rjb-id', []);
            $rjbDelete     = $post->get('rjb-delete', []);
            $rjbUnit       = $post->get('rjb-unit-kerja', []);
            $rjbJenis      = $post->get('rjb-jenis-jabatan', []);
            $rjbJabatan    = $post->get('rjb-nama-jabatan', []);
            $rjbEselon     = $post->get('rjb-eselon', []);
            $rjbBidang     = $post->get('rjb-bidang', []);
            $rjbSub        = $post->get('rjb-sub-bidang', []);
            $rjbDitetapkan = $post->get('rjb-ditetapkan-oleh', []);
            $rjbNoSK       = $post->get('rjb-no-sk', []);
            $rjbTmtJabatan = $post->get('rjbTmtJabatan', []);
            $rjbTmtEselon  = $post->get('rjbTmtEselon', []);
            $rjbTglSK      = $post->get('rjbTglSK', []);
            $rjbDok        = $uploadedFiles['rjb-dok'];

            foreach ($rjbId as $key => $id) {
                if (empty(trim($rjbUnit[$key])) && $id == 0) {
                    continue;
                }

                if ($id == 0) {
                    $rjb = new PegRiwayatJabatanModel;
                } else {
                    $rjb = PegRiwayatJabatanModel::where('pegRiwayatJabatanId', $id)->first();
                }
                if (!$rjb) {
                    throw new Exception('Data riwayat jabatan tidak ditemukan');
                }
                if ($rjbDelete[$key] == 1) {
                    $rjb->delete();
                    continue;
                }
                
                $rjb->pegawaiId      = $pegawai->pegawaiId;
                $rjb->unitKerja      = $rjbUnit[$key];
                $rjb->jenisJabatan   = $rjbJenis[$key];
                $rjb->eselon         = $rjbEselon[$key];
                $rjb->namaJabatan    = $rjbJabatan[$key];
                $rjb->bidang         = $rjbBidang[$key];
                $rjb->subBidang      = $rjbSub[$key];
                $rjb->tmtJabatan     = $rjbTmtJabatan[$key] ?: null;
                $rjb->ditetapkanOleh = $rjbDitetapkan[$key];
                $rjb->noSKJabatan    = $rjbNoSK[$key];
                $rjb->tmtEselon      = $rjbTmtEselon[$key] ?: null;
                $rjb->tglSKJabatan   = $rjbTglSK[$key] ?: null;


                // Dokumen PDF
                if ($rjbDok[$key]->getError() != UPLOAD_ERR_NO_FILE) {
                    $uplSuccess = $rjbDok[$key]->getError() === UPLOAD_ERR_OK;
                    $uplValid = v::size(null, '2MB')->mimetype('application/pdf')->validate($rjbDok[$key]->file);
                    if ($uplSuccess && $uplValid) {
                        $filename = $this->moveUploadedFile($documentPath, $rjbDok[$key]);
                        $rjb->dokumen = $filename;
                    } else {
                        $message = 'Gagal upload dokumen SK Jabatan, error pada aplikasi';
                        if (!$uplValid) {
                            $message = 'File dokumen harus berupa PDF, ukuran maksimum 2MB';
                        }
                        throw new Exception($message);
                    }
                }

                $rjb->save();
            }



            /**
             * Riwayat Pangkat
             */
            $pktId         = $post->get('pkt-id', []);
            $pktDelete     = $post->get('pkt-delete', []);
            $pktPangkat    = $post->get('pkt-pangkat', []);
            $pktTmtPangkat = $post->get('pktTmtPangkat', []);
            $pktDitetapkan = $post->get('pkt-ditetapkan-oleh', []);
            $pktNoSK       = $post->get('pkt-no-sk', []);
            $pktTglSK      = $post->get('pktTglSK', []);
            $pktTahun      = $post->get('pkt-tahun', []);
            $pktBulan      = $post->get('pkt-bulan', []);
            $pktDok        = $uploadedFiles['pkt-dok'];

            foreach ($pktId as $key => $id) {
                if (empty(trim($pktPangkat[$key])) && $id == 0) {
                    continue;
                }

                if ($id == 0) {
                    $pkt = new PegRiwayatPangkatModel;
                } else {
                    $pkt = PegRiwayatPangkatModel::where('pegRiwayatPangkatId', $id)->first();
                }
                if (!$pkt) {
                    throw new Exception('Data riwayat pangkat tidak ditemukan');
                }
                if ($pktDelete[$key] == 1) {
                    $pkt->delete();
                    continue;
                }
                
                $pkt->pegawaiId              = $pegawai->pegawaiId;
                $pkt->jenisPangkatGolonganId = $pktPangkat[$key];
                $pkt->tmtPangkat             = $pktTmtPangkat[$key] ?: null;
                $pkt->ditetapkanOleh         = $pktDitetapkan[$key];
                $pkt->noSKPangkat            = $pktNoSK[$key];
                $pkt->tglSKPangkat           = $pktTglSK[$key] ?: null;
                $pkt->masaKerjaTahun         = $pktTahun[$key];
                $pkt->masaKerjaBulan         = $pktBulan[$key];

                // Dokumen PDF
                if ($pktDok[$key]->getError() != UPLOAD_ERR_NO_FILE) {
                    $uplSuccess = $pktDok[$key]->getError() === UPLOAD_ERR_OK;
                    $uplValid = v::size(null, '2MB')->mimetype('application/pdf')->validate($pktDok[$key]->file);
                    if ($uplSuccess && $uplValid) {
                        $filename = $this->moveUploadedFile($documentPath, $pktDok[$key]);
                        $pkt->dokumen = $filename;
                    } else {
                        $message = 'Gagal upload dokumen SK Pangkat, error pada aplikasi';
                        if (!$uplValid) {
                            $message = 'File dokumen harus berupa PDF, ukuran maksimum 2MB';
                        }
                        throw new Exception($message);
                    }
                }
                $pkt->save();
            }




            /**
             * Penguasaan Bahasa
             */
            $bhsId        = $post->get('bhs-id', []);
            $bhsDelete    = $post->get('bhs-delete', []);
            $bhsBahasa    = $post->get('bhs-bahasa', []);
            $bhsKemampuan = $post->get('bhs-kemampuan', []);

            foreach ($bhsId as $key => $id) {
                if (empty(trim($bhsBahasa[$key])) && $id == 0) {
                    continue;
                }

                if ($id == 0) {
                    $bhs = new PegBahasaModel;
                } else {
                    $bhs = PegBahasaModel::where('pegBahasaId', $id)->first();
                }
                if (!$bhs) {
                    throw new Exception('Data kemampuan bahasa tidak ditemukan');
                }
                if ($bhsDelete[$key] == 1) {
                    $bhs->delete();
                    continue;
                }
                
                $bhs->pegawaiId     = $pegawai->pegawaiId;
                $bhs->jenisBahasaId = $bhsBahasa[$key];
                $bhs->kemampuan     = $bhsKemampuan[$key];
                $bhs->save();
            }



            /**
             * Riwayat Pendidikan
             */
            $pdkId         = $post->get('pdk-id', []);
            $pdkDelete     = $post->get('pdk-delete', []);
            $pdkPendidikan = $post->get('pdk-pendidikan', []);
            $pdkJurusan    = $post->get('pdk-jurusan', []);
            $pdkInstitusi  = $post->get('pdk-insitusi', []);
            $pdkTempat     = $post->get('pdk-tempat', []);
            $pdkKepala     = $post->get('pdk-kepala', []);
            $pdkNoIjasah   = $post->get('pdk-no-ijasah', []);
            $pdkTglIjasah  = $post->get('pdkTglIjasah', []);
            $pdkDok        = $uploadedFiles['pdk-dok'];

            foreach ($pdkId as $key => $id) {
                if (empty(trim($pdkPendidikan[$key])) && $id == 0) {
                    continue;
                }

                if ($id == 0) {
                    $pdk = new PegRiwayatPendidikanModel;
                } else {
                    $pdk = PegRiwayatPendidikanModel::where('pegRiwayatPendidikanId', $id)->first();
                }
                if (!$pdk) {
                    throw new Exception('Data riwayat pendidikan tidak ditemukan');
                }
                if ($pdkDelete[$key] == 1) {
                    $pdk->delete();
                    continue;
                }
                
                $pdk->pegawaiId     = $pegawai->pegawaiId;
                $pdk->jenisPendidikanId = $pdkPendidikan[$key];
                $pdk->jurusan = $pdkJurusan[$key];
                $pdk->namaInstitusi = $pdkInstitusi[$key];
                $pdk->tempat = $pdkTempat[$key];
                $pdk->namaKepala = $pdkKepala[$key];
                $pdk->noIjasah = $pdkNoIjasah[$key];
                $pdk->tglIjasah = $pdkTglIjasah[$key] ?: null;

                // Dokumen PDF
                if ($pdkDok[$key]->getError() != UPLOAD_ERR_NO_FILE) {
                    $uplSuccess = $pdkDok[$key]->getError() === UPLOAD_ERR_OK;
                    $uplValid = v::size(null, '2MB')->mimetype('application/pdf')->validate($pdkDok[$key]->file);
                    if ($uplSuccess && $uplValid) {
                        $filename = $this->moveUploadedFile($documentPath, $pdkDok[$key]);
                        $pdk->dokumen = $filename;
                    } else {
                        $message = 'Gagal upload dokumen SK Pangkat, error pada aplikasi';
                        if (!$uplValid) {
                            $message = 'File dokumen harus berupa PDF, ukuran maksimum 2MB';
                        }
                        throw new Exception($message);
                    }
                }
                $pdk->save();
            }



            /**
             * Riwayat Diklat
             */
            $dktId            = $post->get('dkt-id', []);
            $dktDelete        = $post->get('dkt-delete', []);
            $dktDiklat        = $post->get('dkt-diklat', []);
            $dktNama          = $post->get('dkt-nama', []);
            $dktTempat        = $post->get('dkt-tempat', []);
            $dktPenyelenggara = $post->get('dkt-penyelenggara', []);
            $dktAngkatan      = $post->get('dkt-angkatan', []);
            $dktTglMulai      = $post->get('dktTglMulai', []);
            $dktTglSelesai    = $post->get('dktTglSelesai', []);
            $dktNoSTTP        = $post->get('dkt-no-sttp', []);
            $dktTglSTTP       = $post->get('dktTglSTTP', []);

            foreach ($dktId as $key => $id) {
                if (empty(trim($dktDiklat[$key])) && $id == 0) {
                    continue;
                }

                if ($id == 0) {
                    $dkt = new PegDiklatModel;
                } else {
                    $dkt = PegDiklatModel::where('pegDiklatId', $id)->first();
                }
                if (!$dkt) {
                    throw new Exception('Data riwayat diklat tidak ditemukan');
                }
                if ($dktDelete[$key] == 1) {
                    $dkt->delete();
                    continue;
                }
                
                $dkt->pegawaiId     = $pegawai->pegawaiId;
                $dkt->jenisDiklat = $dktDiklat[$key];
                $dkt->namaDiklat = $dktNama[$key];
                $dkt->tempatDiklat = $dktTempat[$key];
                $dkt->penyelenggara = $dktPenyelenggara[$key];
                $dkt->angkatan = $dktAngkatan[$key];
                $dkt->tglMulai = $dktTglMulai[$key] ?: null;
                $dkt->tglSelesai = $dktTglSelesai[$key] ?: null;
                $dkt->noSTTP = $dktNoSTTP[$key];
                $dkt->tglSTTP = $dktTglSTTP[$key] ?: null;
                $dkt->save();
            }



            /**
             * Penghargaan
             */
            $phgId          = $post->get('phg-id', []);
            $phgDelete      = $post->get('phg-delete', []);
            $phgPenghargaan = $post->get('phg-nama', []);
            $phgAsal        = $post->get('phg-asal', []);
            $phgTahun       = $post->get('phg-tahun', []);
            $phgNoPiagam    = $post->get('phg-no-piagam', []);
            $phgTglPiagam   = $post->get('phgTglPiagam', []);
            $phgDok         = $uploadedFiles['phg-dok'];

            foreach ($phgId as $key => $id) {
                if (empty(trim($phgPenghargaan[$key])) && $id == 0) {
                    continue;
                }

                if ($id == 0) {
                    $phg = new PegPenghargaanModel;
                } else {
                    $phg = PegPenghargaanModel::where('pegPenghargaanId', $id)->first();
                }
                if (!$phg) {
                    throw new Exception('Data riwayat penghargaan tidak ditemukan');
                }
                if ($phgDelete[$key] == 1) {
                    $phg->delete();
                    continue;
                }
                
                $phg->pegawaiId     = $pegawai->pegawaiId;
                $phg->namaPenghargaan = $phgPenghargaan[$key];
                $phg->asal = $phgAsal[$key];
                $phg->tahun = $phgTahun[$key];
                $phg->noPiagam = $phgNoPiagam[$key];
                $phg->tglPiagam = $phgTglPiagam[$key] ?: null;

                // Dokumen PDF
                if ($phgDok[$key]->getError() != UPLOAD_ERR_NO_FILE) {
                    $uplSuccess = $phgDok[$key]->getError() === UPLOAD_ERR_OK;
                    $uplValid = v::size(null, '2MB')->mimetype('application/pdf')->validate($phgDok[$key]->file);
                    if ($uplSuccess && $uplValid) {
                        $filename = $this->moveUploadedFile($documentPath, $phgDok[$key]);
                        $phg->dokumen = $filename;
                    } else {
                        $message = 'Gagal upload dokumen SK Penghargaan, error pada aplikasi';
                        if (!$uplValid) {
                            $message = 'File dokumen harus berupa PDF, ukuran maksimum 2MB';
                        }
                        throw new Exception($message);
                    }
                }
                $phg->save();
            }


            /**
             * Orang tua dan anak
             */
            $pegawai->namaAyah = $post->get('ayah-nama', '');
            $pegawai->tempatLahirAyah = $post->get('ayah-tempat', '');
            $pegawai->tglLahirAyah = $post->get('ayahTgl', null) ?: null;
            $pegawai->pekerjaanAyah = $post->get('ayah-pekerjaan', '');
            $pegawai->alamatAyah = $post->get('ayah-alamat', '');
            $pegawai->namaIbu = $post->get('ibu-nama', '');
            $pegawai->tempatLahirIbu = $post->get('ibu-tempat', '');
            $pegawai->tglLahirIbu = $post->get('ibuTgl', null) ?: null;
            $pegawai->pekerjaanIbu = $post->get('ibu-pekerjaan', '');
            $pegawai->alamatIbu = $post->get('ibu-alamat', '');

            // Anak
            $ankId         = $post->get('ank-id', []);
            $ankDelete     = $post->get('ank-delete', []);
            $ankNama       = $post->get('ank-nama', []);
            $ankTempat     = $post->get('ank-tempat', []);
            $ankTgl        = $post->get('ankTgl', []);
            $ankJK         = $post->get('ank-jk', []);
            $ankStatus     = $post->get('ank-status', []);
            $ankPendidikan = $post->get('ank-pendidikan', []);
            $ankJurusan    = $post->get('ank-jurusan', []);
            $ankPekerjaan  = $post->get('ank-pekerjaan', []);
            $ankTunjangan  = $post->get('ank-tunjangan', []);

            foreach ($ankId as $key => $id) {
                if (empty(trim($ankNama[$key])) && $id == 0) {
                    continue;
                }

                if ($id == 0) {
                    $ank = new PegAnakModel;
                } else {
                    $ank = PegAnakModel::where('pegAnakId', $id)->first();
                }
                if (!$ank) {
                    throw new Exception('Data anak tidak ditemukan');
                }
                if ($ankDelete[$key] == 1) {
                    $ank->delete();
                    continue;
                }
                
                $ank->pegawaiId     = $pegawai->pegawaiId;
                $ank->nama = $ankNama[$key];
                $ank->tempatLahir = $ankTempat[$key];
                $ank->tglLahir = $ankTgl[$key] ?: null;
                $ank->jk = $ankJK[$key];
                $ank->statusKeluarga = $ankStatus[$key];
                $ank->jenisPendidikanId = $ankPendidikan[$key];
                $ank->jurusan = $ankJurusan[$key];
                $ank->pekerjaan = $ankPekerjaan[$key];
                $ank->statusTunjangan = $ankTunjangan[$key];
                $ank->save();
            }




            $pegawai->save();

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

    public function excel()
    {
        $get = new GetSetHelper($this->request->getQueryParams());
        $exportCols  = $get->get('expCol', []);
        $exportSorts  = $get->get('expSort', []);
        $azRange = $this->excelColumnRange('A', 'AZ');//range('A', 'Z');      
        $data = $this->prepareExportData();
        $pegawais = $data['pegawais'];
        $columnName = $data['columnName'];

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet0 = $spreadsheet->setActiveSheetIndex(0);

        // Set document properties
        $spreadsheet->getProperties()->setCreator('Edy Santosa Putra')
            ->setLastModifiedBy('Edy Santosa Putra')
            ->setTitle('Daftar Pegawai Dissos P3A Prov. Bali')
            ->setSubject('Daftar Pegawai Dissos P3A Prov. Bali')
            ->setDescription('Daftar Pegawai Dissos P3A Prov. Bali dari sistem database kepegawaian')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('List');

        // Add some data
        $sheet0->setCellValue('A1', 'Daftar Pegawai');

        $sheet0->getStyle('A1')->getFont()->applyFromArray([
            'bold' => true,
            'size' => 18
        ]);

        $sheet0->getStyle('A2:I2')->getFont()->applyFromArray([
            'bold' => true
        ]);


        foreach ($exportCols as $key => $value) {
            $sheet0->getColumnDimension($azRange[$key])->setAutoSize(true);
            $sheet0->setCellValue($azRange[$key].'2', $columnName[$value]);
            $lastCol = $azRange[$key];
        }
        $sheet0->getStyle('A2:'.$lastCol.'2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEEEEEE');

        $curRow = 2;
        foreach ($pegawais as $pegawai) {
            $curRow++;
            foreach ($exportCols as $key => $value) {
                if (in_array($value, ['nik', 'nip'])) {
                    $sheet0->getCell($azRange[$key].$curRow)->setValueExplicit($pegawai[$value], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } elseif (in_array($value, ['tglLahir'])) {
                    $sheet0->setCellValue($azRange[$key].$curRow, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($pegawai[$value]));
                    $sheet0->getStyle($azRange[$key].$curRow)->getNumberFormat()->setFormatCode("dd-mm-yyyy");
                } else {
                    $sheet0->setCellValue($azRange[$key].$curRow, $pegawai[$value]);
                }
            }
        }

        $styleArrayTabel = array(
        'alignment' => array(
                     'rotation'   => 0,
                     'wrap'       => true
            ),
            'borders' => array(
                'allBorders' => array(
                      'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, //BORDER_THIN BORDER_MEDIUM BORDER_HAIR
                      'color' => array('rgb' => '000000')
                )
              )
        );
        $sheet0->getStyle('A2:'.$lastCol.$curRow)->applyFromArray($styleArrayTabel);
        $sheet0->getStyle('A2:'.$lastCol.'2')->applyFromArray([
            'alignment' =>[
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);


        // Rename worksheet
        $sheet0->setTitle('Daftar Pegawai');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="pegawai.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        return $this->response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function excelColumnRange($lower, $upper)
    {
        $letters = array();
        $letter = $lower;
        while ($letter !== $upper) {
            $letters[] = $letter++;
        }
        return $letters;
    }

    public function pdf()
    {
        $get = new GetSetHelper($this->request->getQueryParams());
        $exportCols  = $get->get('expCol', []);
        $data = $this->prepareExportData();
        $pegawais = $data['pegawais'];
        $columnName = $data['columnName'];


        $html= $this->view->fetchHtml('prints/pegawai.twig', [
            'exportCols' => $exportCols,
            'pegawais' => $pegawais,
            'columnName' => $columnName,
        ]);
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' =>'/tmp',
            'format' => [297, 210],
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 2,
            'margin_footer' => 2,
            'fontDir' => [
                // __DIR__ . '/../../vendor/webfontkit/roboto/fonts',
                '/var/www/html/ekajaya/vendor/webfontkit/roboto/fonts',
            ],
            'fontdata' => [
                'roboto' => [
                    'R' => 'roboto-regular.ttf',
                    'I' => 'roboto-italic.ttf',
                    'B'  => 'roboto-bold.ttf',
                    'BI'  => 'roboto-bolditalic.ttf',
                    'L'  => 'roboto-light.ttf',
                ]
            ],
            'default_font_size' => 8,
            'default_font' => 'roboto',
        ]);
        $mpdf->WriteHTML($html);
        // $mpdf->debug = true;


        $mpdf->setTitle("Daftar Pegawai Dinas Sosial P3A Provinsi Bali");

        if ($get->get('download', 0) == 1) {
            $mpdf->Output($pdfTitle.'.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        } else {
            $mpdf->Output($pdfTitle.'.pdf', \Mpdf\Output\Destination::INLINE);
        }
        return $this->response->withHeader('Content-Type', 'application/pdf');
    }


    private function prepareExportData()
    {
        $pegawaiData = json_decode($this->loadData()->getBody(), true);
        $get = new GetSetHelper($this->request->getQueryParams());

        // Nama untuk kolom yang valid
        $columnName['nik']              = 'NIK';
        $columnName['nip']              = 'NIP';
        $columnName['nama']             = 'Nama Pegawai';
        $columnName['tempatLahir']      = 'Tempat Lahir';
        $columnName['tglLahir']         = 'Tgl. Lahir';
        $columnName['jk']               = 'JenisKelamin';
        $columnName['agama']            = 'Agama';
        $columnName['alamat']           = 'Alamat';
        $columnName['kelurahan']        = 'Kelurahan';
        $columnName['kecamatan']        = 'Kecamatan';
        $columnName['kabupaten']        = 'Kabupaten';
        $columnName['provinsi']         = 'Provinsi';
        $columnName['kodePos']          = 'Kode Pos';
        $columnName['noTelepon']        = 'NoTelepon';
        $columnName['email']            = 'Email';
        $columnName['statusPernikahan'] = 'Status Pernikahan';
        $columnName['golonganDarah']    = 'Golongan Darah';
        $columnName['noKarpeg']         = 'No. Karpeg';
        $columnName['noBPJS']           = 'No. BPJS';
        $columnName['noKaris']          = 'No. Karis';
        $columnName['noTaspen']         = 'No. Taspen';
        $columnName['noNPWP']           = 'No. NPWP';
        $columnName['pangkat']          = 'Pangkat';
        $columnName['jabatan']          = 'Jabatan';
        $columnName['eselon']           = 'Eselon';
        $columnName['bidang']           = 'Bidang';
        $columnName['subbag']           = 'Subbag';
        
        $pegawais = [];
        foreach ($pegawaiData['data'] as $data) {
            $agama = \app\model\JenisAgamaModel::where('jenisAgamaId', $data['jenisAgamaId'])->first();
            $provinsi = \app\model\JenisProvinsiModel::where('jenisProvinsiId', $data['jenisProvinsiId'])->first();
            switch ($data['statusPernikahan']) {
                case '1':
                    $statusPernikahan = "Single";
                    break;
                case '1':
                    $statusPernikahan = "Menikah";
                    break;
                case '1':
                    $statusPernikahan = "Janda";
                    break;
                default:
                    $statusPernikahan = "Duda";
                    break;
            }

            $pangkat = PegRiwayatPangkatModel::with('pangkat')->where('pegawaiId', $data['pegawaiId'])->latest('tglSKPangkat')->first();
            $jabatan = PegRiwayatJabatanModel::where('pegawaiId', $data['pegawaiId'])->latest('tglSKJabatan')->first();
            $bidang = \app\model\JenisBidangModel::where('jenisBidangId', $data['jenisBidangId'])->first();
            $subbag = \app\model\JenisSubbagModel::where('jenisSubbagId', $data['jenisSubbagId'])->first();


            $newpeg = [];
            $newPeg['nik']              = $data['nik'];
            $newPeg['nip']              = $data['nip'];
            $newPeg['nama']             = $data['nama'];
            $newPeg['tempatLahir']      = $data['tempatLahir'];
            $newPeg['tglLahir']         = $data['tglLahir'];
            $newPeg['jk']               = $data['jk'] == 1 ? "Laki-laki" : "Perempuan";
            $newPeg['agama']            = $agama->jenisAgama;
            $newPeg['alamat']           = $data['alamat'];
            $newPeg['kelurahan']        = $data['kelurahan'];
            $newPeg['kecamatan']        = $data['kecamatan'];
            $newPeg['kabupaten']        = $data['kabupaten'];
            $newPeg['provinsi']         = $provinsi->provinsi;
            $newPeg['kodePos']          = $data['kodePos'];
            $newPeg['noTelepon']        = $data['noTelepon'];
            $newPeg['email']            = $data['email'];
            $newPeg['statusPernikahan'] = $statusPernikahan;
            $newPeg['golonganDarah']    = $data['golonganDarah'];
            $newPeg['noKarpeg']         = $data['noKarpeg'];
            $newPeg['noBPJS']           = $data['noBPJS'];
            $newPeg['noKaris']          = $data['noKaris'];
            $newPeg['noTaspen']         = $data['noTaspen'];
            $newPeg['noNPWP']           = $data['noNPWP'];
            $newPeg['pangkat']          = $pangkat ? $pangkat->pangkat->pangkat . ' - ' . $pangkat->pangkat->golonganRuang : '';
            $newPeg['jabatan']          = $jabatan ? $jabatan->namaJabatan : '';
            $newPeg['eselon']           = $jabatan ? $jabatan->eselon : '';
            $newPeg['bidang']           = $bidang ? $bidang->bidang : '' ;
            $newPeg['subbag']           = $subbag ? $subbag->subbag : '' ;
            $pegawais[] = $newPeg;
        }

        return [
            "pegawais" => $pegawais,
            "columnName" => $columnName,
        ];
    }

    public function test()
    {
        $pegawai = PegawaiModel::with(['gajiBerkala', 'riwayatJabatan'])->where('pegawaiId', 2)->first();
        $pegawai->tglLahir = null;
        $pegawai->save();


        return $this->response->withJson([
            'message' => $pegawai->toArray()
        ]);
    }



    // Home Page
    public function homeInfo()
    {
        try {
            $totalPegawai = PegawaiModel::where('status', 1)->count();
            $totalPegawaiPNS = PegawaiModel::where('status', 1)->whereIn('jenisKepegawaianId', [1,2])->count();
            $totalPegawaiPPPK = PegawaiModel::where('status', 1)->where('jenisKepegawaianId', 3)->count();
            $totalPegawaiKontrak = PegawaiModel::where('status', 1)->where('jenisKepegawaianId', 4)->count();
            $totalPegawaiPensiun= PegawaiModel::where('status', 1)->where('tglLahir', '<=', date('Y-m-d', strtotime('-58 years')))->count();
                
            return $this->response->withJson([
                'totalPegawai'        => $totalPegawai,
                'totalPegawaiPNS'     => $totalPegawaiPNS,
                'totalPegawaiPPPK'    => $totalPegawaiPPPK,
                'totalPegawaiKontrak' => $totalPegawaiKontrak,
                'totalPegawaiPensiun' => $totalPegawaiPensiun,
            ]);
        } catch (Exception $err) {
            return $this->response->withStatus(500)->withJson([
                'message' => $err->getMessage()
            ]);
        }
    }
}
