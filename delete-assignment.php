<?php
require_once 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if assignment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$assignmentId = (int)$_GET['id'];
$success = false;
$message = '';

// Delete the assignment
$pdo = getDbConnection();

if ($pdo) {
    try {
        // First, verify this assignment belongs to the logged-in user
        $stmt = $pdo->prepare("
            SELECT * FROM assignments 
            WHERE assignment_id = ? AND user_id = ?
        ");
        $stmt->execute([$assignmentId, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            // If assignment belongs to user, delete it
            $deleteStmt = $pdo->prepare("
                DELETE FROM assignments
                WHERE assignment_id = ? AND user_id = ?
            ");
            
            $success = $deleteStmt->execute([$assignmentId, $_SESSION['user_id']]);
            
            if ($success) {
                $message = "Assignment deleted successfully.";
            } else {
                $message = "Failed to delete assignment.";
            }
        } else {
            $message = "Assignment not found or you don't have permission to delete it.";
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
} else {
    $message = "Could not connect to the database.";
}

// Save message in session to display after redirect
$_SESSION['flash_message'] = $message;
$_SESSION['flash_type'] = $success ? 'success' : 'danger';

// Redirect back to homepage
header("Location: index.php");
exit();
?> 