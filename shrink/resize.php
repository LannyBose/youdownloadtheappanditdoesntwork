<?php
	error_reporting(E_ALL);
	define('ROOT',__DIR__ . DIRECTORY_SEPARATOR . '..');
	
	function swapext($name,$ext){
		if(preg_match('#[^.]+$#',$name,$m)){
			return substr($name,0,-strlen($m[0])) . $ext;
		}
		return "$name.$ext";
	}
	
	$origsize=$newsize=0;
	
	foreach(glob(ROOT . '/originals/*.*') as $img){
		$base=basename($img);
		$dest=ROOT . '/' . $base;
		if(!file_exists($dest)){
			if($i=imagecreatefromstring(file_get_contents($img))){
				$ow=$w=imagesx($i);
				$oh=$h=imagesy($i);
				
				if($ow>490){
					$w=490;
					$h=floor($oh/($ow/$w));
					
					$temp_i=imagescale($i,$w,$h);
					imagedestroy($i);
					$i=$temp_i;
				}
				
				if(preg_match('#\.png$#i',$base)){
					imagepng($i,$dest,9);
				}
				else{
					imagejpeg($i,$dest,80);
				}
				
				imagedestroy($i);
				$origsize+=filesize($img);
				$newsize+=filesize($dest);
				echo "$base ($ow x $oh):\tReduced to ($w x $h) " . round(filesize($dest)/filesize($img)*100) . '% of original' . PHP_EOL;
			}
			else{
				echo "FAIL: $base" . PHP_EOL;
			}
		}
		else{
			echo "SKIP: $base already processed" . PHP_EOL;
		}
	}
	$origsize=round($origsize/1024);
	$newsize=round($newsize/1024);
	echo "Reduced $origsize K to $newsize K (" . round($newsize/$origsize*100) . '% of original)' . PHP_EOL;
?>