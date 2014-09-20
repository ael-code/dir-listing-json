<?php

function get_file_size($file){
	global $cfg;
	
	if( $cfg->getCfg("use_du_command") === true){
		return get_file_size_du($file);
	}else{
		//undefined result for file > 2GB on 32bit 
		return fileSize($file);
	}
}

function get_file_size_du($file){
	exec('du -sLB 1 "'. $file .'"' ,$exec);
	$sizeonly = explode("\t",$exec[0],2);
	return (int) $sizeonly[0];
}

?>
