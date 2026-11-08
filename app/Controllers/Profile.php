<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\UnitModel;
use App\Models\PositionModel;
use App\Models\PlantillaModel;
use App\Models\EmploymentStatusModel;

/**
 * The logged-in user's own "My Profile" page: editing the name and (where the rules
 * allow a choice) the form type, seeing their assignments, changing password, and
 * managing the avatar (uploaded image or generated color+initial).
 */
class Profile extends BaseController
{
    /** GET /profile */
    public function index()
    {
        $userId = (int) session()->get('user_id');
        $userModel = new UserModel();
        $org = \App\Libraries\OrgStructure::instance();
        $user = $userModel->find($userId);
        $role = $org->roleOf($userId);

        // Assignments are set by the admin (invite + User Directory); the profile only shows them.
        $assignments = \Config\Database::connect()->table('plantillas p')
            ->select('p.unit_id, p.position_id, un.name as unit_name, pos.title as position_title, p.started_at')
            ->join('units un', 'un.id = p.unit_id', 'left')->join('positions pos', 'pos.id = p.position_id', 'left')
            ->where('p.user_id', $userId)->where('p.ended_at IS NULL')->orderBy('p.id', 'ASC')->get()->getResultArray();
        foreach ($assignments as &$a) {
            $a['is_head'] = $a['unit_id'] && $a['position_id'] && $org->isHeadOf((int) $a['unit_id'], (int) $a['position_id']);
        }
        unset($a);

        $allowed = $org->allowedDocTypesForUser($userId);
        $current = strtoupper((string) ($user['doc_type'] ?? ''));

        return view('profile/index', [
            'user'          => $user,
            'assignments'   => $assignments,
            'isRated'       => \App\Libraries\OrgStructure::isRatedRole($role),
            'allowedDocs'   => $allowed,
            'currentDoc'    => $current,
            'docFlagged'    => $current === '' || !in_array($current, $allowed, true),
            'docLocked'     => own_doc_type_locked($userId),
            'isAdmin'       => $role === 'Admin',
            'isTwg'         => $role === 'TWG',
            'isPmt'         => $role === 'PMT',
        ]);
    }

    /**
     * POST /profile/general - Updates the name and, where the rules allow a choice, the doc type.
     * Department, position and employment status are the admin's (User Directory), so they are not
     * accepted here. A doc type the rules no longer allow can be kept (the admin is flagged to fix it)
     * but not newly chosen; the paper sync only runs when the doc type really changes.
     */
    public function updateGeneral()
    {
        return $this->tryOrFail(function() {
            $userId = (int) session()->get('user_id');
            $userModel = new UserModel();
            $org = \App\Libraries\OrgStructure::instance();
            $existingUser = $userModel->find($userId);
            $current = strtoupper((string) ($existingUser['doc_type'] ?? ''));
            $newDocType = $current;

            if (\App\Libraries\OrgStructure::isRatedRole($org->roleOf($userId))) {
                $allowed = $org->allowedDocTypesForUser($userId);
                $posted = strtoupper(trim((string) $this->request->getPost('doc_type')));
                if ($posted !== '' && $posted !== $current) {
                    if (own_doc_type_locked($userId)) {
                        return $this->respondError('Your form type is locked: you have papers past drafting this cycle. Ask your supervisor or the administrator to change it.', 422, ['doc_type' => 'Locked: ask your supervisor.']);
                    }
                    if (!in_array($posted, $allowed, true)) {
                        return $this->respondError('That form type is not allowed for your role and employment status.', 422, ['doc_type' => 'Not allowed for your role and employment status.']);
                    }
                    $newDocType = $posted;
                } elseif ($current === '' && count($allowed) === 1) {
                    $newDocType = $allowed[0];                         // nothing set yet and only one choice
                }
            }

            $saved = $userModel->update($userId, [
                'first_name' => $this->request->getPost('first_name'),
                'last_name'  => $this->request->getPost('last_name'),
                'doc_type'   => $newDocType !== '' ? $newDocType : null,
            ]);
            if (!$saved) {
                $errors = $userModel->errors();
                return $this->respondError(implode(' ', $errors), 422, $errors);
            }

            $user = $userModel->find($userId);
            session()->set([
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'email'      => $user['email'],
                'username'   => $user['first_name'] . ' ' . $user['last_name'],
                'doc_type'   => $user['doc_type'],
            ]);

            if ($newDocType !== $current && $newDocType !== '') {
                // The person chose another allowed form type: convert their target drafts to it.
                sync_user_papers_mid_target_setting($userId, $newDocType);
                audit_log('DOC_TYPE_CHANGED', 'ACCOUNT', 'user', $userId, "Changed own form type from " . ($current ?: 'none') . " to {$newDocType}");
            }

            return $this->respond(['status' => 'success', 'message' => 'Profile updated successfully.']);
        });
    }

