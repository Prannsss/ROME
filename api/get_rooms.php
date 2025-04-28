<?php
require_once('../config/config.php');
header('Content-Type: application/json');

try {
    $stmt = $connect->prepare("SELECT id, fullname FROM room_rental_registrations WHERE vacant = 1");
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = '<option value="">Select Room</option>';
    foreach($rooms as $room) {
        $options .= sprintf('<option value="%d">%s</option>',
            $room['id'],
            htmlspecialchars($room['fullname'])
        );
    }
    echo $options;
} catch(PDOException $e) {
    echo '<option>Error loading rooms</option>';
}