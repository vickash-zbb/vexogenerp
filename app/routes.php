<?php

declare(strict_types=1);

use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\CalendarController;
use App\Controllers\ClientController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentController;
use App\Controllers\EmployeeController;
use App\Controllers\ExpenseController;
use App\Controllers\FileController;
use App\Controllers\InvoiceController;
use App\Controllers\LandingController;
use App\Controllers\PaymentController;
use App\Controllers\ProjectController;
use App\Controllers\QuotationController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;
use App\Controllers\TaskController;
use App\Core\Router;

$router = new Router();

// Public
$router->get('/', [LandingController::class, 'index'], 'public');
$router->get('/login', [AuthController::class, 'loginForm'], 'public');
$router->post('/login', [AuthController::class, 'login'], 'public');
$router->get('/logout', [AuthController::class, 'logout'], 'public');

// Protected pages
$router->get('/dashboard', [DashboardController::class, 'index'], null);
$router->get('/clients', [ClientController::class, 'index'], 'clients');
$router->get('/clients/{id}', [ClientController::class, 'show'], 'clients');
$router->get('/projects', [ProjectController::class, 'index'], 'projects');
$router->get('/tasks', [TaskController::class, 'index'], 'tasks');
$router->get('/payments', [PaymentController::class, 'index'], 'payments');
$router->get('/invoices', [InvoiceController::class, 'index'], 'invoices');
$router->get('/quotations', [QuotationController::class, 'index'], 'quotations');
$router->get('/expenses', [ExpenseController::class, 'index'], 'expenses');
$router->get('/employees', [EmployeeController::class, 'index'], 'employees');
$router->get('/files', [FileController::class, 'index'], 'files');
$router->get('/files/download/{id}', [FileController::class, 'download'], 'files');
$router->get('/calendar', [CalendarController::class, 'index'], 'calendar');
$router->get('/reports', [ReportController::class, 'index'], 'reports');
$router->get('/settings', [SettingsController::class, 'index'], 'settings');

// PDF documents
$router->get('/documents/invoice/{id}/pdf', [DocumentController::class, 'invoicePdf'], 'public');
$router->get('/documents/quotation/{id}/pdf', [DocumentController::class, 'quotationPdf'], 'public');

// API
$router->api('GET', '/api/search', [ApiController::class, 'search'], 'clients');
$router->api('GET', '/api/notifications', [ApiController::class, 'notifications'], 'clients');
$router->api('POST', '/api/notifications/read', [ApiController::class, 'markNotificationsRead'], 'clients');
$router->api('GET', '/api/dashboard/stats', [ApiController::class, 'dashboardStats'], 'clients');

$router->api('POST', '/api/clients', [ApiController::class, 'storeClient'], 'clients');
$router->api('POST', '/api/clients/{id}/update', [ApiController::class, 'updateClient'], 'clients');
$router->api('DELETE', '/api/clients/{id}', [ApiController::class, 'deleteClient'], 'clients');
$router->api('POST', '/api/clients/{id}/notes', [ApiController::class, 'updateClientNotes'], 'projects');
$router->api('POST', '/api/communications', [ApiController::class, 'storeCommunication'], 'clients');

$router->api('POST', '/api/projects', [ApiController::class, 'storeProject'], 'projects');
$router->api('POST', '/api/projects/{id}/status', [ApiController::class, 'updateProjectStatus'], 'projects');
$router->api('GET', '/api/projects/{id}', [ApiController::class, 'projectDetails'], 'projects');
$router->api('POST', '/api/projects/{id}/update', [ApiController::class, 'updateProject'], 'projects');
$router->api('DELETE', '/api/projects/{id}', [ApiController::class, 'deleteProject'], 'projects');
$router->api('POST', '/api/projects/{id}/complete', [ApiController::class, 'completeProject'], 'projects');
$router->api('POST', '/api/projects/{id}/comment', [ApiController::class, 'addProjectComment'], 'projects');

