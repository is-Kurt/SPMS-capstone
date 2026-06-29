<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\UnitModel;
use App\Models\PositionModel;
use App\Models\PlantillaModel;

class Profile extends BaseController
{
    public function index()
    {
        $userId = session()->get('user_id');
        $userModel      = new UserModel();
        $unitModel      = new UnitModel();
        $positionModel  = new PositionModel();
        $plantillaModel = new PlantillaModel();
        
        $currentPlantilla = $plantillaModel->where('user_id', $userId)->where('ended_at IS NULL')->findAll();
        
        if (empty($currentPlantilla)) {
            $currentPlantilla = [['unit_id' => '', 'position_id' => '']];
        }

        $data = [
            'user'             => $userModel->find($userId),
            'units'            => $unitModel->orderBy('name', 'ASC')->findAll(),
            'positions'        => $positionModel->orderBy('title', 'ASC')->findAll(),
            'currentPlantilla' => $currentPlantilla
        ];

        return view('profile', $data);
    }

    public function updateGeneral()
    {
        $userId = session()->get('user_id');
        $userModel      = new UserModel();
        $plantillaModel = new PlantillaModel();

        $rules = [
            'first_name' => 'required|min_length[2]',
            'last_name'  => 'required|min_length[2]',
            'email'      => "required|valid_email|is_unique[users.email,id,{$userId}]"
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your inputs and try again.');
        }

        $userModel->db->transStart();

        $userModel->update($userId, [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
        ]);

        $plantillaModel->where('user_id', $userId)->where('ended_at IS NULL')->delete();

        $units     = $this->request->getPost('units');
        $positions = $this->request->getPost('positions');

        if (!empty($units) && !empty($positions)) {
            for ($i = 0; $i < count($units); $i++) {
                if (!empty($units[$i]) && !empty($positions[$i])) {
                    $plantillaModel->insert([
                        'user_id'     => $userId,
                        'unit_id'     => $units[$i],
                        'position_id' => $positions[$i],
                        'created_at'  => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        $userModel->db->transComplete();

        session()->set([
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'username'   => $this->request->getPost('first_name') . ' ' . $this->request->getPost('last_name')
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[3]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your password inputs.');
        }

        $currentPassword = $this->request->getPost('current_password');
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->withInput()->with('errors', [
                'current_password' => 'The current password you entered is incorrect.'
            ]);
        }

        $newPassword = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
        $userModel->update($userId, ['password' => $newPassword]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    public function updateAvatar() {
        $userModel = new \App\Models\UserModel();
        $userId = session()->get('user_id');
        $uploadPath = FCPATH . 'uploads/avatars/'; // Define path once here

        // 1. Check if they uploaded an image
        $file = $this->request->getFile('avatar_file');
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
                return redirect()->back()->with('error', 'Invalid image format.');
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

            return redirect()->back()->with('success', 'Profile image updated!');
        }

        // 2. If no image, check if they are updating the Initials/Color
        $color = $this->request->getPost('avatar_color');
        $letter = strtoupper(substr($this->request->getPost('avatar_letter'), 0, 1));
        $removeImage = $this->request->getPost('remove_image');

        $updateData = [];
        if ($color) $updateData['avatar_color'] = $color;
        if ($letter) $updateData['avatar_letter'] = $letter;
        
        if ($removeImage == '1') {
            // FIX: Actually delete the physical file when "Remove Image" is clicked!
            $oldAvatar = session('avatar_image');
            if ($oldAvatar && file_exists($uploadPath . $oldAvatar)) {
                unlink($uploadPath . $oldAvatar);
            }

            $updateData['avatar_image'] = null;
            session()->set('avatar_image', null);
        }

        if (!empty($updateData)) {
            $userModel->update($userId, $updateData);
            if(isset($updateData['avatar_color'])) session()->set('avatar_color', $updateData['avatar_color']);
            if(isset($updateData['avatar_letter'])) session()->set('avatar_letter', $updateData['avatar_letter']);
        }

        return redirect()->back()->with('success', 'Avatar settings updated!');
    }
}