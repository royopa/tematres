<?php
/**
 * Nicolas Poulain, http://tounoki.Org
 * GNU/GPL - part of tematres1.1
*/
// Be careful, access to this file is not protected
if ((stristr( $_SERVER['REQUEST_URI'], "session.php") ) || ( !defined('T3_ABSPATH') )) die("no access");

/*
must be ADMIN
*/
if($_SESSION[$_SESSION["CFGURL"]][ssuser_nivel]=='1'){
	// get the variables
	
	if (($_POST['taskAdmin']=='importTab') && (file_exists($_FILES["file"]["tmp_name"])) )
	{

	$src_txt= $_FILES["file"]["tmp_name"];
	$content_text = file_get_contents($src_txt) ;

	// tests
	$ok = true ;
	$error = array() ;


	if ( $ok ) {
	//~ && utf8_encode(utf8_decode($content_text)) == $content_text ) {
		$content_text = NULL ;
	}
	else {
		$ok = false ;
		$error[] = "ERROR : encodage of import file must be UTF-8" ;
		// sinon faire conversion automatique
	}

	// start the procedure
	if ($ok == true ) {

		// creation of the relation
		// 2 step : 1. add the terms 2. add the relation
		$to_do = array() ;

		if ($debug) echo "<textarea style=\"width:500px;height:500px;\">" ;

		$fd=fopen($src_txt,"r");
		
		while ( ($content= fgets($fd)) !== false ) {	

		$time_now = time();
		if ($time_start >= $time_now + 10) {
			$time_start = $time_now;
			header('X-pmaPing: Pong');
		};
			
			
			//sleep(1) ;
			$terme = $content ;
			// traitement data
			$level = substr_count($terme,"\t") ;
			$terme = str_replace("\n","",$terme) ;
			$terme = str_replace("\t","",$terme) ;
			$terme = trim($terme) ;
			if ( empty($terme) ) continue ;

			// traitement de oe
			// traitement de = pour terme exclus/renvois
			$equ = false ;
			if ( strpos($terme,"=") === false ) {
				$p = 0 ;
			}
			else {
				$liste = explode("=",$terme) ;
				$terme = trim($liste[0]) ;
				$equ = true ;
			}

			$encoded_term =($DBCFG["DBcharset"] =="utf8") ? $terme : utf8_decode($terme) ;

			if ( $level == 0 ) {
				$new_termino = abm_tema('alta',$encoded_term);
				$last[0] = $new_termino;
			}
			elseif ( $level > 0 ) {
				$new_termino = abm_tema('alta',$encoded_term);
				//$new_relacion = do_r($last[$level-1],$new_termino,"3");
				$to_do[] = $last[$level-1]."|".$new_termino."|3" ;
				$last[$level] = $new_termino ;
			}


			if ( $equ === true ) {
				foreach( $liste as $value ) {
					$value = trim($value) ;
					if ( $value == $terme ) continue ;
					//$encoded_value = ( $CFG["_CHAR_ENCODE"] == "utf-8" ) ? $value : utf8_decode($value) ;
					$encoded_value = utf8_decode($value) ;
					$id_equ = abm_tema('alta',$encoded_value) ;
					//$equ_relacion = do_r($id_equ,$new_termino,"4") ;
					$to_do[] = $id_equ."|".$new_termino."|4" ;
				}
			}

			if ($debug) echo $level."-".$terme."\n" ;

		}

		foreach ($to_do as $line) {
			if ($debug) echo $line."\n" ;
			$t = explode("|",$line) ;
			do_r($t[0],$t[1],$t[2]) ;
		}

		if ($debug) echo "</textarea>" ;

		fclose($fd);
		//recreate index
		$sql=SQLreCreateTermIndex();		
		echo '<p class="true">'.ucfirst(IMPORT_finish).'</p>' ;

	}

	else {
		foreach ($error as $e) {
			echo "<p>$e</p>" ;
		}
	}
}


	################# UPLOAD FORM ##########################

//end must be admin
};
?>
