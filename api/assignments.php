<?php
require_once '../db_connect.php';
session_start();

// Set the content type to JSON
header('Content-Type: application/json');

// Function to send JSON response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendResponse(['error' => 'Unauthorized access. Please login first.'], 401);
}

// Get database connection
$pdo = getDbConnection();
if (!$pdo) {
    sendResponse(['error' => 'Database connection failed'], 500);
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all assignments or a specific one
        $assignmentId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        try {
            if ($assignmentId) {
                // Get specific assignment
                $stmt = $pdo->prepare("
                    SELECT * FROM assignments 
                    WHERE assignment_id = ? AND user_id = ?
                ");
                $stmt->execute([$assignmentId, $_SESSION['user_id']]);
                $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($assignment) {
                    sendResponse($assignment);
                } else {
                    sendResponse(['error' => 'Assignment not found'], 404);
                }
            } else {
                // Get all assignments with optional filtering
                $query = "SELECT * FROM assignments WHERE user_id = ?";
                $params = [$_SESSION['user_id']];
                
                // Filter by course if provided
                if (isset($_GET['course']) && !empty($_GET['course'])) {
                    $query .= " AND course = ?";
                    $params[] = $_GET['course'];
                }
                
                // Filter by priority if provided
                if (isset($_GET['priority']) && !empty($_GET['priority'])) {
                    $query .= " AND priority = ?";
                    $params[] = $_GET['priority'];
                }
                
                // Add ordering
                $query .= " ORDER BY due_date ASC";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                sendResponse(['assignments' => $assignments, 'count' => count($assignments)]);
            }
        } catch (PDOException $e) {
            sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
        break;
        
    case 'POST':
        // Create a new assignment
        $data = json_decode(file_get_contents('php://input'), true);
        
        // If no data was sent as JSON, check for form data
        if (!$data) {
            $data = $_POST;
        }
        
        // Validate required fields
        if (empty($data['title']) || empty($data['course']) || empty($data['due_date'])) {
            sendResponse(['error' => 'Missing required fields (title, course, due_date)'], 400);
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO assignments (user_id, title, description, course, due_date, priority)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $success = $stmt->execute([
                $_SESSION['user_id'],
                $data['title'],
                $data['description'] ?? '',
                $data['course'],
                $data['due_date'],
                $data['priority'] ?? 'medium'
            ]);
            
            if ($success) {
                $newId = $pdo->lastInsertId();
                sendResponse(['message' => 'Assignment created successfully', 'id' => $newId], 201);
            } else {
                sendResponse(['error' => 'Failed to create assignment'], 500);
            }
        } catch (PDOException $e) {
            sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
        break;
        
    case 'PUT':
        // Update an existing assignment
        $assignmentId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$assignmentId) {
            sendResponse(['error' => 'Assignment ID is required'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($data)) {
            sendResponse(['error' => 'No data provided for update'], 400);
        }
        
        try {
            // First check if assignment exists and belongs to user
            $stmt = $pdo->prepare("SELECT * FROM assignments WHERE assignment_id = ? AND user_id = ?");
            $stmt->execute([$assignmentId, $_SESSION['user_id']]);
            
            if (!$stmt->fetch()) {
                sendResponse(['error' => 'Assignment not found or not authorized'], 404);
            }
            
            // Build the update query dynamically based on provided fields
            $updateFields = [];
            $updateParams = [];
            
            if (isset($data['title'])) {
                $updateFields[] = "title = ?";
                $updateParams[] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateParams[] = $data['description'];
            }
            
            if (isset($data['course'])) {
                $updateFields[] = "course = ?";
                $updateParams[] = $data['course'];
            }
            
            if (isset($data['due_date'])) {
                $updateFields[] = "due_date = ?";
                $updateParams[] = $data['due_date'];
            }
            
            if (isset($data['priority'])) {
                $updateFields[] = "priority = ?";
                $updateParams[] = $data['priority'];
            }
            
            // If no fields to update
            if (empty($updateFields)) {
                sendResponse(['message' => 'No changes to make'], 200);
            }
            
            // Add assignment ID and user ID to parameters
            $updateParams[] = $assignmentId;
            $updateParams[] = $_SESSION['user_id'];
            
            $query = "UPDATE assignments SET " . implode(", ", $updateFields) . 
                     " WHERE assignment_id = ? AND user_id = ?";
            
            $stmt = $pdo->prepare($query);
            $success = $stmt->execute($updateParams);
            
            if ($success) {
                sendResponse(['message' => 'Assignment updated successfully']);
            } else {
                sendResponse(['error' => 'Failed to update assignment'], 500);
            }
        } catch (PDOException $e) {
            sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
        break;
        
    case 'DELETE':
        // Delete an assignment
        $assignmentId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$assignmentId) {
            sendResponse(['error' => 'Assignment ID is required'], 400);
        }
        
        try {
            $stmt = $pdo->prepare("
                DELETE FROM assignments
                WHERE assignment_id = ? AND user_id = ?
            ");
            
            $success = $stmt->execute([$assignmentId, $_SESSION['user_id']]);
            
            if ($success && $stmt->rowCount() > 0) {
                sendResponse(['message' => 'Assignment deleted successfully']);
            } else {
                sendResponse(['error' => 'Assignment not found or not authorized'], 404);
            }
        } catch (PDOException $e) {
            sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
        break;
        
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
        break;
}
?> 