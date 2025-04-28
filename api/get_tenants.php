<?php
require_once('../config/config.php');
header('Content-Type: application/json');

try {
    $stmt = $connect->prepare("SELECT id, fullname FROM users WHERE role = 'tenant'");
    $stmt->execute();
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = '<option value="">Select Tenant</option>';
    foreach($tenants as $tenant) {
        $options .= sprintf('<option value="%d">%s</option>',
            $tenant['id'],
            htmlspecialchars($tenant['fullname'])
        );
    }
    echo $options;
} catch(PDOException $e) {
    echo '<option>Error loading tenants</option>';
}