<?php
// Very simple, direct PostgreSQL connection test
echo "<h1>Database Connection Test</h1>";

// Hardcoded credentials for direct test
$host = "db";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "postgres";

echo "<p>Attempting to connect to PostgreSQL at $host:$port as $user...</p>";

// Test using both pgsql extension and PDO
echo "<h2>Testing PDO Connection:</h2>";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green; font-weight: bold;'>PDO Connection successful!</p>";
    
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "<p>PostgreSQL version: $version</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>PDO Connection failed!</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

// Try a socket connection to see if PostgreSQL server is actually reachable
echo "<h2>Testing Socket Connection:</h2>";
$socket = @fsockopen($host, $port, $errno, $errstr, 5);
if ($socket) {
    echo "<p style='color: green;'>Socket connection successful!</p>";
    fclose($socket);
} else {
    echo "<p style='color: red;'>Socket connection failed: $errstr ($errno)</p>";
    echo "<p>This suggests the PostgreSQL server is not reachable from PHP.</p>";
}

// Docker inspection commands
echo "<h2>Docker Environment Information:</h2>";
echo "<p>Please run these commands in your terminal to get more information:</p>";
echo "<pre>
docker ps
docker inspect db_2025
docker network inspect cs4640-dev-environment-s25v1_default
docker-compose logs db
</pre>";

echo "<h2>Possible Solutions:</h2>";
echo "<ol>";
echo "<li>Make sure the PostgreSQL container is actually running: <code>docker ps | grep db</code></li>";
echo "<li>Try restarting the containers: <code>docker-compose down && docker-compose up -d</code></li>";
echo "<li>Check if PostgreSQL is listening on port 5432 inside the container: <code>docker exec -it db_2025 pg_isready</code></li>";
echo "<li>Examine your docker-compose.yml file to ensure the database service is correctly configured</li>";
echo "</ol>";
?> 