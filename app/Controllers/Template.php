<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TemplateModel;

/**
 * Admin-only "Templates": the reusable IPCR/DPCR/OPCR content blueprints that
 * can be picked when creating a new Document (see Document::store()).
 */
class Template extends BaseController
{
    /** GET /templates - Lists every template, newest first. */
    public function index() {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');

        $templateModel = new TemplateModel();

        $data = [
            'templates' => $templateModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('templates/index', $data);
    }

    /** GET /templates/create - Opens the editor with a blank template. */
    public function create() {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');
        return view('templates/editor', ['template' => null]);
    }

    /** GET /templates/edit/{id} - Opens the editor pre-loaded with an existing template's content. */
    public function edit($id) {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');

        $templateModel = new TemplateModel();
        $template = $templateModel->find($id);

        if (!$template) {
            return redirect()->to('templates')->with('error', 'Template not found.');
        }

        return view('templates/editor', ['template' => $template]);
    }

    /** POST /templates/store - Creates a new template, or updates one if template_id is present. */
    public function store() {
        if (session()->get('role') !== 'Admin') return redirect()->to('/');

        $templateModel = new TemplateModel();
        $templateId = $this->request->getPost('template_id');

        // 1. Get the title or set the default
        $title = trim($this->request->getPost('title'));
        if (empty($title)) {
            $title = 'Basic template';
        }

        if ($templateId) {
            // Update: Resolve unique title, ignoring the current template's ID
            $title = resolve_unique_title($title, function($model) use ($templateId) {
                $model->where('id !=', $templateId);
            }, 'title', $templateModel);

            $data = [
                'title'   => $title,
                'content' => $this->request->getPost('content') ?? ''
            ];
            $templateModel->update($templateId, $data);
            $msg = 'Template updated successfully.';
        } else {
            // Creation: Resolve unique title against all templates
            $title = resolve_unique_title($title, [], 'title', $templateModel);

            $data = [
                'title'   => $title,
                'content' => $this->request->getPost('content') ?? ''
            ];
            $templateModel->save($data);
            $msg = 'New template created successfully.';
        }

        return redirect()->to('templates')->with('success', $msg);
    }

    /** POST /templates/delete */
    public function delete() {
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
