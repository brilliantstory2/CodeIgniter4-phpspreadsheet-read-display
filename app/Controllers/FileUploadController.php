<?php

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;

class FileUploadController extends BaseController
{

    public function __construct( )
    {
    }


    public function uploadFile()
    {
        $response = ['success' => false, 'message' => '', 'data' => []];

        $file = $this->request->getFile('userfile');
        if ($file) {
            $validationRules = [
                'userfile' => [
                    'uploaded[userfile]',
                    'mime_in[userfile,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet]',
                    'max_size[userfile,10000]'
                ],
            ];

            if (!$this->validate($validationRules)) {
                $response['message'] = $this->validator->getError('userfile');
            } else {
                $newName = $file->getRandomName();
                $file->move('uploads', $newName);

                $response['success'] = true;
                $response['message'] = 'File uploaded successfully!';
                $response['file_name'] = $file->getName();

                $spreadsheet = IOFactory::load(FCPATH . 'uploads/'.$file->getName());
                $sheet = $spreadsheet->getActiveSheet();
                $excelData = array();


                $rowData = array();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    array_push($rowData, $col);
                }
                array_push($excelData, $rowData);

                for ($row = 1; $row <= $highestRow; $row++) {
                    $rowData = array();
                    for ($column = 'A'; $column <= $highestColumn; $column++) {
                        $cellValue = $sheet->getCell($column . $row)->getValue();
                        array_push($rowData, $cellValue);
                    }
                    array_push($excelData, $rowData);
                }
                $response['data'] = $excelData;
            }
        } else {
            $response['message'] = 'No file was uploaded.';
        }

        return $this->response->setJSON($response);
    }
}
