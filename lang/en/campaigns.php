<?php

return [
    'title' => 'Campaigns',
    'marketing_campaigns' => 'Marketing Campaigns',
    'manage_desc' => 'Create and manage your bulk WhatsApp campaigns.',
    'new_campaign' => 'New Campaign',
    'no_campaigns' => 'No campaigns yet',
    'start_reaching' => 'Upload an Excel file and start reaching out to your customers today via WhatsApp.',
    'create_first' => 'Create your first campaign',
    
    // Statuses
    'status' => [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'running' => 'Running',
        'paused' => 'Paused',
        'completed' => 'Completed',
        'failed' => 'Failed',
    ],

    // Table Headers
    'table' => [
        'name' => 'Campaign Name',
        'status' => 'Status',
        'progress' => 'Progress',
        'scheduled' => 'Scheduled For',
        'actions' => 'Actions',
        'tenant' => 'Tenant',
        'instance' => 'Instance',
        'recipient' => 'Recipient',
        'sent_at' => 'Sent At',
        'reason' => 'Reason / Error',
    ],

    // Create Page
    'create' => [
        'title' => 'Create WhatsApp Campaign',
        'subtitle' => 'Launch a new marketing or notification campaign in minutes.',
        'step_details' => 'Campaign Details',
        'step_recipients' => 'Upload Recipients',
        'step_schedule' => 'Schedule',
        'name_label' => 'Campaign Name',
        'name_placeholder' => 'e.g., Summer Sale 2024',
        'instance_label' => 'WhatsApp Instance',
        'instance_placeholder' => 'Select an instance...',
        'template_label' => 'Message Template',
        'template_placeholder' => 'Hello @{{name}}, check out our latest offers!',
        'variables_hint' => 'Use variables: "@{{name}}", "@{{phone}}"',
        'upload_title' => 'Click to upload or drag and drop',
        'upload_hint' => 'Supported formats: XLSX, XLS, CSV (Max 10MB)',
        'file_structure' => 'File Structure:',
        'file_structure_desc' => 'Columns must include "phone" (required). Optional: "name" and "message".',
        'schedule_now' => 'Send Now',
        'schedule_later' => 'Send Later',
        'datetime_label' => 'Select Date & Time',
        'launch_btn' => 'Launch Campaign',
        'spam_warning' => 'By launching, you agree to our anti-spam terms. Avoid sending more than 1000 messages daily on new accounts.',
    ],

    // Show Page
    'show' => [
        'stats' => [
            'total' => 'Total Recipients',
            'sent' => 'Sent Successfully',
            'failed' => 'Failed Messages',
            'status' => 'Current Status',
            'completion_rate' => 'completion rate',
        ],
        'logs_title' => 'Delivery Logs',
        'export' => 'Export Logs',
        'template_title' => 'Message Template',
        'settings_title' => 'Settings',
        'creation_date' => 'Creation Date',
        'tips_title' => 'Campaign Tips',
        'tip_connected' => 'Ensure your WhatsApp instance is Connected.',
        'tip_delay' => 'We add a random 5-10s delay between messages to keep your account safe.',
        'tip_retry' => 'Failed messages will automatically retry once if the error is temporary.',
    ],

    // Admin Specific
    'admin' => [
        'all_campaigns' => 'All Tenant Campaigns',
        'monitoring' => 'Campaign Monitoring',
        'total_global' => 'Total Campaigns',
        'running_now' => 'Running Now',
        'failures_today' => 'Failures Today',
        'filter_title' => 'Filters',
        'filter_btn' => 'Filter Results',
        'manage_btn' => 'Manage',
        'audit_logs' => 'Recent Audit Logs',
        'no_audit' => 'No audit logs found',
        'retry_failures' => 'Retry Failures',
        'force_stop' => 'Force Stop',
        'force_stop_confirm' => 'FORCE STOP this campaign? This action cannot be undone.',
        'pause' => 'Pause Campaign',
        'resume' => 'Resume Campaign',
        'cancel_confirm' => 'Cancel this campaign?',
        'delete_confirm' => 'Delete this campaign?',
    ],

    // Flash Messages
    'flash' => [
        'created' => 'Campaign created and processing started.',
        'paused' => 'Campaign paused.',
        'resumed' => 'Campaign resumed.',
        'toggle_error' => 'Cannot toggle campaign in current status.',
        'cancelled' => 'Campaign cancelled.',
        'deleted' => 'Campaign deleted.',
        'retrying' => 'Retrying failed messages...',
    ],
];
