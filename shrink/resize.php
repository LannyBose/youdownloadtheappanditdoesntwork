<?php
	error_reporting(E_ALL);
	
	//Only allow to be run from the command line
	if(php_sapi_name()!=='cli'){
		die(0);
	}
	//Directory with the unscaled images
	define('ROOT',__DIR__ . DIRECTORY_SEPARATOR . '..');
	
	//NOT_IN_USE
	//Can be used to swap the extension, for example when all images should be converted to jpeg.
	function swapext($name,$ext){
		if(preg_match('#[^.]+$#',$name,$m)){
			return substr($name,0,-strlen($m[0])) . $ext;
		}
		return "$name.$ext";
	}
	
	//File size counter
	$origsize=$newsize=0;
	
	foreach(glob(ROOT . '/originals/*.*') as $img){
		$base=basename($img);
		$dest=ROOT . '/' . $base;
		//Don't resize files we already have
		if(!file_exists($dest)){
			if($i=imagecreatefromstring(file_get_contents($img))){
				$ow=$w=imagesx($i);
				$oh=$h=imagesy($i);
				
				//Resize if the width is larger than mentioned in the HTML document (only scale down)
				if($ow>490){
					$w=490;
					$h=floor($oh/($ow/$w));
					
					$temp_i=imagescale($i,$w,$h);
					imagedestroy($i);
					$i=$temp_i;
				}
				
				//Export as PNG or JPG, depending on the source file extension
				if(preg_match('#\.png$#i',$base)){
					imagepng($i,$dest,9);
				}
				else{
					imagejpeg($i,$dest,80);
				}
				
				imagedestroy($i);
				//Tally sizes
				$origsize+=filesize($img);
				$newsize+=filesize($dest);
				//Report
				echo "$base ($ow x $oh):\tReduced to ($w x $h) " . round(filesize($dest)/filesize($img)*100) . '% of original' . PHP_EOL;
			}
			else{
				//Not a valid image. Should probably be inspected
				echo "FAIL: $base" . PHP_EOL;
			}
		}
		else{
			//Image already exists
			echo "SKIP: $base already processed" . PHP_EOL;
		}
	}
	if($origsize>0){
		//Convert to KiB
		$origsize=round($origsize/1024);
		$newsize=round($newsize/1024);
		//Report size change to user
		echo "Reduced $origsize K to $newsize K (" . round($newsize/$origsize*100) . '% of original)' . PHP_EOL;
	}
	else{
		echo 'No file was resized' . PHP_EOL;
	}
?>