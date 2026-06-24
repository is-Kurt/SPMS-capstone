<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DocumentFolderModel;
use App\Models\EvaluationRoutingModel;
use App\Models\DocumentModel;

class Rating extends BaseController
{
    public function index($folderId = null) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');     

        $folderModel = new DocumentFolderModel();

        $folders = $folderModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        if (!$folderId) {
            $lastId = session()->get('active_folder_id');
            if ($lastId && array_search($lastId, array_column($folders, 'id')) !== false) {
                return redirect()->to('ratings/' . $lastId);
            } elseif (!empty($folders)) {
                return redirect()->to('ratings/' . $folders[0]['id']);
            }
        } else {
            session()->set('active_folder_id', $folderId);
        }

        $builder = $folderModel->db->table('document_folders df')
            ->select("df.id as folder_id, df.user_id, (u.first_name || ' ' || u.last_name) as username, 
                      REPLACE(GROUP_CONCAT(DISTINCT pos.title), ',', ', ') as position, 
                      REPLACE(GROUP_CONCAT(DISTINCT un.name), ',', ', ') as department, 
                      df.final_rating, df.status as folder_status")
            ->join('users u', 'u.id = df.user_id')
            ->join('plantillas p', 'p.user_id = u.id AND p.ended_at IS NULL', 'left')
            ->join('positions pos', 'pos.id = p.position_id', 'left')
            ->join('units un', 'un.id = p.unit_id', 'left');

        if ($sysRole === 'Admin') {
            $builder->where('df.parent_folder_id', $folderId);
        } else {
            $builder->join('evaluation_routings er_me', 'er_me.folder_id = df.id')
                    ->where('er_me.evaluator_id', $userId)
                    ->where('er_me.evaluator_folder_id', $folderId);
        }

        $builder->groupBy('df.id');
        $rawFolders = $builder->get()->getResultArray();

        foreach ($rawFolders as &$f) {
            if ($f['position'])   $f['position']   = str_replace(',', ', ', $f['position']);
            if ($f['department']) $f['department'] = str_replace(',', ', ', $f['department']);
        }

        $tabs = [
            'action'    => ['label' => 'Action Required', 'folders' => []],
            'pending'   => ['label' => 'Pending Subordinate', 'folders' => []],
            'completed' => ['label' => 'Completed', 'folders' => []]
        ];

        foreach ($rawFolders as $f) {
            if ($f['folder_status'] === \App\Enums\FolderStatus::APPROVED->value) {
                $tabs['completed']['folders'][] = $f;
            } elseif (in_array($f['folder_status'], [\App\Enums\FolderStatus::DRAFT->value, \App\Enums\FolderStatus::REEVALUATE->value])) {
                $tabs['pending']['folders'][] = $f;
            } else {
                $tabs['action']['folders'][] = $f;
            }
        }

        return view('app_shell', [
            'sidebarFolders'   => $folders,
            'selectedFolderId' => $folderId, 
            'mainView'         => 'rating/_show', 
            'mainData'         => [
                'tabs'    => $tabs,
                'sysRole' => $sysRole
            ]
        ]);
    }

    public function show($subFolderId) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        $folderModel = new DocumentFolderModel();
        $routingModel = new EvaluationRoutingModel();
        $documentModel = new DocumentModel();

        $subFolder = $folderModel->find($subFolderId);
        if (!$subFolder) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $folderOwnerId = $subFolder['user_id'];
        $isAuthorized = false;

        if ($folderOwnerId == $userId || $sysRole === 'Admin') {
            $isAuthorized = true;
        } else {
            $routingCount = $routingModel->where('folder_id', $subFolderId)
                                         ->where('evaluator_id', $userId)
                                         ->countAllResults();
            if ($routingCount > 0) $isAuthorized = true;
        }

        if (!$isAuthorized) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unauthorized access.');

        $folders = $folderModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        return view('app_shell', [
            'sidebarFolders'   => $folders, 
            'selectedFolderId' => null, 
            'mainView'         => 'document/_doc_rows', 
            'mainData'         => [
                'activeFolder'  => $subFolder,
                'myDocs'        => $documentModel->where('document_folder_id', $subFolderId)->findAll(),
                'isReadOnly'    => true, 
                'presets'       => []    
            ]
        ]);
    }
}