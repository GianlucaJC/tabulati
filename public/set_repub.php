<?php
session_start();

header('Content-type:application/json;charset=utf-8');
$nome_file=$_POST['nome_file'];
$ref_tabulato=$_POST['ref_tabulato'];

try {

	$orig = "allegati/pubblicazioni/".$nome_file.".csv";
	$dest = "allegati/".$ref_tabulato.".csv";

	copy($orig,$dest);
    // All good, send the response
    echo json_encode([
        'status' => 'ok',
        'orig' => $orig,
		'dest' =>$dest
	]);

} catch (RuntimeException $e) {
	// Something went wrong, send the err message as JSON
	http_response_code(400);
	echo json_encode([
		'status' => 'error',
		'message' => $e->getMessage()
	]);
}