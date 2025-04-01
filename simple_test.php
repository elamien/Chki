<?php
echo "Testing PostgreSQL Connection<br>";

$host = 'db';
$user = 'postgres';
$password = 'postgres';
$dbname = 'postgres';
$port = 5432;

// Try socket connection first to check network connectivity
if ($socket = @fsockopen($host, $port, $errno, $errstr, 3)) {
    echo "✅ Socket connection to $host:$port successful<br>";
    fclose($socket);
} else {
    echo "❌ Socket connection failed: $errstr ($errno)<br>";
}

// Try PDO connection
try {
    echo "Attempting PDO connection...<br>";
    $dsn = "pgsql:host=$host;dbname=$dbname;port=$port";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ PDO connection successful<br>";
    
    $result = $pdo->query("SELECT version()")->fetchColumn();
    echo "PostgreSQL version: $result<br>";
} catch (PDOException $e) {
    echo "❌ PDO connection failed: " . $e->getMessage() . "<br>";
}

// Print environment info
echo "<hr>Environment info:<br>";
echo "PHP version: " . phpversion() . "<br>";
echo "Loaded extensions: " . implode(", ", get_loaded_extensions()) . "<br>";

// Check if PostgreSQL extension is loaded
if (extension_loaded('pgsql')) {
    echo "✅ PostgreSQL extension loaded<br>";
} else {
    echo "❌ PostgreSQL extension not loaded<br>";
}

// Check if PDO PostgreSQL driver is loaded
if (in_array('pgsql', PDO::getAvailableDrivers())) {
    echo "✅ PDO PostgreSQL driver loaded<br>";
} else {
    echo "❌ PDO PostgreSQL driver not loaded<br>";
}

// Check Docker environment
echo "<hr>Docker environment commands to run:<br>";
echo "<pre>
# Check running containers
docker ps

# View PostgreSQL logs
docker logs db_2025

# Check PostgreSQL readiness
docker exec db_2025 pg_isready

# Check PostgreSQL configuration
docker exec db_2025 cat /var/lib/postgresql/data/postgresql.conf | grep listen

# Check network connectivity from web container
docker exec web_2025 ping -c 3 db
</pre>";
?> 