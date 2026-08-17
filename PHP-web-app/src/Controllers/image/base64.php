<?php
$base64Image = null;

if (isset($_POST['upload']) && isset($_FILES['fotos']) && $_FILES['fotos']['error'] === UPLOAD_ERR_OK) {
    $tmpPath   = $_FILES['fotos']['tmp_name'];
    $mimeType  = mime_content_type($tmpPath);          // ex: "image/jpeg"
    $conteudo  = file_get_contents($tmpPath);           // lê o binário
    $base64    = base64_encode($conteudo);              // converte para base64
    $base64Image = "data:{$mimeType};base64,{$base64}"; // monta o data URI
}
?>