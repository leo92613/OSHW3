<?php
	$file=fopen("process.file".$argv[1],'w');
	fwrite($file,$argv[1]);
	for ($i=0;$i<$argv[1];$i++){
		$num=rand(0,$argv[1]*0.8);
		fwrite($file,"\n$num");
		}
	fclose($file);
?>