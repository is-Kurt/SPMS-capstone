<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Rating extends BaseController
{
    public function index() {
        $userId = session()->get('user_id');
        $role   = session()->get('role');
        $dept   = session()->get('department');
        $folderId = $this->request->getGet('folder_id');
        $subFolderId = $this->request->getGet('sub_folder'); 
        
        $folderModel = new \App\Models\DocumentFolderModel();
        
        // 1. Fetch Sidebar Folders (The Master Batches)
        $folders = $folderModel->where('user_id', $userId)
                               ->orderBy('created_at', 'DESC')
                               ->findAll();
                               
        if (!$folderId && !empty($folders)) {
            $folderId = $folders[0]['id'];
        }
        
        $activeFolder = $folderId ? $folderModel->find($folderId) : null;

        // ==============================================================
        // MODE A: VIEWING A SUBORDINATE'S FOLDER
        // ==============================================================
        if ($activeFolder && $subFolderId) {
            $subFolder = $folderModel->find($subFolderId);
            if (!$subFolder) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Folder not found.');

            // Hierarchical Security Check
            $folderOwnerId = $subFolder['user_id'];
            $isAuthorized = false;
            
            $userModel = new \App\Models\UserModel();
            $folderOwner = $userModel->find($folderOwnerId);

            if ($folderOwnerId == $userId || $role === 'Admin') {
                $isAuthorized = true;
            } elseif ($folderOwner) {
                $opcrRoles = ['Vice President', 'Campus Administrator'];
                $dpcrRoles = ['Dean', 'Director', 'Head of Office'];
                $ipcrRoles = ['Employee'];

                if (in_array($role, $opcrRoles) && in_array($folderOwner['role'], $dpcrRoles)) {
                    $isAuthorized = true;
                } elseif (in_array($role, $dpcrRoles) && in_array($folderOwner['role'], $ipcrRoles) && $folderOwner['department'] === $dept) {
                    $isAuthorized = true;
                }
            }

            if (!$isAuthorized) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unauthorized access.');

            // Fetch the subordinate's documents
            $documentModel = new \App\Models\DocumentModel();
            $myDocs = $documentModel->where('document_folder_id', $subFolderId)->findAll();

            // Return the app_shell but load the Folder View instead of the Ratings list!
            return view('app_shell', [
                'sidebarFolders'   => $folders, // Keeps the master batch selected in sidebar
                'selectedFolderId' => $activeFolder['id'],
                'mainView'         => 'document/_doc_rows', // Swap the view!
                'mainData'         => [
                    'activeFolder'  => $subFolder,
                    'myDocs'        => $myDocs,
                    'groupedGuides' => [], // FIXED: Explicitly empty so guides don't render!
                    'isReadOnly'    => true, 
                    'backUrl'       => site_url('ratings?folder_id=' . $activeFolder['id']) 
                ]
            ]);
        }

        // ==============================================================
        // MODE B: NORMAL RATINGS LIST VIEW
        // ==============================================================
        $data = [
            'activeFolder' => $activeFolder,
            'docHeaders'   => [],
            'userRows'     => [],
            'viewTitle'    => 'Evaluation Dashboard'
        ];
        
        if ($activeFolder) {
            $db = \Config\Database::connect();
            $masterFolderId = $activeFolder['parent_folder_id'] ?? $activeFolder['id'];
            
            $data['docHeaders'] = $db->table('documents')
                ->select('title')
                ->where('document_folder_id', $masterFolderId)
                ->distinct()
                ->get()->getResultArray();
                
            $builder = $db->table('document_folders df')
                ->select("df.id as folder_id, u.id as user_id, (u.first_name || ' ' || u.last_name) as username, 
                        u.position, u.department, u.role, d.title as doc_title, d.id as doc_id, 
                        d.final_rating, d.status, d.updated_at, d.created_at", false) // NEW: Added timestamps
                ->join('users u', 'u.id = df.user_id')
                ->join('documents d', 'd.document_folder_id = df.id', 'left')
                ->where('df.parent_folder_id', $masterFolderId);
                
            if ($role === 'Admin') {
                $data['viewTitle'] = "System-wide Evaluation";
            } elseif (in_array($role, ['Vice President', 'Campus Administrator'])) {
                $builder->whereIn('u.role', ['Dean', 'Director', 'Head of Office']);
                $data['viewTitle'] = "DPCR Evaluation";
            } else {
                $builder->where('u.role', 'Employee')->where('u.department', $dept);
                $data['viewTitle'] = "IPCR Evaluation (" . $dept . ")";
            }
            
            $rawRatings = $builder->get()->getResultArray();
            $userRows = [];
            
            foreach ($rawRatings as $row) {
                if (!isset($userRows[$row['user_id']])) {
                    $userRows[$row['user_id']]['info'] = [
                        'username'   => $row['username'], 
                        'position'   => $row['position'],
                        'department' => $row['department'],
                        'folder_id'  => $row['folder_id'],
                        'role'       => $row['role'],
                        'latest_eval_date' => null,
                        'final_rating'     => null
                    ];
                }
                if ($row['doc_title']) {
                    $userRows[$row['user_id']]['scores'][$row['doc_title']] = [
                        'doc_id' => $row['doc_id'],
                        'rating' => $row['final_rating'],
                        'status' => $row['status']
                    ];
                }
                if ($row['status'] === 'evaluated' && $row['final_rating'] !== null) {
                    $docDate = strtotime($row['updated_at'] ?? $row['created_at'] ?? '1970-01-01');
                    $currentLatest = $userRows[$row['user_id']]['info']['latest_eval_date'];
                    
                    if ($currentLatest === null || $docDate > $currentLatest) {
                        $userRows[$row['user_id']]['info']['latest_eval_date'] = $docDate;
                        $userRows[$row['user_id']]['info']['final_rating'] = $row['final_rating'];
                    }
                }
            }
            $data['userRows'] = $userRows;
        }
        
        return view('app_shell', [
            'sidebarFolders'   => $folders,
            'selectedFolderId' => $activeFolder['id'] ?? null, 
            'mainView'         => 'rating/_show', 
            'mainData'         => $data
        ]);
    }
}