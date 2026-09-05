<?php

return [
    'title' => 'Orders & Bookings',
    'overview_title' => 'Orders & Bookings Overview',
    'manage_desc' => 'Manage all customer orders and upcoming bookings automatically created from WhatsApp.',
    'monitor_desc' => 'Monitor all orders across all tenants.',
    'all_statuses' => 'All Statuses',
    'status' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
    'no_orders' => 'No Orders Found',
    'wait_for_orders' => 'Wait for new WhatsApp messages to automatically generate orders, or adjust your filter.',
    'no_admin_orders' => 'No orders found.',
    'no_admin_orders_desc' => 'No orders have been placed in the system yet or match your filter criteria.',
    'table' => [
        'order_id' => 'Order ID',
        'tenant' => 'Tenant',
        'customer' => 'Customer',
        'service_request' => 'Service Request',
        'date_time' => 'Date & Time',
        'status' => 'Status',
        'actions' => 'Actions',
        'tenant_id' => 'Tenant ID: :id',
        'unknown_tenant' => 'Unknown Tenant',
    ],
    'actions' => [
        'confirm' => 'Confirm',
        'mark_completed' => 'Mark Completed',
        'cancel' => 'Cancel',
        'cancel_confirm' => 'Are you sure you want to cancel this order?',
        'no_actions' => 'No Actions',
    ]
];
