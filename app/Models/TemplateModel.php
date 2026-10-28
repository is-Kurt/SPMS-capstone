<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Legacy TemplateModel - Table 'templates' has been dropped in favor of
 * dynamic view templates (app/Views/document/templates/) and DocType enum.
 * This class provides backward-compatible fallback mocks without querying the database.
 */
class TemplateModel extends Model
{
    protected $table = 'templates';

    public function findAll(?int $limit = null, int $offset = 0)
    {
        return [
            ['id' => 1, 'title' => 'IPCR', 'tabs' => []],
            ['id' => 2, 'title' => 'DPCR', 'tabs' => []],
            ['id' => 3, 'title' => 'OPCR', 'tabs' => []],
            ['id' => 4, 'title' => 'IPERF', 'tabs' => []],
        ];
    }

    public function find($id = null)
    {
        helper('functions');
        $map = [
            1 => 'IPCR',
            2 => 'DPCR',
            3 => 'OPCR',
            4 => 'IPERF',
            'IPCR' => 'IPCR',
            'DPCR' => 'DPCR',
            'OPCR' => 'OPCR',
            'IPERF' => 'IPERF',
        ];
        $type = $map[$id] ?? (is_string($id) ? strtoupper($id) : null);
        if ($type && function_exists('get_default_form_template')) {
            return [
                'id'    => $id,
                'title' => $type,
                'tabs'  => get_default_form_template($type),
            ];
        }
        return null;
    }

    public function first()
    {
        return null;
    }

    public function where($key, $value = null, ?bool $escape = null)
    {
        return $this;
    }

    public function like($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
    {
        return $this;
    }
}