$router->api('POST', '/api/tasks', [ApiController::class, 'storeTask'], 'tasks');
$router->api('POST', '/api/tasks/{id}/status', [ApiController::class, 'updateTaskStatus'], 'tasks');
$router->api('POST', '/api/tasks/{id}/update', [ApiController::class, 'updateTask'], 'tasks');
$router->api('DELETE', '/api/tasks/{id}', [ApiController::class, 'deleteTask'], 'tasks');

$router->api('POST', '/api/payments', [ApiController::class, 'storePayment'], 'payments');
$router->api('POST', '/api/payments/{id}/update', [ApiController::class, 'updatePayment'], 'payments');
$router->api('DELETE', '/api/payments/{id}', [ApiController::class, 'deletePayment'], 'payments');
$router->api('POST', '/api/invoices', [ApiController::class, 'storeInvoice'], 'invoices');
$router->api('POST', '/api/invoices/{id}/update', [ApiController::class, 'updateInvoice'], 'invoices');
$router->api('DELETE', '/api/invoices/{id}', [ApiController::class, 'deleteInvoice'], 'invoices');
$router->api('POST', '/api/invoices/{id}/email', [DocumentController::class, 'emailInvoice'], 'invoices');
$router->api('POST', '/api/invoices/{id}/whatsapp', [DocumentController::class, 'whatsappInvoice'], 'invoices');

$router->api('POST', '/api/quotations', [ApiController::class, 'storeQuotation'], 'quotations');
$router->api('POST', '/api/quotations/{id}/update', [ApiController::class, 'updateQuotation'], 'quotations');
$router->api('DELETE', '/api/quotations/{id}', [ApiController::class, 'deleteQuotation'], 'quotations');
$router->api('POST', '/api/quotations/{id}/convert', [ApiController::class, 'convertQuotation'], 'quotations');
$router->api('POST', '/api/quotations/{id}/email', [DocumentController::class, 'emailQuotation'], 'quotations');
$router->api('POST', '/api/quotations/{id}/whatsapp', [DocumentController::class, 'whatsappQuotation'], 'quotations');

$router->api('POST', '/api/expenses', [ApiController::class, 'storeExpense'], 'expenses');
$router->api('POST', '/api/expenses/{id}/update', [ApiController::class, 'updateExpense'], 'expenses');
$router->api('DELETE', '/api/expenses/{id}', [ApiController::class, 'deleteExpense'], 'expenses');
$router->api('POST', '/api/employees', [ApiController::class, 'storeEmployee'], 'employees');
$router->api('POST', '/api/employees/{id}/update', [ApiController::class, 'updateEmployee'], 'employees');
$router->api('DELETE', '/api/employees/{id}', [ApiController::class, 'deleteEmployee'], 'employees');
$router->api('POST', '/api/calendar-events', [ApiController::class, 'storeCalendarEvent'], 'calendar');
$router->api('POST', '/api/calendar-events/{id}/update', [ApiController::class, 'updateCalendarEvent'], 'calendar');
$router->api('DELETE', '/api/calendar-events/{id}', [ApiController::class, 'deleteCalendarEvent'], 'calendar');
$router->api('POST', '/api/settings', [ApiController::class, 'updateSettings'], 'settings');
$router->api('POST', '/api/users/{id}/update', [ApiController::class, 'updateUser'], 'settings');
$router->api('DELETE', '/api/users/{id}', [ApiController::class, 'deleteUser'], 'settings');

$router->api('POST', '/api/files', [ApiController::class, 'uploadFile'], 'files');
$router->api('POST', '/api/files/{id}/update', [ApiController::class, 'updateFile'], 'files');
$router->api('DELETE', '/api/files/{id}', [ApiController::class, 'deleteFile'], 'files');

$router->api('POST', '/api/backup', [ApiController::class, 'createBackup'], 'settings');
$router->api('POST', '/api/backup/token', [ApiController::class, 'generateBackupToken'], 'settings');

return $router;
