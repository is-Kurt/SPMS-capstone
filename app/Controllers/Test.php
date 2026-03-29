<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Table;

class Test extends BaseController
{
    public function index()
    {
        return view('test');
    }

public function importWordTable()
{
    ini_set('memory_limit', '512M'); // Allow up to 512MB RAM
    ini_set('max_execution_time', 300); // Allow 5 minut


    $file = $this->request->getFile('word_file');
    
    // Check if file was uploaded
    if (!$file || !$file->isValid()) {
        return $this->response->setJSON([
            'success' => false,
            'error' => 'No valid file uploaded'
        ]);
    }
    
    // Check MIME type - ODT has a different MIME
    $mimeType = $file->getMimeType();
    $extension = $file->getExtension();
    
    // Allowed formats
    $allowedExtensions = ['docx', 'odt'];
    $allowedMimes = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
        'application/vnd.oasis.opendocument.text' // odt
    ];
    
    if (!in_array($extension, $allowedExtensions) || !in_array($mimeType, $allowedMimes)) {
        return $this->response->setJSON([
            'success' => false,
            'error' => 'Please upload a DOCX or ODT file'
        ]);
    }
    
    try {
        log_message('error', 'Starting to load PHPWord file...');
        
        $phpWord = IOFactory::load($file->getTempName());
        
        log_message('error', 'File loaded. Starting to parse sections...');

        $tables = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                    // Process table...
                    $tableData = [];
                    foreach ($element->getRows() as $row) {
                        $rowData = [];
                        foreach ($row->getCells() as $cell) {
                            // Extract cell content
                            $cellContent = '';
                            foreach ($cell->getElements() as $cellElement) {
                                if ($cellElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                    $cellContent .= $cellElement->getText();
                                } elseif ($cellElement instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                    foreach ($cellElement->getElements() as $textElement) {
                                        if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                            $cellContent .= $textElement->getText();
                                        }
                                    }
                                }
                            }
                            $rowData[] = $cellContent;
                        }
                        $tableData[] = $rowData;
                    }
                    $tables[] = $tableData;
                }
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'tables' => $tables
        ]);
        
    } catch (\Throwable $e) { 
        // Use \Throwable instead of \Exception to catch Fatal Errors too
        log_message('error', 'PHPWord Crash: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'error' => 'Fatal Error: ' . $e->getMessage()
        ]);
    }
}
}