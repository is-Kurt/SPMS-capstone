<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
function adminer_object() {
    class AdminerSoftware extends Adminer {
        function login($login, $password) {
            $isAuth = false;
            
            // Check password
            if ($password === 'admin123') {
                $isAuth = true;
                $_SESSION['sqlite_admin_auth'] = true;
            } elseif (isset($_SESSION['sqlite_admin_auth']) && $_SESSION['sqlite_admin_auth'] === true) {
                $isAuth = true;
            }
            
            if ($isAuth) {
                // Wipe the password so the SQLite driver doesn't throw "Database does not support password"
                if (isset($_POST['auth']['password'])) {
                    $_POST['auth']['password'] = '';
                }
                return true;
            }
            
            return false;
        }
        
        function credentials() {
            return array('localhost', '', '');
        }
        
        function database() {
            return '../writable/spms_db';
        }
        
        function loginForm() {
            ?>
            <table cellspacing="0">
            <tr><th>Database path<td><input name="auth[db]" value="../writable/spms_db" autocapitalize="off">
            <tr><th>Password<td><input type="password" name="auth[password]">
            </table>
            <p><input type="hidden" name="auth[driver]" value="sqlite"><input type="submit" value="Login">
            <?php
            return true;
        }
    }
    return new AdminerSoftware;
}

include "./adminer.php";
