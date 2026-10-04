<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class NotificationControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $dbConfig = config('Database');
        $dbConfig->tests['database'] = WRITEPATH . 'database/spms_db.sqlite3';
        $dbConfig->tests['DBPrefix'] = '';
        $dbConfig->tests['DBDriver'] = 'SQLite3';
    }

    public function testNotificationPageRendersForAuthenticatedUser(): void
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('is_active', 1)->first();
        $this->assertNotNull($user, 'At least one active user must exist');

        $result = $this->withSession([
            'user_id'    => (int) $user['id'],
            'email'      => $user['email'],
            'username'   => $user['first_name'] . ' ' . $user['last_name'],
            'role'       => 'Admin',
            'isLoggedIn' => true,
        ])->get('notifications');

        $result->assertStatus(200);
        $result->assertSee('Notifications');
        $result->assertSee('notifications-feed');
        $result->assertSee('ACTIVITY CENTER');
    }

    public function testNotificationApiReturnsJsonForAjax(): void
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('is_active', 1)->first();
        $this->assertNotNull($user, 'At least one active user must exist');

        $result = $this->withSession([
            'user_id'    => (int) $user['id'],
            'email'      => $user['email'],
            'username'   => $user['first_name'] . ' ' . $user['last_name'],
            'role'       => 'Admin',
            'isLoggedIn' => true,
        ])->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
        ])->get('notifications');

        $result->assertStatus(200);
        $result->assertJSONFragment([
            'status' => 'success',
        ]);
    }
}
