<?php
session_start();

header('Content-type:application/json;charset=utf-8');
$ref_tabulato=$_POST['ref_tabulato'];
$ref_aziende=$_POST['ref_aziende'];

try {
    if (
        !isset($_FILES['file']['error']) ||
        is_array($_FILES['file']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    //$filepath = sprintf('files/%s_%s', uniqid(), $_FILES['file']['name']);

	$path_parts = pathinfo($_FILES["file"]["name"]);
	$extension = $path_parts['extension'];
	$filename=uniqid().".csv";

	$sub="allegati";
	if ($ref_aziende=="0") {
		@unlink("$sub/".$ref_tabulato.".csv");
		$filepath = "$sub/".$ref_tabulato.".csv";
	}	
	else {
		@unlink("$sub/".$ref_tabulato."_aziende.csv");		
		$filepath = "$sub/".$ref_tabulato."_aziende.csv";
	}	

    if (!move_uploaded_file(
        $_FILES['file']['tmp_name'],
        $filepath
    )) {
        throw new RuntimeException('Failed to move uploaded file.');
    }


	
    // All good, send the response
    echo json_encode([
        'status' => 'ok',
        'path' => $filepath,
		'filename' =>$filename,
		'ref_aziende' =>$ref_aziende
	]);

} catch (RuntimeException $e) {
	// Something went wrong, send the err message as JSON
	http_response_code(400);

	echo json_encode([
		'status' => 'error',
		'message' => $e->getMessage()
	]);
}