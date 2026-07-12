<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DocumentFolderModel;
use App\Models\EvaluationRoutingModel;
use App\Models\DocumentModel;

/**
 * The "Ratings" dashboard: lets evaluators (Admins, Supervisors, HR) see every
 * subordinate folder they're responsible for, grouped into Action
 * Required / Pending Subordinate / Completed queues, and drill into one folder
 * to actually review and rate it.
 */
class Rating extends BaseController
{
    /**
     * GET /ratings/{folderId} - Builds the evaluator's queue: fetches every folder
     * routed to this evaluator and buckets each into a tab based on its status
     * (still with the employee vs. awaiting this evaluator's action vs. approved).
     */
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

        if (!empty($folders)) {
            $activeFolder = $folderModel->find($folderId);
        }

        $rawFolders = $folderModel->getRatingDashboardFolders($userId, $sysRole);

        foreach ($rawFolders as &$f) {
            if ($f['position'])   $f['position']   = str_replace(',', ', ', $f['position']);
            if ($f['department']) $f['department'] = str_replace(',', ', ', $f['department']);
        }

        unset($f);

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

        $filterUnits = [];
        $filterPositions = [];
        foreach ($rawFolders as $f) {
            if (!empty($f['department'])) {
                foreach (explode(', ', $f['department']) as $d) $filterUnits[trim($d)] = trim($d);
            }
            if (!empty($f['position'])) {
                foreach (explode(', ', $f['position']) as $p) $filterPositions[trim($p)] = trim($p);
            }
        }
        sort($filterUnits);
        sort($filterPositions);

        return view('app_shell', [
            'sidebarFolders'   => $folders,
            'selectedFolderId' => $folderId, 
            'mainView'         => 'ratings/index',
            'mainData'         => [
                'activeFolder'  => $activeFolder ?? null,
                'tabs'    => $tabs,
                'sysRole' => $sysRole,
                'filterUnits'     => $filterUnits,
                'filterPositions' => $filterPositions
            ]
        ]);
    }

    /**
     * GET /ratings/show/{subFolderId} - Opens one subordinate's folder for review,
     * after verifying the viewer is an Admin or a routed evaluator. Deliberately
     * does NOT grant access to the folder's own owner: this route only exists to
     * review someone else's folder, there's no in-app link that ever points an
     * owner at their own folder here (Rating::index()'s dashboard only lists
     * folders you're routed to evaluate), and Folder::index() already gives owners
     * a strictly better - editable, not artificially read-only - view of their
     * own folder. So an owner landing here only ever means they clicked a
     * "Pending Review" email link addressed to their evaluator, not to them.
     * Also gathers "guide" documents from that subordinate's own superiors so the
     * evaluator has the same reference material the employee saw while drafting.
     */
    public function show($subFolderId) {
        $userId  = session()->get('user_id');
        $sysRole = session()->get('role');

        $folderModel = new DocumentFolderModel();
        $routingModel = new EvaluationRoutingModel();
        $documentModel = new DocumentModel();

        $subFolder = $folderModel->find($subFolderId);
        if (!$subFolder) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $isAuthorized = false;

        if ($sysRole === 'Admin') {
            $isAuthorized = true;
        } else {
            $routingCount = $routingModel->where('folder_id', $subFolderId)
                                         ->where('evaluator_id', $userId)
                                         ->countAllResults();
            if ($routingCount > 0) $isAuthorized = true;
        }

        if (!$isAuthorized) {
            // Unlike Folder::index() (a folder always has exactly one owner), this
            // route can legitimately belong to several accounts - a folder may have
            // multiple routed evaluators, and "Pending Review" emails the same link
            // to all of them. There's no single correct account to name here, so the
            // mismatch screen shows its generic wording instead of guessing one.
            session()->setFlashdata('mismatch_detected', true);
            return redirect()->to('account-mismatch');
        }

        $folders = $folderModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        $groupedGuides = [];
        $cascadedRoutes = $routingModel->getEvaluatorsForFolder($subFolderId);

        foreach ($cascadedRoutes as $route) {
            $guideFolder = $folderModel->find($route['evaluator_folder_id']);
            if ($guideFolder) {
                $docs = $documentModel->where('document_folder_id', $guideFolder['id'])->findAll();
                $groupedGuides[] = [
                    'superior' => [
                        'id'   => $route['evaluator_id'],
                        'name' => $route['first_name'] . ' ' . $route['last_name'],
                        'role' => $route['evaluator_position'] ?? 'Evaluator' 
                    ], 
                    'docs' => !empty($docs) ? $docs : [['document_folder_id' => $route['evaluator_folder_id']]] 
                ];
            }
        }

        $mergedGuides = [];
        foreach ($groupedGuides as $guide) {
            $key = $guide['superior']['name']; 
            if (!isset($mergedGuides[$key])) {
                $mergedGuides[$key] = $guide;
            } else {
                $existingRoles = $mergedGuides[$key]['superior']['role'];
                $newRole       = $guide['superior']['role'];
                if (strpos($existingRoles, $newRole) === false) {
                    $mergedGuides[$key]['superior']['role'] .= ', ' . $newRole;
                }
            }
        }
        $groupedGuides = array_values($mergedGuides);

        return view('app_shell', [
            'sidebarFolders'   => $folders, 
            'selectedFolderId' => null, 
            'mainView'         => 'document/_doc_rows', 
            'mainData'         => [
                'activeFolder'  => $subFolder,
                'myDocs'        => $documentModel->where('document_folder_id', $subFolderId)->findAll(),
                'isReadOnly'    => true, 
                'presets'       => [],
                'groupedGuides' => $groupedGuides
            ]
        ]);
    }
}