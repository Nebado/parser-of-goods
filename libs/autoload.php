<?php

spl_autoload_register(function ($class_name) {
	$preg_match = preg_match('/^PhpOffice\\\PhpSpreadsheet\\\/', $class_name);

	if (1 === $preg_match) {
		$class_name = preg_replace('/\\\/', '/', $class_name);
		$class_name = preg_replace('/^PhpOffice\\/PhpSpreadsheet\\//', '', $class_name);
		require_once(__DIR__ . '/PhpSpreadsheet/' . $class_name . '.php');
	}
});

spl_autoload_register(function ($class_name) {
	$preg_match = preg_match('/^ZipStream\\\/', $class_name);

	if (1 === $preg_match) {
		$class_name = preg_replace('/\\\/', '/', $class_name);
		$class_name = preg_replace('/^ZipStream\\\/', '', $class_name);
		require_once(__DIR__ . '/' . $class_name . '.php');
	}
});

spl_autoload_register(function ($class_name) {
	$preg_match = preg_match('/^Psr\\\/', $class_name);

	if (1 === $preg_match) {
		require_once(__DIR__ . '/Psr.php');
	} else if (false === $preg_match) {
		assert(false, 'Error de preg_match().');
	}
});
