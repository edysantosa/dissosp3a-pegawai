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

                $nik = trim($row['A']);
                $alamat = trim($row['I']);
                $pegawai = PegawaiModel::where('nip', trim($row['K']))->first();

                if (!$pegawai) {
                    throw new Exception($index);
                }

                $pegawai->nik = $nik;
                $pegawai->alamat = $alamat;
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
