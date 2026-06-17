<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TemplateModel;

class Template extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');

        $templateModel = new TemplateModel();
        
        $data = [
            'templates' => $templateModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('templates/index', $data);
    }

    // NEW: Load blank editor
    public function create()
    {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');
        return view('templates/editor', ['template' => null]);
    }

    // NEW: Load populated editor
    public function edit($id)
    {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');
        
        $templateModel = new TemplateModel();
        $template = $templateModel->find($id);
        
        if (!$template) {
            return redirect()->to('templates')->with('error', 'Template not found.');
        }

        return view('templates/editor', ['template' => $template]);
    }

    public function store()
    {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');

        $rules = [
            'title'   => 'required|min_length[3]',
            // Removed content requirement so they can save blank templates if they want
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'A valid Template Title is required.');
        }

        $templateModel = new TemplateModel();
        $templateId = $this->request->getPost('template_id');

        $data = [
            'title'   => $this->request->getPost('title'),
            'content' => $this->request->getPost('content') ?? ''
        ];

        if ($templateId) {
            $templateModel->update($templateId, $data);
            $msg = 'Template updated successfully.';
        } else {
            $templateModel->save($data);
            $msg = 'New template created successfully.';
        }

        // Redirect back to the library
        return redirect()->to('templates')->with('success', $msg);
    }

    public function delete()
    {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');

        $templateId = $this->request->getPost('template_id');
        
        if ($templateId) {
            $templateModel = new TemplateModel();
            $templateModel->delete($templateId);
            return redirect()->back()->with('success', 'Template deleted successfully.');
        }

        return redirect()->back()->with('error', 'Template not found.');
    }
}
