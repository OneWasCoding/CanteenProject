<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $itemId = $data->item_id;

    $userId = $_SESSION['user_id'];

    // Check if item is already in cart
    $sql_check = "SELECT quantity FROM cart WHERE user_id = ? AND item_id = ?";
    $stmt_check = $con->prepare($sql_check);
    $stmt_check->bind_param("ii", $userId, $itemId);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Item already in cart, update quantity
        $row = $result_check->fetch_assoc();
        $newQuantity = $row['quantity'] + 1;

        $sql_update = "UPDATE cart SET quantity = ? WHERE user_id = ? AND item_id = ?";
        $stmt_update = $con->prepare($sql_update);
        $stmt_update->bind_param("iii", $newQuantity, $userId, $itemId);

        if ($stmt_update->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cart updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
        }
        $stmt_update->close();
    } else {
        // Item not in cart, add new item
        $sql_insert = "INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, 1)";
        $stmt_insert = $con->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $userId, $itemId);

        if ($stmt_insert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Item added to cart.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add item to cart.']);
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$con->close();
?>
