<?php namespace app\controller;

use \Exception;
use \app\model\PegawaiModel;
use \app\helper\GetSetHelper;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

use \Carbon\Carbon;

class Tools extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function importPegawai()
    {
        return $this->view
            ->addCss($this->url . '/assets/dist/css/import-pegawai.css')
            ->addJs($this->url . '/assets/dist/js/import-pegawai.js')

            ->render('importPegawai.twig');
    }

    public function submitImportPegawai()
    {
        $post = new GetSetHelper($this->request->getParsedBody());
        $ids = $post->get('ids', null);
        try {
            $uploadedFiles = $this->request->getUploadedFiles();
            $uploadedFile = $uploadedFiles['excel'];
            if ($uploadedFile->getError() != UPLOAD_ERR_OK) {
                throw new Exception('Gagal upload file excel.');
            }

            // $spreadsheet = IOFactory::load($uploadedFile->file);
            // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();


            /**  Identify the type of $inputFileName  **/
            $inputFileType = IOFactory::identify($uploadedFile->file);
            /**  Create a new Reader of the type that has been identified  **/
            $reader = IOFactory::createReader($inputFileType);
            /**  Load $inputFileName to a Spreadsheet Object  **/
            $spreadsheet = $reader->load($uploadedFile->file);


            $loadedSheet = $spreadsheet->getActiveSheet();
            $highestRow = $loadedSheet->getHighestRow();
            $highestColumn = $loadedSheet->getHighestColumn();

            $sheet = $loadedSheet->rangeToArray("A2:{$highestColumn}{$highestRow}", NULL, TRUE, TRUE, TRUE);
            $this->database->getConnection()->getPdo()->beginTransaction();
            foreach ($sheet as $index => $row) {

                $golonganDarah = trim($row['L']);
                $tglLahir = new Carbon(trim($row['P']));
                $tempatLahir = ucfirst(strtolower(trim($row['O'])));
                $jk = (trim($row['I'])) == "Perempuan" ? 2 : 1;

                switch (trim($row['K'])) {
                    case 'Menikah':
                        $statusPernikahan = 2;
                        break;
                    case 'Belum Menikah':
                        $statusPernikahan = 1;
                        break;
                    default:
                        $statusPernikahan = 3;
                        break;
                }

                // return $this->response->withJson([
                //     'tglLahir' => $tglLahir->format('d/m/Y'),
                //     'tempatLahir' => $tempatLahir,
                //     'jk' => $jk,
                //     'golonganDarah' => $golonganDarah,
                //     'statusPernikahan' => $statusPernikahan,
                // ]);
                $pegawai = PegawaiModel::where('nip', trim($row['A']))->first();

                if (!$pegawai) {
                    throw new Exception($index);
                }
                $pegawai->tglLahir = $tglLahir->format('Y-m-d');
                $pegawai->tempatLahir = $tempatLahir;
                $pegawai->jk = $jk;
                $pegawai->golonganDarah = $golonganDarah;
                $pegawai->statusPernikahan = $statusPernikahan;
                $pegawai->save();
            }
            $this->database->getConnection()->getPdo()->commit();

            return $this->response->withJson([
                'message' => "berhasil import"
            ]);
        } catch (Exception $err) {
            $this->database->getConnection()->getPdo()->rollBack();
            return $this->response->withStatus(500)->withJson([
                'message' => $err->getMessage()
            ]);
        }
    }
}
