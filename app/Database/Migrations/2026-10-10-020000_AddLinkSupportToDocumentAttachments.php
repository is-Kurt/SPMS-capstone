<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLinkSupportToDocumentAttachments extends Migration
{
    public function up()
    {
        $fields = [
            'is_link' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'file_size',
            ],
            'link_url' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'is_link',
            ],
        ];

        $this->forge->addColumn('document_attachments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('document_attachments', ['is_link', 'link_url']);
    }
}
