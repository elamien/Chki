/**
 * Assignments.js - Client-side JavaScript for the Chki Assignment Tracker
 * This file contains functions for interacting with the assignments API
 */

// Function to fetch assignments from the API
async function fetchAssignments(filters = {}) {
    try {
        // Build query parameters
        const params = new URLSearchParams();
        
        if (filters.course) {
            params.append('course', filters.course);
        }
        
        if (filters.priority) {
            params.append('priority', filters.priority);
        }
        
        // Make the API request
        const response = await fetch(`/api/assignments.php?${params.toString()}`);
        
        // Check if request was successful
        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }
        
        // Parse the JSON response
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching assignments:', error);
        throw error;
    }
}

// Function to delete an assignment via API
async function deleteAssignment(assignmentId) {
    try {
        if (!confirm('Are you sure you want to delete this assignment?')) {
            return false;
        }
        
        const response = await fetch(`/api/assignments.php?id=${assignmentId}`, {
            method: 'DELETE',
        });
        
        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }
        
        const result = await response.json();
        
        // If successful, refresh the page or update the UI
        if (result.message) {
            window.location.reload();
            return true;
        }
        
        return false;
    } catch (error) {
        console.error('Error deleting assignment:', error);
        return false;
    }
}

// Function to create an assignment via API
async function createAssignment(assignmentData) {
    try {
        const response = await fetch('/api/assignments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(assignmentData),
        });
        
        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Error creating assignment:', error);
        throw error;
    }
}

// Function to update an assignment via API
async function updateAssignment(assignmentId, assignmentData) {
    try {
        const response = await fetch(`/api/assignments.php?id=${assignmentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(assignmentData),
        });
        
        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Error updating assignment:', error);
        throw error;
    }
}

// Function to render assignments in the UI
function renderAssignments(assignments) {
    const container = document.querySelector('.assignments-container');
    
    // Clear existing content
    const existingCards = container.querySelectorAll('.assignment-card');
    existingCards.forEach(card => card.remove());
    
    if (assignments.length === 0) {
        const noAssignments = document.createElement('div');
        noAssignments.className = 'no-assignments';
        noAssignments.innerHTML = `
            <p>You don't have any assignments yet.</p>
            <a href="add-assignment.php" class="btn primary-btn">Add Your First Assignment</a>
        `;
        container.appendChild(noAssignments);
        return;
    }
    
    // Create assignment cards
    assignments.forEach(assignment => {
        const card = createAssignmentCard(assignment);
        container.appendChild(card);
    });
}

// Function to create an assignment card element
function createAssignmentCard(assignment) {
    const cardClass = getAssignmentClass(assignment.due_date, assignment.priority);
    
    const card = document.createElement('div');
    card.className = `assignment-card ${cardClass}`;
    card.innerHTML = `
        <div class="card-header">
            <div class="header-content">
                <h2 class="assignment-title">${assignment.title}</h2>
                <p class="assignment-description">${assignment.description || ''}</p>
                <div class="course-tag">${assignment.course}</div>
            </div>
            <span class="due-date">Due: ${formatDate(assignment.due_date)}</span>
        </div>
        <div class="card-actions">
            <div class="dropdown">
                <button class="btn menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="edit-assignment.php?id=${assignment.assignment_id}">Edit</a></li>
                    <li><button class="dropdown-item delete-btn" data-id="${assignment.assignment_id}">Delete</button></li>
                    <li><a class="dropdown-item" href="#">Mark as Complete</a></li>
                </ul>
            </div>
        </div>
    `;
    
    // Add event listener for delete button
    card.querySelector('.delete-btn').addEventListener('click', async (e) => {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        if (await deleteAssignment(id)) {
            card.remove();
        }
    });
    
    return card;
}

// Helper function to determine assignment card class based on due date and priority
function getAssignmentClass(dueDate, priority) {
    const today = new Date();
    const dueDateTime = new Date(dueDate);
    const daysDiff = Math.ceil((dueDateTime - today) / (1000 * 60 * 60 * 24));
    
    if (daysDiff <= 1) {
        return 'urgent';
    } else if (daysDiff <= 3 || priority === 'high') {
        return 'warning';
    } else {
        return 'normal blue';
    }
}

// Helper function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Initialize assignments when document is ready
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Add event listeners for filter controls if they exist
        const courseFilter = document.getElementById('courseFilter');
        if (courseFilter) {
            courseFilter.addEventListener('change', async () => {
                const data = await fetchAssignments({ course: courseFilter.value });
                renderAssignments(data.assignments);
            });
        }
        
        const priorityFilter = document.getElementById('priorityFilter');
        if (priorityFilter) {
            priorityFilter.addEventListener('change', async () => {
                const data = await fetchAssignments({ priority: priorityFilter.value });
                renderAssignments(data.assignments);
            });
        }
        
        // Initialize delete buttons for existing assignments
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                const id = button.getAttribute('data-id');
                if (await deleteAssignment(id)) {
                    const card = button.closest('.assignment-card');
                    if (card) {
                        card.remove();
                    }
                }
            });
        });
        
    } catch (error) {
        console.error('Error initializing assignments:', error);
    }
}); 