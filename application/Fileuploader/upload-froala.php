<?php
if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $name = uniqid() . '.' . $ext;
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/froala/';
    $uploadUrl = 'https://embalabag.com/uploads/froala/' . $name;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $name);

    echo json_encode(['link' => $uploadUrl]);

} else {

    http_response_code(400);
    echo json_encode(['error' => 'Erro no upload']);
}