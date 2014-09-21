<?php
//debug
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
//end debug

function errorResponse($message, $httpCode){
	$response = array(
					'error' => true,
					'httpcode' => $httpCode,
					'message' => $message
					);
	response($response, $httpCode);
}

function response($dataArray, $httpCode){
	header('Content-Type: application/json');
	http_response_code($httpCode);
	echo json_encode($dataArray, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
	exit(0);
}

require 'dir_listing_func.php';
require 'configProvider.php';

$cfg= new ConfigProvider();

//PATH
	$path = $_SERVER['REQUEST_URI'];
	$doc_root = $_SERVER['DOCUMENT_ROOT'];
	//remove last '/' from doc_root if exist
	if($doc_root[strlen($doc_root)-1]=='/')
		$doc_root = substr($doc_root,0,strlen($doc_root)-1);
	//Substitute %xx characters
	$path = rawurldecode($path);
	//Create full path
	$full_path = $_SERVER['DOCUMENT_ROOT'].$path;
//END PATH


	$dir_handle = opendir($full_path);
	
	//error opening folder
	if($dir_handle == false) errorResponse("Failed to open folder", 500);
	
	$folderlist = array();
	$filelist = array();
		
	while( false !== ($entry = readdir($dir_handle))){
		
		//skip hidden files(optional), current folder ".", parent folder ".."
		if ( ( strpos($entry,'.') === 0 and $cfg->getCfg("show_hidden_files")===false) | $entry == "." | $entry == ".." ){
			continue;
		}else if ( is_dir( $full_path.$entry ) ) {
			$folderlist[ $entry ] = array();
		}else{
			$filelist[ $entry ] = array();
		}
	}
	
	
	//folder is empty
	if(count ($folderlist) == 0 and count ($filelist) == 0) errorResponse("Empty folder", 200);

	//order folder and files
	//sort($folderlist);
	//sort($filelist);
	 	
	 	//print folder
		foreach ($folderlist as $name => $farr) {
			$folderlist[ $name ]['name'] = $name;
			$folderlist[ $name ]['url'] = rawurlencode($path . $name);
			
			if($cfg->getCfg("use_du_command") === true && $cfg->getCfg("show_folders_size") === true)
				$folderlist[ $name ]['size'] = get_file_size($full_path.$name);			
			else
				$folderlist[ $name ]['size'] = null;
			
			if($cfg->getCfg("show_modified_time") === true)
				$folderlist[ $name ]['mtime'] = filectime($full_path.$name);
			
			$folderlist[ $name ]['perms'] = fileperms($full_path.$name);
			
			
		}
	
		//print file
		foreach ($filelist as $name => $farr) {
			$filelist[ $name ]['name'] = $name;
			$filelist[ $name ]['url'] = rawurlencode($path . $name);
			$filelist[ $name ]['size'] = get_file_size($full_path.$name);
			
			if($cfg->getCfg("show_modified_time") === true)
				$filelist[ $name ]['mtime'] = filectime($full_path.$name);
				
			$filelist[ $name ]['perms'] = fileperms($full_path.$name);
		}
		
		$response = array(
			'folders' => $folderlist,
			'files' => $filelist
		);
		
		response($response, 200);		
?>
