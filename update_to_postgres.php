<?php
/**
 * This script updates all PHP files in the current directory to use PDO with PostgreSQL
 * instead of mysqli. Run this script once to convert your codebase.
 */

// Directory to scan for PHP files
$directory = './';

// Get all PHP files
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
);

$phpFiles = [];
foreach ($files as $file) {
    if ($file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

// Patterns to search and replace
$patterns = [
    // Database connection
    '/mysqli_connect$$(.*?)$$/' => 'new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password")',
    
    // Query execution
    '/mysqli_query$$\$conn,\s*"(.*?)"$$/' => '$conn->query("$1")',
    
    // Prepared statements
    '/\$stmt\s*=\s*mysqli_prepare$$\$conn,\s*"(.*?)"$$/' => '$stmt = $conn->prepare("$1")',
    
    // Bind parameters
    '/mysqli_stmt_bind_param$$\$stmt,\s*"(.*?)",\s*(.*?)$$/' => '$stmt->bindParam($2)',
    
    // Execute statement
    '/mysqli_stmt_execute$$\$stmt$$/' => '$stmt->execute()',
    
    // Fetch results
    '/mysqli_fetch_assoc$$\$(.*?)$$/' => '$$1->fetch()',
    
    // Number of rows
    '/mysqli_num_rows$$\$(.*?)$$/' => '$$1->rowCount()',
    
    // Last insert ID
    '/mysqli_insert_id$$\$conn$$/' => '$conn->lastInsertId()',
    
    // Error handling
    '/mysqli_error$$\$conn$$/' => '$conn->errorInfo()[2]',
    
    // Close connection
    '/mysqli_close$$\$conn$$/' => '// PDO connections close automatically when the script ends',
    
    // Real escape string
    '/mysqli_real_escape_string$$\$conn,\s*(.*?)$$/' => '$1',
    
    // AUTO_INCREMENT to SERIAL
    '/AUTO_INCREMENT/' => 'SERIAL',
    
    // INT to INTEGER
    '/int$$(\d+)$$/' => 'INTEGER',
    
    // ENUM to custom type
    '/ENUM$$(.*?)$$/' => 'TEXT CHECK (value IN ($1))',
];

// Process each file
foreach ($phpFiles as $file) {
    // Skip this script
    if (basename($file) === basename(__FILE__)) {
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Apply replacements
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Save the updated file
    file_put_contents($file, $content);
    
    echo "Updated: $file\n";
}

echo "All PHP files have been updated to use PDO with PostgreSQL.\n";
?>