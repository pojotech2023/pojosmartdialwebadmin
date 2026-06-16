<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DeviceToken;
use App\Models\AssignBase;

echo "=== DEVICE TOKENS ===\n";
$tokens = DeviceToken::all();
echo "Total Tokens: " . $tokens->count() . "\n\n";

foreach($tokens as $token) {
    echo "ID: {$token->id}\n";
    echo "Employee ID: {$token->emp_id}\n";
    echo "Device Type: {$token->device_type}\n";
    echo "Token: " . substr($token->device_token, 0, 30) . "...\n";
    echo "Active: " . ($token->is_active ? 'Yes' : 'No') . "\n";
    echo "Created: {$token->created_at}\n";
    echo "---\n";
}

echo "\n=== ASSIGN BASE (Sample) ===\n";
$assigns = AssignBase::take(3)->get();
foreach($assigns as $assign) {
    echo "ID: {$assign->id} | Customer: {$assign->customer_name} | Emp: {$assign->emp_id}\n";
}