    /**
     * POST /profile/avatar - Two modes in one endpoint: if an image file was
     * uploaded, resize/convert it to WebP and use it as the avatar; otherwise
     * fall back to updating the generated color+initial avatar, or clearing the
     * image entirely if "remove image" was requested.
     */
    public function updateAvatar() {
        return $this->tryOrFail(function() {
            $userModel = new \App\Models\UserModel();
            $userId = session()->get('user_id');
            $uploadPath = FCPATH . 'uploads/avatars/'; // Define path once here

            // 1. Check if they uploaded an image
            $file = $this->request->getFile('avatar_file');

            if ($file && $file->isValid() && !$file->hasMoved()) {
                if ($file->getSize() > 10485760) {
                    return $this->respondError('Image must be less than 10MB.', 422, ['avatar_file' => 'Image must be less than 10MB.']);
                }

                if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
                    return $this->respondError('Invalid image format.', 422, ['avatar_file' => 'Invalid image format.']);
                }

                if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

                $newName = $file->getRandomName();
                $newNameWebp = pathinfo($newName, PATHINFO_FILENAME) . '.webp';

                \Config\Services::image()
                    ->withFile($file)
                    ->fit(256, 256, 'center')
                    ->convert(IMAGETYPE_WEBP)
                    ->save($uploadPath . $newNameWebp, 80);

                // Delete old avatar if exists
                $oldAvatar = session('avatar_image');
                if ($oldAvatar && file_exists($uploadPath . $oldAvatar)) {
                    unlink($uploadPath . $oldAvatar);
                }

                $userModel->update($userId, ['avatar_image' => $newNameWebp]);
                session()->set('avatar_image', $newNameWebp);

                return $this->respond([
                    'status'  => 'success',
                    'message' => 'Profile image updated!',
                    'avatar'  => ['image' => base_url('uploads/avatars/' . $newNameWebp)],
                ]);
            }

            // 2. If no image, check if they are updating the Initials/Color
            $color = $this->request->getPost('avatar_color');
            $letter = strtoupper(substr($this->request->getPost('avatar_letter'), 0, 1));
            $removeImage = $this->request->getPost('remove_image');

            $updateData = [];
            if ($color) $updateData['avatar_color'] = $color;
            if ($letter) $updateData['avatar_letter'] = $letter;

            if ($removeImage == '1') {
                // Actually delete the physical file when "Remove Image" is clicked
                $oldAvatar = session('avatar_image');
                if ($oldAvatar && file_exists($uploadPath . $oldAvatar)) {
                    unlink($uploadPath . $oldAvatar);
                }

                $updateData['avatar_image'] = null;
                session()->set('avatar_image', null);
            }

            if (!empty($updateData)) {
                $userModel->update($userId, $updateData);
                if (isset($updateData['avatar_color'])) session()->set('avatar_color', $updateData['avatar_color']);
                if (isset($updateData['avatar_letter'])) session()->set('avatar_letter', $updateData['avatar_letter']);
            }

            return $this->respond([
                'status'  => 'success',
                'message' => 'Avatar settings updated!',
                'avatar'  => [
                    'image'  => has_avatar_file(session('avatar_image')) ? base_url('uploads/avatars/' . session('avatar_image')) : null,
                    'color'  => session('avatar_color'),
                    'letter' => session('avatar_letter'),
                ],
            ]);
        });
    }

    /**
     * GET /uploads/avatars/{filename}
     * Dynamic fallback when an avatar file was accidentally removed or missing from server disk.
     * Serves an SVG with the user's initial and color instead of a broken image (404).
     */
    public function avatarFallback(string $filename = '')
    {
        $cleanName = basename(trim($filename));
        $filePath = FCPATH . 'uploads/avatars/' . $cleanName;

        if ($cleanName !== '' && is_file($filePath)) {
            $mime = mime_content_type($filePath) ?: 'image/webp';
            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Cache-Control', 'public, max-age=86400')
                ->setBody(file_get_contents($filePath));
        }

        $userModel = new UserModel();
        $user = $cleanName !== '' ? $userModel->where('avatar_image', $cleanName)->first() : null;

        $color = $user['avatar_color'] ?? session('avatar_color') ?? '#5b3704';
        $letter = $user['avatar_letter'] ?? session('avatar_letter') ?? '';
        if (empty($letter)) {
            $name = $user['first_name'] ?? session('username') ?? 'U';
            $letter = strtoupper(substr($name, 0, 1)) ?: 'A';
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128">'
             . '<circle cx="64" cy="64" r="64" fill="' . esc($color, 'attr') . '"/>'
             . '<text x="50%" y="54%" font-family="system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif" font-weight="900" font-size="56" fill="#ffffff" text-anchor="middle" dominant-baseline="middle">'
             . esc($letter)
             . '</text>'
             . '</svg>';

        return $this->response
            ->setHeader('Content-Type', 'image/svg+xml; charset=utf-8')
            ->setHeader('Cache-Control', 'no-cache, private')
            ->setBody($svg);
    }
}