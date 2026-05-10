<?php
	if(phpversion() > '7.1' && phpversion() < '8.0'){ 
		require_once('prostore7174.php');
	}elseif(phpversion() >= '8.0' && phpversion() < '8.1'){ 
		exit('PHP8.0 is not supported by the Prostore template!');
	}elseif(phpversion() >= '8.1' && phpversion() < '8.2'){ 
		require_once('prostore81.php');
	}else{
		require_once('prostore82.php');
	}
?>
