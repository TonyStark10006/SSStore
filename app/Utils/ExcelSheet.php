<?php

namespace App\Utils;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Exception;


class ExcelSheet
{
    private $excel;
    private $presentSheet;

    public function __construct($title = null, $creator = 'admin', $description = null, $lastModifier = 'admin')
    {
        $this->excel = new PHPExcel();
        $this->excel->getProperties()
            ->setCreator($creator)
            ->setLastModifiedBy($lastModifier)
            ->setTitle($title)
            ->setDescription($description);
        $this->presentSheet = $this->excel->getActiveSheet();
    }

    public function outPutExcelFile($filename = 'default', $filePath = '.', $type = 'Excel2007') : bool
    {
        if ($filePath !== '.') {
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }
        }

        $fileFullName = $filePath . '/' . $filename;
        if ($type == 'Excel2007') {
            $fileFullName .= '.xlsx';
        } else {
            $fileFullName .= '.xls';
        }

        try {
            PHPExcel_IOFactory::createWriter($this->excel, $type)->save($fileFullName);
        } catch (PHPExcel_Exception $e) {
            app('debugbar')->info($e->getMessage());
            return false;
        }

        return true;
    }

    public function addASheet() : void
    {
        $this->presentSheet = $this->excel->createSheet();
        return;
    }

    public function getSheetAmount() : int
    {
        return count($this->excel->getAllSheets());
    }

    public function addData($dataArray, array $properties = []) : void
    {
        if (empty($dataArray)) {
            return;
        }

        if ($this->presentSheet->getCell('A1')->getValue()) {
            $paddingTop = 1;
        } else {
            $paddingTop = 0;
        }

        try {
            foreach ($dataArray as $keyForRow => $itemCollection) {
                static $i = 0;
                foreach ($itemCollection as $item) {
                    $this->presentSheet->setCellValueByColumnAndRow($i, $keyForRow + 1 + $paddingTop, $item);
                    ++$i;
                }
                $i = 0;
            }
        } catch (PHPExcel_Exception $e) {
            app('debugbar')->info($e->getMessage());
            return;
        }

        return;
    }

    public function setHeader(array $headerArray) : void
    {
        try {
            foreach ($headerArray as $key => $item) {
                $this->presentSheet->setCellValueByColumnAndRow($key, 1, $item);
            }
        } catch (PHPExcel_Exception $e) {
            app('debugbar')->info($e->getMessage());
        }
    }
}