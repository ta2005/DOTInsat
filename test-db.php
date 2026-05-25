<?php
require_once __DIR__ . '/config/db_connect.php';

echo "<h1>Database Connection Test</h1>";

if ($pdo === null) {
    echo "<p style='color:red'><strong>❌ Database connection FAILED</strong></p>";
} else {
    echo "<p style='color:green'><strong>✅ Database connection SUCCESS</strong></p>";

    // Try to fetch test data
    try {
        $stmt = $pdo->query("SELECT id, nom FROM enseignement LIMIT 5");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Test Courses from Database:</h2>";
        echo "<ul>";
        foreach ($courses as $course) {
            echo "<li>ID: {$course['id']} - {$course['nom']}</li>";
        }
        echo "</ul>";

        // Test professeur data
        $stmt = $pdo->query("SELECT id FROM professeur LIMIT 5");
        $profs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p><strong>Professors found:</strong> " . count($profs) . "</p>";

    } catch (Exception $e) {
        echo "<p style='color:red'><strong>Query Error:</strong> " . $e->getMessage() . "</p>";
    }
}
?>