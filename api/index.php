<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="authors" content="Ahmed Elamin">
    <title>Chki API Documentation</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .api-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .endpoint {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }
        .get { background-color: #61affe; }
        .post { background-color: #49cc90; }
        .put { background-color: #fca130; }
        .delete { background-color: #f93e3e; }
        
        .endpoint-url {
            font-family: monospace;
            font-size: 14px;
        }
        
        .param-table {
            width: 100%;
            margin-top: 15px;
        }
        
        .response-example {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Navigation Bar -->
        <nav class="main-nav">
            <div class="nav-section left">
                <a href="../index.php" class="logo">Chki</a>
            </div>
            <div class="nav-section middle">
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
            <div class="nav-section right">
                <a href="../add-assignment.php" class="btn add-btn" aria-label="Add assignment">
                    <i class="fas fa-plus"></i>
                </a>
                <a href="../settings.html" class="btn user-btn" aria-label="Account settings">
                    <i class="fas fa-user"></i>
                </a>
                <a href="../logout.php" class="btn logout-btn" aria-label="Log out">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="content-area">
            <div class="api-container">
                <h1>Chki API Documentation</h1>
                <p>This documentation describes the available API endpoints for the Chki assignment tracker application.</p>
                
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="endpoint-url">/api/assignments.php</span>
                    <p>Retrieves all assignments for the currently logged-in user.</p>
                    
                    <h4>Query Parameters:</h4>
                    <table class="table param-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>course</td>
                                <td>string</td>
                                <td>Filter assignments by course name</td>
                            </tr>
                            <tr>
                                <td>priority</td>
                                <td>string</td>
                                <td>Filter assignments by priority (low, medium, high)</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h4>Response Example:</h4>
                    <pre class="response-example">
{
  "assignments": [
    {
      "assignment_id": 1,
      "user_id": 1,
      "title": "Web Development Sprint 3",
      "description": "Implement the PHP backend",
      "course": "CS4640",
      "due_date": "2025-03-15 23:59:00",
      "priority": "high",
      "status": "pending",
      "created_at": "2023-03-01 12:00:00"
    },
    {
      "assignment_id": 2,
      "user_id": 1,
      "title": "Database Project",
      "description": "Create ER diagram",
      "course": "CS4750",
      "due_date": "2025-03-20 23:59:00",
      "priority": "medium",
      "status": "pending",
      "created_at": "2023-03-02 12:00:00"
    }
  ],
  "count": 2
}
                    </pre>
                </div>
                
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="endpoint-url">/api/assignments.php?id={assignment_id}</span>
                    <p>Retrieves a specific assignment by ID.</p>
                    
                    <h4>Response Example:</h4>
                    <pre class="response-example">
{
  "assignment_id": 1,
  "user_id": 1,
  "title": "Web Development Sprint 3",
  "description": "Implement the PHP backend",
  "course": "CS4640",
  "due_date": "2025-03-15 23:59:00",
  "priority": "high",
  "status": "pending",
  "created_at": "2023-03-01 12:00:00"
}
                    </pre>
                </div>
                
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="endpoint-url">/api/assignments.php</span>
                    <p>Creates a new assignment.</p>
                    
                    <h4>Request Body:</h4>
                    <table class="table param-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>title</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Assignment title</td>
                            </tr>
                            <tr>
                                <td>description</td>
                                <td>string</td>
                                <td>No</td>
                                <td>Assignment description</td>
                            </tr>
                            <tr>
                                <td>course</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Course name</td>
                            </tr>
                            <tr>
                                <td>due_date</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Due date and time (YYYY-MM-DD HH:MM:SS)</td>
                            </tr>
                            <tr>
                                <td>priority</td>
                                <td>string</td>
                                <td>No</td>
                                <td>Priority level (low, medium, high)</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h4>Response Example:</h4>
                    <pre class="response-example">
{
  "message": "Assignment created successfully",
  "id": 3
}
                    </pre>
                </div>
                
                <div class="endpoint">
                    <span class="method put">PUT</span>
                    <span class="endpoint-url">/api/assignments.php?id={assignment_id}</span>
                    <p>Updates an existing assignment.</p>
                    
                    <h4>Request Body:</h4>
                    <p>Include any field you want to update.</p>
                    
                    <h4>Response Example:</h4>
                    <pre class="response-example">
{
  "message": "Assignment updated successfully"
}
                    </pre>
                </div>
                
                <div class="endpoint">
                    <span class="method delete">DELETE</span>
                    <span class="endpoint-url">/api/assignments.php?id={assignment_id}</span>
                    <p>Deletes an assignment.</p>
                    
                    <h4>Response Example:</h4>
                    <pre class="response-example">
{
  "message": "Assignment deleted successfully"
}
                    </pre>
                </div>
                
                <h2>Try the API</h2>
                <p>Use the form below to test the API:</p>
                
                <div class="card mb-4">
                    <div class="card-header">
                        Test API Endpoint
                    </div>
                    <div class="card-body">
                        <form id="apiTestForm">
                            <div class="mb-3">
                                <label for="method" class="form-label">Method</label>
                                <select id="method" class="form-select">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <option value="PUT">PUT</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="endpoint" class="form-label">Endpoint</label>
                                <input type="text" class="form-control" id="endpoint" value="/api/assignments.php">
                            </div>
                            
                            <div class="mb-3">
                                <label for="requestData" class="form-label">Request Body (JSON)</label>
                                <textarea class="form-control" id="requestData" rows="5"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        Response
                    </div>
                    <div class="card-body">
                        <pre id="apiResponse" class="response-example">No response yet</pre>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('apiTestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const method = document.getElementById('method').value;
            const endpoint = document.getElementById('endpoint').value;
            const requestData = document.getElementById('requestData').value;
            const responseElement = document.getElementById('apiResponse');
            
            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };
                
                // Add body for POST and PUT requests
                if (method === 'POST' || method === 'PUT') {
                    if (requestData.trim()) {
                        options.body = requestData;
                    }
                }
                
                const response = await fetch(endpoint, options);
                const data = await response.json();
                
                // Format and display the response
                responseElement.textContent = JSON.stringify(data, null, 2);
                
            } catch (error) {
                responseElement.textContent = `Error: ${error.message}`;
            }
        });
        
        // Update sample request data based on selected method
        document.getElementById('method').addEventListener('change', function() {
            const method = this.value;
            const requestDataField = document.getElementById('requestData');
            
            switch(method) {
                case 'POST':
                    requestDataField.value = JSON.stringify({
                        title: "New Assignment",
                        description: "Description here",
                        course: "CS4640",
                        due_date: "2025-04-01 23:59:00",
                        priority: "medium"
                    }, null, 2);
                    break;
                case 'PUT':
                    requestDataField.value = JSON.stringify({
                        title: "Updated Assignment Title",
                        priority: "high"
                    }, null, 2);
                    break;
                default:
                    requestDataField.value = "";
            }
        });
    </script>
</body>
</html> 