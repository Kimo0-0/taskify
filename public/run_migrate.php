<?php
/**
 * TEMPORARY MIGRATION HELPER - DELETE AFTER USE
 * Access: http://task_management_api.local/run_migrate.php
 */

// Basic security: only allow from localhost
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
    die('Access denied.');
}

chdir(dirname(__DIR__));

// Check columns existence first
$host     = '127.0.0.1';
$db       = 'tasks';
$user     = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $cols = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    foreach ($stmt as $row) {
        $cols[] = $row['Field'];
    }

    echo "<h2>Users table columns:</h2><pre>" . implode(', ', $cols) . "</pre>";

    $needsMigration = !in_array('share_token', $cols);
    echo "<p>Needs migration: " . ($needsMigration ? '<b style="color:red">YES</b>' : '<b style="color:green">NO - Already done</b>') . "</p>";

    if ($needsMigration) {
        // Run the migration manually
        $pdo->exec("ALTER TABLE users ADD COLUMN share_token VARCHAR(255) NULL UNIQUE AFTER profile_image");
        $pdo->exec("ALTER TABLE users ADD COLUMN share_can_edit TINYINT(1) NOT NULL DEFAULT 0 AFTER share_token");
        $pdo->exec("ALTER TABLE users ADD COLUMN share_can_complete TINYINT(1) NOT NULL DEFAULT 0 AFTER share_can_edit");
        echo "<p style='color:green'><b>✓ Added share_token, share_can_edit, share_can_complete to users table!</b></p>";
    }

    // Check tasks table
    $taskCols = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM tasks");
    foreach ($stmt as $row) {
        $taskCols[] = $row['Field'];
    }
    echo "<h2>Tasks table columns:</h2><pre>" . implode(', ', $taskCols) . "</pre>";

    $taskNeedsMigration = !in_array('share_token', $taskCols);
    echo "<p>Tasks needs migration: " . ($taskNeedsMigration ? '<b style="color:red">YES</b>' : '<b style="color:green">NO</b>') . "</p>";

    if ($taskNeedsMigration) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN share_token VARCHAR(255) NULL UNIQUE AFTER user_id");
        $pdo->exec("ALTER TABLE tasks ADD COLUMN share_can_edit TINYINT(1) NOT NULL DEFAULT 0 AFTER share_token");
        $pdo->exec("ALTER TABLE tasks ADD COLUMN share_can_complete TINYINT(1) NOT NULL DEFAULT 0 AFTER share_can_edit");
        echo "<p style='color:green'><b>✓ Added share columns to tasks table!</b></p>";
    }

    // Check category_shares table
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    foreach ($stmt as $row) {
        $tables[] = array_values($row)[0];
    }
    echo "<h2>Tables:</h2><pre>" . implode(', ', $tables) . "</pre>";

    if (!in_array('category_shares', $tables)) {
        $pdo->exec("CREATE TABLE category_shares (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            share_token VARCHAR(255) NOT NULL UNIQUE,
            can_edit TINYINT(1) NOT NULL DEFAULT 0,
            can_complete TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        )");
        echo "<p style='color:green'><b>✓ Created category_shares table!</b></p>";

        // Mark migration as done in migrations table
        $pdo->exec("INSERT INTO migrations (migration, batch) VALUES ('2026_07_05_120000_add_sharing_to_tasks_and_categories', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations AS m2))");
        echo "<p style='color:green'><b>✓ Marked migration as done!</b></p>";
    }

    echo "<br><p style='color:green; font-size:1.2em;'><b>✓ Migration complete! You can delete this file now.</b></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
