<?php
if ((stristr( $_SERVER['REQUEST_URI'], "session.php") ) || ( !defined('T3_ABSPATH') )) die("no access");
#   TemaTres : aplicación para la gestión de vocabularios controlados#       #
#   TemaTres : web application to manage controlled vocabularies
#                                                                        #
#   Copyright (C) 2004-2008 Diego Ferreyra tematres@r020.com.ar
#   Distribuido bajo Licencia GNU Public License, versión 2 (de junio de 1.991) Free Software Foundation
#
###############################################################################################################
# Funciones para presentar formularios HTML. #
#


function HTMLformAssociateExistTerms($taskterm,$ARRAYtermino,$term_id="0")
{
	GLOBAL $new_relacion;
	
	switch ($taskterm)
	{
		case 'addRT':
			$nombre_pantalla=ucfirst(LABEL_AgregarRTexist).' <a title="'.$ARRAYtermino[titTema].'" href="'.$PHP_SELF.'?tema='.$ARRAYtermino[idTema].'">'.$ARRAYtermino[titTema].'</a>';				
			break;

		case 'addBT':
			$nombre_pantalla=sprintf(LABEL_AgregarTG,'<a title="'.$ARRAYtermino[titTema].'" href="'.$PHP_SELF.'?tema='.$ARRAYtermino[idTema].'">'.$ARRAYtermino[titTema].'</a>');
			break;

		case 'addFreeUF':
			$nombre_pantalla=sprintf(LABEL_existAgregarUP,'<a  title="'.$ARRAYtermino[titTema].'" href="'.$PHP_SELF.'?tema='.$ARRAYtermino[idTema].'">'.$ARRAYtermino[titTema].'</a>');
			break;

		case 'addFreeNT':
			$nombre_pantalla=sprintf(LABEL_existAgregarTE,'<a href="'.$PHP_SELF.'?tema='.$ARRAYtermino[idTema].'">'.$ARRAYtermino[titTema].'</a>');
			break;

		default: '';
	}

	if(doValue($_POST,FORM_LABEL_buscarTermino)){
			
	 $expresBusca=doValue($_POST,FORM_LABEL_buscarTermino);

	 //seleccionar SQL adecuado a la operacion
	 if(($taskterm=='addFreeNT') || ($taskterm=='addFreeUF'))
	 {
		 $sql_busca=SQLsearchFreeTerms("$expresBusca",$ARRAYtermino[idTema]);
	 }
	 else
	 {
		 $sql_busca=SQLbuscaTR("$ARRAYtermino[idTema]","$expresBusca");
	 }

	 $cant_result=SQLcount($sql_busca);

	 $search_leyenda='<h3>'.$cant_result.' '.MSG_ResultBusca.' '.$expresBusca.'".</h3>'."\n\r";
	  
	 if($cant_result>0)
	 {
		 $row_result='<ol class="demo" style="list-style-position:inside;">';

		 while($resulta_busca=$sql_busca->FetchRow())
		 {

			$css_class_MT=($resulta_busca["isMetaTerm"]==1) ? ' class="metaTerm" ' : '';
		 	$label_MT=($datosNT["isMetaTerm"]==1) ? NOTE_isMetaTerm : '';


			if($taskterm=='addBT')
			{
				 	$row_result.='<li><a '.$css_class_MT.' title="'.ucfirst(LABEL_seleccionar).' '.$label_MT.'" href="'.$PHP_SELF.'?rema_id='.$resulta_busca[0].'&amp;tema='.$ARRAYtermino[idTema].'&amp;taskrelations='.$taskterm.'">'.$resulta_busca[1].'</a></li>';

			}
			else 
			{
				 	$row_result.='<li><a '.$css_class_MT.' href="#" title="'.ucfirst(LABEL_seleccionar).' '.$label_MT.'" onclick=addrt(\''.$resulta_busca[0].'\',\''.$ARRAYtermino[idTema].'\',\''.$taskterm.'\') title="seleccionar">'.$resulta_busca[1].'</a></li>';
	
			}
			 
		 };
		 $row_result.='</ol>'."\n\r";
	 };// fin de if result

	};//fin de if buscar

	$rows.='<div id="bodyText">
	<a class="topOfPage" href="index.php?tema='.$ARRAYtermino[idTema].'" title="'.LABEL_Anterior.'">'.LABEL_Anterior.'</a>
<h1>'.LABEL_EditorTermino.'</h1>';

 	if ($new_relacion["log"]==true)
 	{
		$rows.='<p class="success">'.ucfirst(LABEL_saved).'</p>';
	}	

	$rows.=' <form class="formdiv" name="busca_rel" action="index.php?taskterm='.$taskterm.'&amp;tema='.$ARRAYtermino[idTema].'" method="post" onsubmit="return checkrequired(this)">'.LABEL_BuscaTermino.':';
	$rows.='  <fieldset>
    <legend>'.$nombre_pantalla.'</legend>

	  <input name="'.FORM_LABEL_buscarTermino.'" type="text" id="addExistTerm" size="15" maxlength="50"/>
  	  <input type="submit" class="submit ui-corner-all" name="boton" value="'.LABEL_Buscar.'"/>
	  <input type="button" class="submit ui-corner-all" name="cancelar" type="button" onClick="location.href=\'index.php?tema='.$ARRAYtermino[idTema].'\'" value="'.ucfirst(LABEL_Cancelar).'"/>
	  <input type="hidden" name="tema" value="'.$ARRAYtermino[idTema].'"/>
	  <input type="hidden" name="taskterm" value="'.$taskterm.'"/>	  
 </form>';
 
	$rows.=$search_leyenda;
	
	$rows.='<form name="addRelatrions" id="addRelatrions" action="index.php" method="get" >';
	$rows.=' <input type="hidden" name="tema" id="tema" value="'.$ARRAYtermino[idTema].'"/>';
	$rows.=' <input type="hidden" name="rema_id" id="rema_id" value="0"/>	  ';
	$rows.=' <input type="hidden" name="taskterm" id="taskterm" value="'.$taskterm.'"/>	  ';
	$rows.=' <input type="hidden" name="taskrelations" id="taskrelations" value="'.$taskterm.'"/>	  ';
  	
	
	$rows.=$row_result;		
	$rows.='</fieldset>';
	$rows.='</form>';
	$rows.='   </div>';

	return $rows;
}


/*
 * Form for edit or add terms
 3 casos:
 - Alta y edici�n de un t�rmino nuevo.
 - Alta de un t�rmino no preferido de un t�rmino preferido.
 - Alta de un t�rmino subordinado a un t�rmino.
 *
 */
function HTMLformEditTerms($taskterm,$ARRAYtermino="0")
{
	//SEND_KEY to prevent duplicated
	session_start();
	$_SESSION['SEND_KEY']=md5(uniqid(rand(), true));

	switch($taskterm)
	{
		case 'addTerm':// add term
			$nombre_pantalla=LABEL_AgregarT;
			$hidden='<input type="hidden"  name="alta_t" value="new" />';
			$hidden.='<div><label for="estado_id" accesskey="e">'.ucfirst(LABEL_Candidato).'</label><input type="checkbox" name="estado_id" id="estado_id" value="12" alt="'.ucfirst(LABEL_Candidato).'" /> </div>';
			$hidden.='<div><label for="isMetaTerm" accesskey="e">'.ucfirst(LABEL_meta_term).'</label><input type="checkbox" name="isMetaTerm" id="isMetaTerm" value="1" alt="'.ucfirst(LABEL_meta_term).'" /><p>'.NOTE_isMetaTermNote.'</p></div>';
			$help_rows='<p class="help">'.HELP_variosTerminos.'</p>';
			break;

		case 'editTerm'://Edici�n de un t�rmino $edit_id
			$nombre_pantalla=LABEL_editT.' <a href="'.$PHP_SELF.'?tema='.$ARRAYtermino[idTema].'">'.$ARRAYtermino[titTema].'</a>';
			$vista_titulo_tema=$ARRAYtermino[titTema];
			$vista_desc_tema=$ARRAYtermino[descTema];
			$hidden='<input type="hidden"  name="edit_id_tema" value="'.$ARRAYtermino[idTema].'" />';
			break;

		case 'addNT':// add narowwer term
			$nombre_pantalla=LABEL_AgregarTE.' <a href="'.$PHP_SELF.'?tema='.$ARRAYtermino[idTema].'">'.$ARRAYtermino[titTema].'</a>';
			$hidden.='<input type="hidden"  name="id_termino_sub" value="'.$ARRAYtermino[idTema].'" />';
			$help_rows='<p class="help">'.HELP_variosTerminos.'</p>';
			$t_relation='3';
			break;

		case 'addUF'://Alta de un t�rmino no preferido a $id_uf
			$nombre_pantalla=LABEL_AgregarUP.' <a href="'.$PHP_SELF.'?tema='.$ARRAYtermino[idTema].'">'.$ARRAYtermino[titTema].'</a>';
			$hidden='<input type="hidden"  name="id_termino_uf" value="'.$ARRAYtermino[idTema].'" />';
			$help_rows='<p class="help">'.HELP_variosTerminos.'</p>';
			$t_relation='4';
			break;

		case 'addRTnw'://Alta de un t�rmino no preferido a $id_uf
			$nombre_pantalla=LABEL_AgregarTR.' <a href="'.$PHP_SELF.'?tema='.$ARRAYtermino[idTema].'">'.$ARRAYtermino[titTema].'</a>';
			$hidden='<input type="hidden"  name="id_termino_rt" value="'.$ARRAYtermino[idTema].'" />';
			$help_rows='<p class="help">'.HELP_variosTerminos.'</p>';
			$t_relation='2';
			break;
	};

	$rows.='<div id="bodyText">';
	
	$rows.='<script type="text/javascript">$("#form-tvocab").validate({});</script>';
	$rows.='<a class="topOfPage" href="index.php?tema='.$ARRAYtermino[idTema].'" title="'.LABEL_Anterior.'">'.LABEL_Anterior.'</a>
			<h1>'.LABEL_EditorTermino .'</h1>';
	$rows.='<form class="myform" id="alta_t" name="alta_t" action="index.php" method="post">
		 <fieldset>
		<legend>'.$nombre_pantalla.'</legend>

	<div><label for="'.FORM_LABEL_termino .'" accesskey="t">'.LABEL_Termino.'</label>
	<textarea rows="2" cols="60" name="'.FORM_LABEL_termino.'" id="addTerms">'.$vista_titulo_tema.'</textarea>
	</div>';
	
	
	if(in_array($t_relation,array(2,3,4)))
	{
		$SQLtypeRelations=SQLtypeRelations($t_relation);
		
		if(SQLcount($SQLtypeRelations)>0)
		{
			while($ARRAYtypeRelations=$SQLtypeRelations->FetchRow())
			{
				$arraySelectTypeRelations[]=$ARRAYtypeRelations[rel_rel_id].'#'.$ARRAYtypeRelations[rr_value];
				$neutralLabel=LABELrelTypeSYS($ARRAYtypeRelations[t_relation]);
			}
			
			
			$rows.='<div class="forminputdiv"><label for="rel_rel_id" accesskey="r">'.ucfirst(LABEL_relationSubType).'<span class="small">('.LABEL_optative.')</span></label>';
			$rows.='<select id="t_rel_rel_id" name="t_rel_rel_id"><option>'.ucfirst(LABEL_seleccionar).'</option>';
			$rows.=doSelectForm($arraySelectTypeRelations,"");
			$rows.='</select>';
			$rows.='</div>';
		}
	}
	$rows.=$hidden;
	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="hidden"  name="ks" id="ks" value="'.$_SESSION["SEND_KEY"].'"/>';
	$rows.='<input type="button"  class="submit ui-corner-all" name="cancelar" type="button" onClick="location.href=\'index.php?tema='.$ARRAYtermino[idTema].'\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
	$rows.='<input type="submit"  class="submit ui-corner-all" name="boton" value="'.LABEL_Enviar.'"/>';

	$rows.='</div>';
	$rows.='  </fieldset>';
	$rows.='</form>';
	$rows.=$help_rows;
	$rows.='</div>';
	
	$rows.='<script id="tvocab_script" type="text/javascript">
	$(document).ready(function() {
	var validator = $("#alta_t").validate({
		rules: {\''.FORM_LABEL_termino.'\':  {required: true},
				},
				debug: true,
				errorElement: "label",
				 submitHandler: function(form) {
				   form.submit();
				 }
				
		});	
	});
	</script>	';
	
	return $rows;
}




/*
 Advanced search form
 *
 */
function HTMLformAdvancedSearch($array)
{

	GLOBAL $CFG;

	$array=XSSpreventArray($array);
	
	
	$rows.='<form class="myform" name="advancedsearch" action="index.php#xstring" method="get" onsubmit="return checkrequired(this)">';
	$rows.='  <fieldset>';

	$rows.=' <legend>'.ucfirst(LABEL_BusquedaAvanzada).'</legend>';

	$LABEL_Termino=ucfirst(LABEL_Termino);
	$LABEL_esNoPreferido=ucfirst(LABEL_esNoPreferido);
	$LABEL_CODE=ucfirst(LABEL_CODE);
	$LABEL_NOTE=ucfirst(LABEL_nota);
	$LABEL_META_TERM=ucfirst(LABEL_meta_term);
	$LABEL_TARGET_TERM=ucfirst(LABEL_TargetTerm);

	$arrayWS=array("t#$LABEL_Termino","mt#$LABEL_META_TERM");

	$arrayVocabStats=ARRAYresumen($_SESSION[id_tesa],"G","");

	if($arrayVocabStats["cant_up"]>0)
	{
		array_push($arrayWS,"uf#$LABEL_esNoPreferido");
	}

	if($arrayVocabStats["cant_notas"]>0)
	{
		array_push($arrayWS,"n#$LABEL_NOTE");
	}

	if($CFG["_SHOW_CODE"]=='1')
	{
		array_push($arrayWS,"c#$LABEL_CODE");
	}

	if($arrayVocabStats["cant_term2tterm"])
	{
		array_push($arrayWS,"tgt#$LABEL_TARGET_TERM");
	}

	/*
	 solo si hay m�s de un opci�n
	 */
	if(count($arrayWS)>1)
	{
		$rows.='<div><label for="ws" accesskey="f">'.ucfirst(LABEL_QueBuscar).'</label>';
		$rows.='<select id="ws" name="ws">';
		$rows.=doSelectForm($arrayWS,"$_GET[ws]");
		$rows.='</select>';
		$rows.='</div>';
	}

	$rows.='<div><label for="xstring" accesskey="s">'.ucfirst(LABEL_BuscaTermino).'</label>';
	$rows.='<input name="xstring" type="text" id="xstring" size="25" maxlength="50" value="'.$array["xstring"].'"/>';
	$rows.='</div>';

	$rows.='<div><label for="isExactMatch" accesskey="f">'.ucfirst(LABEL_esFraseExacta).'</label>';
	$rows.='<input name="isExactMatch" type="checkbox" id="isExactMatch" value="1" '.do_check('1',$_GET["isExactMatch"],"checked").'/>';
	$rows.='</div>';

	//Evaluar si hay top terms
	$sqlTopTerm=SQLverTopTerm();

	if(SQLcount($sqlTopTerm)>0)
	{
		while ($arrayTopTerms=$sqlTopTerm->FetchRow())
		{
			$formSelectTopTerms[]=$arrayTopTerms[tema_id].'#'.$arrayTopTerms[tema];
		}
		$rows.='<div><label for="hasTopTerm" accesskey="t">'.ucfirst(LABEL_TopTerm).'</label>';
		$rows.='<select id="hasTopTerm" name="hasTopTerm">';
		$rows.='<option value="">'.ucfirst(LABEL_Todos).'</option>';
		$rows.=doSelectForm($formSelectTopTerms,"$_GET[hasTopTerm]");
		$rows.='</select>';
		$rows.='</div>';
	}

	//Evaluar si hay notas
	if (is_array($arrayVocabStats["cant_notas"]))
	{

		$LabelNB='NB#'.LABEL_NB;
		$LabelNH='NH#'.LABEL_NH;
		$LabelNA='NA#'.LABEL_NA;
		$LabelNP='NP#'.LABEL_NP;
		$LabelNC='NC#'.LABEL_NC;

		$sqlNoteType=SQLcantNotas();
		$arrayNoteType=array();
		 
		while ($arrayNotes=$sqlNoteType->FetchRow()){
			if($arrayNotes[cant]>0)
			{

			//nota privada no	
			if(($_SESSION[$_SESSION["CFGURL"]][ssuser_nivel]) || ($arrayNotes["value_id"]!=='11'))
				{
				$varNoteType=(in_array($arrayNotes["value_id"],array(8,9,10,11,15))) ? arrayReplace(array(8,9,10,11,15),array($LabelNA,$LabelNH,$LabelNB,$LabelNP,$LabelNC),$arrayNotes["value_id"]) : $arrayNotes["value_code"].'#'.$arrayNotes["value"];
				$varNoteType.=' ('.$arrayNotes[cant].')';								
				array_push($arrayNoteType, $varNoteType);
				}
			}
		};


		/*
		 Si hay m�s de un tipo de nota
		 */
		if(count($arrayVocabStats["cant_notas"])>0)
		{
			$rows.='<div><label for="hasNote" accesskey="n">'.ucfirst(LABEL_tipoNota).'</label>';
			$rows.='<select id="hasNote" name="hasNote">';
			$rows.='<option value="">'.ucfirst(LABEL_Todos).'</option>';
			$rows.=doSelectForm($arrayNoteType,"$_GET[hasNote]");
			$rows.='</select>';
			$rows.='</div>';
		}
	}


	//Evaluar si hay terminos
	$sqlTermsByDates=SQLtermsByDate();

	if(SQLcount($sqlTermsByDates)>0)
	{
		GLOBAL $MONTHS;
		while ($arrayTermsByDates=$sqlTermsByDates->FetchRow())
		{
			//normalizacion de fechas
			$arrayTermsByDates[months]=(strlen($arrayTermsByDates[months])==1) ? '0'.$arrayTermsByDates[months] : $arrayTermsByDates[months];

			$formSelectByDate[]=$arrayTermsByDates[years].'-'.$arrayTermsByDates[months].'#'.$MONTHS["$arrayTermsByDates[months]"].'/'.$arrayTermsByDates[years].' ('.$arrayTermsByDates[cant].')';
		}

		$rows.='<div><label for="fromDate" accesskey="d">'.ucfirst(LABEL_DesdeFecha).'</label>';
		$rows.='<select id="fromDate" name="fromDate">';
		$rows.='<option value="">'.ucfirst(LABEL_Todos).'</option>';
		$rows.=doSelectForm($formSelectByDate,"$_GET[fromDate]");
		$rows.='</select>';
		$rows.='</div>';
	};

	//Evaluar si hay candidatos
	$sqlTermsByDeep=SQLTermDeep();

	if(SQLcount($sqlTermsByDeep)>1)
	{
		while ($arrayTermsByDeep=$sqlTermsByDeep->FetchRow())
		{
			$formSelectByDeep[]=$arrayTermsByDeep[tdeep].'#'.$arrayTermsByDeep[tdeep].' ('.$arrayTermsByDeep[cant].')';
		}
		$rows.='<div><label for="termDeep" accesskey="e">'.ucfirst(LABEL_ProfundidadTermino).'</label>';
		$rows.='<select id="termDeep" name="termDeep">';
		$rows.='<option value="">'.ucfirst(LABEL_Todos).'</option>';
		$rows.=doSelectForm($formSelectByDeep,"$_GET[termDeep]");
		$rows.='</select>';
		$rows.='</div>';
	};

	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="hidden"  name="xsearch" id="xsearch" value="1"/>';
	$rows.='<input type="button"  name="cancelar" type="button" onClick="location.href=\'index.php\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
	$rows.='<input type="submit"  id="boton" name="boton" value="'.LABEL_Enviar.'"/>';

	$rows.='</div>';
	
	$rows.='  </fieldset>';
	$rows.='</form>';

	if($_GET[boton]==LABEL_Enviar)
	{
		$rows.=HTMLadvancedSearchResult($array);
	}


return $rows;
}

/*
 Term Report form
 *
 */
function HTMLformAdvancedTermReport($array)
{

	GLOBAL $CFG;

	$LABEL_Termino=ucfirst(LABEL_Termino);
	$LABEL_esNoPreferido=ucfirst(LABEL_esNoPreferido);
	$LABEL_CODE=ucfirst(LABEL_CODE);
	$LABEL_NOTE=ucfirst(LABEL_nota);
	$LABEL_TARGET_TERM=ucfirst(LABEL_TargetTerm);
	$LABEL_haveEQ=LABEL_haveEQ;
	$LABEL_nohaveEQ=LABEL_nohaveEQ;
	$LABEL_start=LABEL_start;
	$LABEL_end=LABEL_end;
	$LABEL_equalThisWord=LABEL_equalThisWord;

	$arrayVocabStats=ARRAYresumen($_SESSION[id_tesa],"G","");


   $rows.='<form class="myform" name="advancedreport" action="index.php#csv" method="get">';
	$rows.=' <fieldset>
    <legend>'.ucfirst(LABEL_FORM_advancedReport).'</legend>';

	$arrayWS=array("t#$LABEL_Termino");

	if($arrayVocabStats["cant_up"]>0)
	{
		array_push($arrayWS,"uf#$LABEL_esNoPreferido");
	}

	if($arrayVocabStats["cant_notas"]>0)
	{
		array_push($arrayWS,"n#$LABEL_NOTE");
	}

	//Evaluar si hay top terms
	$sqlTopTerm=SQLverTopTerm();

	if(SQLcount($sqlTopTerm)>0)
	{
		while ($arrayTopTerms=$sqlTopTerm->FetchRow())
		{
			$formSelectTopTerms[]=$arrayTopTerms[tema_id].'#'.$arrayTopTerms[tema];
		}
		$rows.='<div><label for="hasTopTerm" accesskey="t">'.ucfirst(LABEL_TopTerm).'</label>';
		$rows.='<select id="hasTopTerm" name="hasTopTerm">';
		$rows.='<option value="">'.ucfirst(LABEL_FORM_nullValue).'</option>';
		$rows.=doSelectForm($formSelectTopTerms,"$_GET[hasTopTerm]");
		$rows.='</select>';
		$rows.='</div>';
	}

	//Evaluar si hay notas
	if (is_array($arrayVocabStats["cant_notas"]))
	{

		$LabelNB='NB#'.LABEL_NB;
		$LabelNH='NH#'.LABEL_NH;
		$LabelNA='NA#'.LABEL_NA;
		$LabelNP='NP#'.LABEL_NP;
		$LabelNC='NC#'.LABEL_NC;

		$sqlNoteType=SQLcantNotas();
		$arrayNoteType=array();
		 
		while ($array=$sqlNoteType->FetchRow()){
			if($array[cant]>0)
			{
				$varNoteType=(in_array($array["value_id"],array(8,9,10,11,15))) ? arrayReplace(array(8,9,10,11,15),array($LabelNA,$LabelNH,$LabelNB,$LabelNP,$LabelNC),$array["value_id"]) : $array["value_code"].'#'.$array["value"];
				$varNoteType.=' ('.$array[cant].')';

				array_push($arrayNoteType, $varNoteType);
			}
		};
		/*
		 Si hay m�s de un tipo de nota
		 */
		if(count($arrayVocabStats["cant_notas"])>0)
		{
			$rows.='<div><label for="hasNote" accesskey="n">'.ucfirst(LABEL_FORM_haveNoteType).'</label>';
			$rows.='<select id="hasNote" name="hasNote">';
			$rows.='<option value="">'.ucfirst(LABEL_FORM_nullValue).'</option>';
			$rows.=doSelectForm($arrayNoteType,"$_GET[hasNote]");
			$rows.='</select>';
			$rows.='</div>';
		}
	}


	//Evaluar si hay terminos
	$sqlTermsByDates=SQLtermsByDate();

	if(SQLcount($sqlTermsByDates)>0)
	{
		GLOBAL $MONTHS;
		while ($arrayTermsByDates=$sqlTermsByDates->FetchRow())
		{
			//normalizacion de fechas
			$arrayTermsByDates[months]=(strlen($arrayTermsByDates[months])==1) ? '0'.$arrayTermsByDates[months] : $arrayTermsByDates[months];

			$formSelectByDate[]=$arrayTermsByDates[years].'-'.$arrayTermsByDates[months].'#'.$MONTHS["$arrayTermsByDates[months]"].'/'.$arrayTermsByDates[years].' ('.$arrayTermsByDates[cant].')';
		}

		$rows.='<div><label for="fromDate" accesskey="d">'.ucfirst(LABEL_DesdeFecha).'</label>';
		$rows.='<select id="fromDate" name="fromDate">';
		$rows.='<option value="">'.ucfirst(LABEL_FORM_nullValue).'</option>';
		$rows.=doSelectForm($formSelectByDate,"$_GET[fromDate]");
		$rows.='</select>';
		$rows.='</div>';
	};


	if($arrayVocabStats["cant_term2tterm"])
	{

		$sql=SQLtargetVocabulary("1");
		$array_vocabularios=array();
		while($array=$sql->FetchRow())
		{
			if($array[vocabulario_id]!=='1'){
				//vocabularios que no sean el vocabulario principal
				array_push($array_vocabularios,$array[tvocab_id].'#'.FixEncoding($array[tvocab_label]));
			}
		};

		$rows.='<div><label for="report_tvocab_id" accesskey="t">'.ucfirst(LABEL_TargetTerms).'</label>';
		$rows.='<select id="csv_tvocab_id" name="csv_tvocab_id">';
		$rows.='<option value="">'.ucfirst(LABEL_FORM_nullValue).'</option>';
		$rows.=doSelectForm($array_vocabularios,"$_GET[csv_tvocab_id]");
		$rows.='</select>';

		$rows.='<select id="mapped" name="mapped">';
		$rows.=doSelectForm(array("y#$LABEL_haveEQ","n#$LABEL_nohaveEQ"),"$_GET[mapped]");
		$rows.='</select>';
		$rows.='</div>';
	}

	$sql=SQLdatosVocabulario();

	if(SQLcount($sql)>'1'){
		//Hay vobularios de referencia
		$array_ivocabularios=array();
		while($array=$sql->FetchRow())
		{
			if($array[vocabulario_id]!=='1')
			{
				//vocabularios que no sean el vocabulario principal
				array_push($array_ivocabularios,$array[vocabulario_id].'#'.$array[titulo]);
			}
		};

		$rows.='<div><label for="report_tvocab_id" accesskey="t">'.ucfirst(LABEL_vocabulario_referencia).'</label>';
		$rows.='<select id="csv_itvocab_id" name="csv_itvocab_id">';
		$rows.='<option value="">'.ucfirst(LABEL_FORM_nullValue).'</option>';
		$rows.=doSelectForm($array_ivocabularios,"$_GET[csv_itvocab_id]");
		$rows.='</select>';

		$rows.='<select id="int_mapped" name="int_mapped">';
		$rows.=doSelectForm(array("y#$LABEL_haveEQ","n#$LABEL_nohaveEQ"),"$_GET[mapped]");
		$rows.='</select>';
		$rows.='</div>';
	}

	//only for admin
	if($_SESSION[$_SESSION["CFGURL"]][ssuser_nivel]=='1')
	{
		$sqlUsers=SQLdatosUsuarios();
		if(SQLcount($sqlUsers)>1)
		{
			while ($arrayUsers=$sqlUsers->FetchRow())
			{
				$formSelectUsers[]=$arrayUsers[id].'#'.$arrayUsers[apellido].', '.$arrayUsers[nombres];
			}
			$rows.='<div><label for="user_id" accesskey="u">'.ucfirst(MENU_Usuarios).'</label>';
			$rows.='<select id="byuser_id" name="byuser_id">';
			$rows.='<option value="">'.ucfirst(LABEL_FORM_nullValue).'</option>';
			$rows.=doSelectForm($formSelectUsers,"$_GET[byuser_id]");
			$rows.='</select>';
			$rows.='</div>';
		}
	}


	$rows.='<div><label for="csvstring" accesskey="s">'.ucfirst(LABEL_haveWords).'</label>';
	$rows.='<select id="w_string" name="w_string">';
	$rows.=doSelectForm(array("x#$LABEL_equalThisWord","s#$LABEL_start","e#$LABEL_end"),"$_GET[w_string]");
	$rows.='</select>';
	$rows.='<input name="csvstring" type="text" id="csvstring" size="5" maxlength="10" value="'.$_GET[csvstring].'"/>';
	$rows.='</div>';

	if ($CFG["_CHAR_ENCODE"]=='utf-8')
	{
		$rows.='<div><label for="csv_encode" accesskey="e">'.ucfirst(LABEL_encode).' latin1</label>';
		$rows.='<input name="csv_encode" type="checkbox" id="csv_encode" value="latin1" '.do_check('latin1',$_GET[csv_encode],"checked").'/>';
		$rows.='</div>';
	}

	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="hidden"  name="mod" id="mod" value="csv"/>';
	$rows.='<input type="hidden"  name="task" id="mod" value="csv1"/>';
	$rows.='<input type="button"   class="submit ui-corner-all" name="cancelar" type="button" onClick="location.href=\'index.php\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
	$rows.='<input type="submit"   class="submit ui-corner-all" id="boton" name="boton" value="'.ucfirst(LABEL_Guardar).'"/>';

	$rows.='</div>';
	$rows.='  </fieldset>';
	$rows.='</form>';

	return $rows;
}



/*
 Simple Term report by
 */
function HTMLformSimpleTermReport($array)
{

	GLOBAL $CFG;

	$LABEL_FreeTerms=ucfirst(LABEL_terminosLibres);
	$LABEL_DuplicatedTerms=ucfirst(LABEL_terminosRepetidos);
	$LABEL_PoliBT=ucfirst(LABEL_poliBT);
	$LABEL_candidate=ucfirst(LABEL_Candidato);
	$LABEL_rejected=ucfirst(LABEL_Rechazado);
	$LABEL_termsxNTterms=ucfirst(LABEL_termsxNTterms);
	$LABEL_termsXcantWords=ucfirst(LABEL_termsXcantWords);
	$LABEL_termsIsMetaTerms=ucfirst(LABEL_meta_terms);
	$LABEL_termsXrelatedTerms=ucfirst(LABEL_relatedTerms);
	$LABEL_termsXnonPreferedTerms=ucfirst(LABEL_nonPreferedTerms);

   $rows.='<form class="myform" name="simprereport" id="simprereport" action="index.php" method="get">';
	$rows.=' <fieldset>
    <legend>'.ucfirst(LABEL_FORM_simpleReport).'</legend>';


	$rows.='<div><label for="simpleReport" accesskey="s">'.ucfirst(LABEL_seleccionar).'</label>';
	$rows.='<select id="task" name="task">';
	$rows.='<option value="">'.ucfirst(LABEL_seleccionar).'</option>';
	$rows.=doSelectForm(array("csv2#$LABEL_FreeTerms","csv3#$LABEL_DuplicatedTerms","csv4#$LABEL_PoliBT","csv7#$LABEL_termsxNTterms","csv8#$LABEL_termsXcantWords","csv9#$LABEL_termsIsMetaTerms","csv10#$LABEL_termsXrelatedTerms","csv11#$LABEL_termsXnonPreferedTerms", "csv5#$LABEL_candidate","csv6#$LABEL_rejected"),"$_GET[task]");
	$rows.='</select>';
	$rows.='</div>';


	if ($CFG["_CHAR_ENCODE"]=='utf-8')
	{
		$rows.='<div><label for="csv_encode_simpre" accesskey="s">'.ucfirst(LABEL_encode).' latin1</label>';
		$rows.='<input name="csv_encode" type="checkbox" id="csv_encode_simpre" value="latin1" '.do_check('latin1',$_GET[csv_encode],"checked").'/>';
		$rows.='</div>';
	}


	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="hidden"  name="mod" id="mod" value="csv"/>';
	$rows.='<input type="button"   class="submit ui-corner-all" name="cancelar" type="button" onClick="location.href=\'index.php\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
	$rows.='<input type="submit"   class="submit ui-corner-all" id="boton" name="boton" value="'.ucfirst(LABEL_Guardar).'"/>';
	$rows.='</div>';
	$rows.='  </fieldset>';
	$rows.='</form>';

	return $rows;
}




/*
 Register web services provider
 *
 */
function HTMLformTargetVocabulary($tvocab_id="0")
{

	GLOBAL $CFG;

	$array=($tvocab_id>0) ? ARRAYtargetVocabulary($tvocab_id) : array();

	$array[tvocab_status] = (is_numeric($array[tvocab_status])) ? $array[tvocab_status] : '1';

	$doAdmin= ($array[tvocab_id]>0) ? 'saveTargetVocabulary' : 'addTargetVocabulary';

	// Preparado de datos para el formulario ///
	$arrayLang=array();
	foreach ($CFG["ISO639-1"] as $langs) {
		array_push($arrayLang,"$langs[0]#$langs[1]");
		};

	//SEND_KEY to prevent duplicated
	session_start();
	$_SESSION['TGET_SEND_KEY']=md5(uniqid(rand(), true));
	
	$rows='<script type="text/javascript">$("#form-tvocab").validate({});</script>';
	
   	$rows.='<form class="myform" id="form-tvocab" name="abmTargetVocabulary" action="admin.php" method="post" >';
	$rows.=' <fieldset><legend><a href="admin.php?vocabulario_id=list">'.ucfirst(LABEL_lcConfig).'</a> &middot; '.ucfirst(LABEL_TargetVocabularyWS).'</legend>';

	if($array[tvocab_id])
	{
		$rows.='<div><label for="tvocab_label">'.ucfirst(LABEL_Titulo).'</label>';
		$rows.='<a id="tvocab_title" href="'.$array[tvocab_url].'">'.$array[tvocab_title].'</a>';
		$rows.='</div>';

		$link2tterms='<a href="admin.php?doAdmin=seeTermsTargetVocabulary&amp;tvocab_id='.$array[tvocab_id].'">'.$array[cant].' '.LABEL_Terminos.'</a>';
	}


	$rows.='<div><label for="tvocab_label" accesskey="l">'.ucfirst(LABEL_tvocab_label).'</label>';
	$rows.='<input name="tvocab_label" type="text" id="tvocab_label" size="25" maxlength="50" value="'.$array[tvocab_label].'"/>';
	$rows.='</div>';

	$rows.='<div><label for="tvocab_tag" accesskey="t">'.ucfirst(LABEL_tvocab_tag).'</label>';
	$rows.='<input class="short" name="tvocab_tag" type="text" id="tvocab_tag"  size="5" maxlength="5" value="'.$array[tvocab_tag].'"/>';
	$rows.='</div>';

	$rows.='<div><label for="tvocab_lang" accesskey="t">'.ucfirst(LABEL_Idioma).'</label>';
	$rows.='<select id="tvocab_lang" name="tvocab_lang">'.doSelectForm($arrayLang,$array[tvocab_lang]).'</select>';
	$rows.='</div>';

	$rows.='<div><label for="tvocab_uri_service" accesskey="u">'.ucfirst(LABEL_tvocab_uri_service).'</label>';
	if($array[tvocab_id])
	{
		$rows.='<span id="tvocab_uri_service">'.$array[tvocab_uri_service].'</span>';
	}
	else
	{
		$rows.='<input name="tvocab_uri_service" type="text" id="tvocab_uri_service" size="50" maxlength="250"  value="'.$array[tvocab_uri_service].'"/>';
	}

	$rows.='</div>';

	$rows.='<div><label for="tvocab_status" accesskey="u">'.ucfirst(LABEL_enable).'</label>';
	$rows.='<input name="tvocab_status" type="checkbox" id="tvocab_status" value="1" '.do_check('1',$array[tvocab_status],"checked").'/>'.$link2tterms;
	$rows.='</div>';

	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="button"  class="submit ui-corner-all"  name="cancelar" type="button" onClick="location.href=\'admin.php\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
	$rows.='<input type="submit"  class="submit ui-corner-all"  id="boton" name="botonTargetVocabulary" value="'.LABEL_Enviar.'"/>';
	$rows.='<input type="hidden"  id="tvocab_id" name="tvocab_id" value="'.$array[tvocab_id].'"/>';
	$rows.='<input type="hidden"  name="doAdmin" id="doAdmin" value="'.$doAdmin.'"/>';
	$rows.='<input type="hidden"  name="ks" id="ks" value="'.$_SESSION["TGET_SEND_KEY"].'"/>';
	$rows.='</div>';
	$rows.='</form>';
	$rows.='  </fieldset>';

	$rows.='<script id="tvocab_script" type="text/javascript">
	$(document).ready(function() {
	var validator = $("#form-tvocab").validate({
		rules: {\'tvocab_label\':  {required: true},
				\'tvocab_uri_service\': {required: true, url: true }
				},
				debug: true,
				errorElement: "label",
				 submitHandler: function(form) {
				   form.submit();
				 }
				
	});	
});
</script>	';

	
	return $rows;
}


/*
 Asociaci�n con datos provistos por web services terminol�gicos TemaTres
 */
function HTMLformAssociateTargetTerms($ARRAYtermino,$term_id="0")
{

	$sql=SQLtargetVocabulary("1");

	$rows='<div id="bodyText">';
	$rows.='<a class="topOfPage" href="index.php?tema='.$ARRAYtermino[idTema].'" title="'.LABEL_Anterior.'">'.LABEL_Anterior.'</a>';
	$rows.='<h1>'.LABEL_EditorTermino.'</h1>';

	if(SQLcount($sql)=='0'){
		//No hay vocabularios de referencia, solo vocabulario principal
		$rows.='<p class="error">'.ucfirst(LABEL_NO_vocabulario_referencia).'</p>';
	} else {
		//Hay vobularios de referencia
		$array_vocabularios=array();
		while($array=$sql->FetchRow())
		{
			if($array[vocabulario_id]!=='1')
			{
				//vocabularios que no sean el vocabulario principal
				array_push($array_vocabularios,$array[tvocab_id].'#'.FixEncoding($array[tvocab_label]));
			}
		};

		$rows.='<form class="myform" name="alta_tt" id="alta_tt" action="index.php" method="get">';
		$rows.='  <fieldset>';
		$rows.='    <legend>'.ucfirst(LABEL_relacion_vocabulario).' <a href="index.php?tema='.$ARRAYtermino[idTema].'">'.$ARRAYtermino[titTema].'</a></legend>';
				
				
		$rows.='<div class="clear">';
		$rows.='<label for="tvocab_id" accesskey="t">';
		$rows.=ucfirst(FORM_LABEL_nombre_vocabulario).'</label>';
		$rows.='<select id="tvocab_id" name="tvocab_id">';
		$rows.=doSelectForm($array_vocabularios,"$_GET[tvocab_id]");
		$rows.='</select><br/>';
		$rows.='</div>';

		//~ $rows.='	<legend class="ui-widget-header ui-corner-all">'.ucfirst(LABEL_selectMapMethod).'</legend>';
		
		//Configurar opcion búsqueda por código
		$arrayOptions=(strlen($ARRAYtermino["code"])>0) ? array('string#'.ucfirst(LABEL_string2search),'reverse#'.ucfirst(LABEL_reverseMappign),'code#'.LABEL_CODE) : array('string#'.ucfirst(LABEL_string2search),'reverse#'.ucfirst(LABEL_reverseMappign));
		$rows.='<div>';
		$rows.='<label for="search_by" accesskey="b">';
		$rows.=ucfirst(LABEL_selectMapMethod).'</label>';
		$rows.='<select id="search_by" name="search_by" onChange="mostrar(this.value);">';
		$rows.=doSelectForm($arrayOptions,"$_GET[search_by]");
		$rows.='</select>';

		$rows.='</div>';

		$display=(in_array($_GET[search_by],array('reverse','code'))) ? 'style="display: none;"' : '';
		$rows.='<div id="by_string" '.$display.'>';
		$rows.='<label for="string2search" accesskey="s">';
		$rows.=LABEL_Buscar.'</label>';

		$string2search = ($_GET[string2search]) ? $_GET[string2search] : $ARRAYtermino[titTema];

		$rows.='<input name="string2search" type="text" id="string2search" size="15" maxlength="50" value="'.$string2search.'"/>';
		$rows.='</div>';

		$rows.='<div align="center">';
		$rows.='<input class="submit ui-corner-all" type="button"  name="cancelar" type="button" onClick="location.href=\'index.php?tema='.$ARRAYtermino[idTema].'\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
		$rows.='  <input  class="submit ui-corner-all" type="submit" name="boton" value="'.LABEL_Buscar.'"/>';

		$rows.='<input type="hidden" name="tema" value="'.$ARRAYtermino[idTema].'"/>
		<input type="hidden" name="taskterm" value="findTargetTerm"/>';	  
		$rows.='</div>';
		$rows.='  </fieldset>';
		$rows.='</form>';
		
		
	}

	if(($_GET["string2search"]) && ($_GET["tvocab_id"])){
		require_once(T3_ABSPATH . 'common/include/vocabularyservices.php')	;

		$arrayVocab=ARRAYtargetVocabulary($_GET["tvocab_id"]);

		switch ($_GET["search_by"]) {
			case 'string':
			$arrayTerm=xmlVocabulary2array($arrayVocab[tvocab_uri_service].'?task=search&arg='.urlencode($_GET["string2search"]));
			break;

			case 'code':
			$arrayTerm=xmlVocabulary2array($arrayVocab[tvocab_uri_service].'?task=fetchCode&arg='.urlencode($ARRAYtermino["code"]));			
			break;

			case 'reverse':
			$arrayTerm=xmlVocabulary2array($arrayVocab[tvocab_uri_service].'?task=fetchSourceTerms&arg='.rawurlencode($ARRAYtermino["titTema"]));			
			break;
		
			default :
			$arrayTerm=xmlVocabulary2array($arrayVocab[tvocab_uri_service].'?task=search&arg='.urlencode($_GET[string2search]));
		
			break;
		}
		

		$rows.=HTMLtargetVocabularySearchResult($arrayTerm,$_GET[string2search],$arrayVocab,$ARRAYtermino[idTema]);


	};//fin de if buscar
	$rows.='   </div>';

	$rows.='<script type="text/javascript">
	function mostrar(id) {
	((id == "string") ?	$("#by_string").show() : $("#by_string").hide());	
	}
</script>';
	return $rows;
}





function HTMLtargetVocabularySearchResult($array,$string_search,$arrayVocab,$tema_id){

	//SEND_KEY to prevent duplicated
	session_start();
	$_SESSION['SEND_KEY']=md5(uniqid(rand(), true));

	$tag_type='ol';

	$div_title='Resultados para <i>'.$string_search.'</i> en '.FixEncoding($arrayVocab[tvocab_title]).': ';

	$rows.='<h3>'.$div_title.' ('.$array["resume"]["cant_result"].')</h3><'.$tag_type.'>';

	if($array["resume"]["cant_result"]>"0")	{
		foreach ($array["result"] as $key => $value){
			while (list( $k, $v ) = each( $value )){
				$i=++$i;
				//Controlar que no sea un resultado unico
				if(is_array($v)){
					$rows.='<li>';
					$rows.='<a title="" href="'.$PHP_SELF.'?tema='.$tema_id.'&amp;tvocab_id='.$arrayVocab[tvocab_id].'&amp;tgetTerm_id='.$v[term_id].'&amp;taskrelations=addTgetTerm&amp;ks='.$_SESSION["SEND_KEY"].'">'.FixEncoding($v[string]).'</a>';
					$rows.='</li>';

				} else {

					//controlar que sea la ultima
					if(count($value)==$i){
						$rows.='<li>';
						$rows.='<a title="" href="'.$PHP_SELF.'?tema='.$tema_id.'&amp;tvocab_id='.$arrayVocab[tvocab_id].'&amp;tgetTerm_id='.$value[term_id].'&amp;taskrelations=addTgetTerm&amp;ks='.$_SESSION["SEND_KEY"].'">'.FixEncoding($value[string]).'</a>';
						$rows.='</li>';
					}
				}
			}
		}
		$rows.='</'.$tag_type.'>';
	}

	/*
	 if($cantResult==0)
	 {
	 $rows.=HTMLdidYouMean($arrayVocab,$string_search,$tema_id);
	 }
	 */
	return $rows;
}




function HTMLformAltaEquivalenciaTermino($ARRAYTermino){

	$LabelEE=id_EQ.'#'.LABEL_termino_equivalente;
	$LabelIE=id_EQ_PARCIAL.'#'.LABEL_termino_parcial_equivalente;
	$LabelNE=id_EQ_NO.'#'.LABEL_termino_no_equivalente;

	$sql=SQLdatosVocabulario();

	//SEND_KEY to prevent duplicated
	session_start();
	$_SESSION['SEND_KEY']=md5(uniqid(rand(), true));

	$rows='<div id="bodyText">';
	$rows.='<script type="text/javascript">$("#form-tvocab").validate({});</script>';

	$rows.='<a class="topOfPage" href="index.php?tema='.$ARRAYtermino[idTema].'" title="'.LABEL_Anterior.'">'.LABEL_Anterior.'</a>';
	$rows.='<h1>'.ucfirst(LABEL_EditorTermino).'</h1>';

	if(SQLcount($sql)=='1'){
		//No hay vocabularios de referencia, solo vocabulario principal
		$rows.='<p class="error">'.ucfirst(LABEL_NO_vocabulario_referencia).'</p>';
	} else {
		//Hay vobularios de referencia
		$array_vocabularios=array();
		while($array=$sql->FetchRow())
		{
			if($array[vocabulario_id]!=='1')
			{
				//vocabularios que no sean el vocabulario principal
				array_push($array_vocabularios,$array[vocabulario_id].'#'.$array[titulo]);
			}
		};

		$rows.='<form class="myform" name="alta_eqt" id="alta_eqt" action="index.php" method="post">';
		$rows.='  <fieldset>';
		$rows.='    <legend>'.ucfirst(LABEL_relacion_vocabulario).' <a href="index.php?tema='.$ARRAYTermino[idTema].'">'.$ARRAYTermino[titTema].'</a></legend>';


		$rows.='<div><label for="'.FORM_LABEL_termino.'" accesskey="t">'.ucfirst(LABEL_Termino).'</label>';
		$rows.='<textarea rows="2" cols="50" name="'.FORM_LABEL_termino.'" id="'.FORM_LABEL_termino.'"></textarea>';
		$rows.='</div>';
		$rows.='<div>';
		$rows.='<label for="'.ref_vocabulario_id.'" accesskey="v">'.ucfirst(FORM_LABEL_nombre_vocabulario).'</label>';
		$rows.='<select id="ref_vocabulario_id" name="ref_vocabulario_id">';
		//$rows.='<optgroup label="'.FORM_LABEL_nombre_vocabulario.'">';
		$rows.=doSelectForm($array_vocabularios,"");
		$rows.='</optgroup>';
		$rows.='</select>';
		$rows.='</div>';

		$rows.='<div>';
		$rows.='<label for="tipo_equivalencia" accesskey="e">';
		$rows.=ucfirst(FORM_LABEL_tipo_equivalencia).'</label>';
		$rows.='<select id="tipo_equivalencia" name="tipo_equivalencia">';
		//$rows.='<optgroup label="'.ucfirst(FORM_LABEL_tipo_equivalencia).'">';
		$rows.=doSelectForm(array("$LabelEE","$LabelIE","$LabelNE"),"");
		$rows.='</optgroup>';
		$rows.='</select>';
		$rows.='</div>';
		$rows.='<div class="submit_form" align="center">';
		$rows.='<input type="button"  class="submit ui-corner-all"  name="cancelar" type="button" onClick="location.href=\'index.php?tema='.$ARRAYTermino[idTema].'\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
		$rows.='<input type="submit"   class="submit ui-corner-all" name="boton" value="'.LABEL_Enviar.'"/>';

		$rows.='<input type="hidden"  name="id_termino_eq" value="'.$ARRAYTermino[idTema].'" />';
		$rows.='<input type="hidden"  name="ks" id="ks" value="'.$_SESSION["SEND_KEY"].'"/>';
		$rows.='</div>';
		$rows.='  </fieldset>';
		$rows.='</form>';
		
$rows.='<script id="tvocab_script" type="text/javascript">
		$(document).ready(function() {
		var validator = $("#alta_eqt").validate({
			rules: {\''.FORM_LABEL_termino.'\':  {required: true},
					},
					debug: true,
					errorElement: "label",
					 submitHandler: function(form) {
					   form.submit();
					 }
					
			});	
		});
		</script>	';			
	}
	$rows.='</div>';

	return $rows;
}



//View and edit config options
function HTMLformConfigValues($array_vocabulario){

	$si=LABEL_SI;
	$no=LABEL_NO;

	GLOBAL $arrayCFGs;
	
	$sql=SQLconfigValues();

	$NEWarrayCFGs=array();
	while ($array=$sql->FetchRow()){
		 $NEWarrayCFGs[$array["value"]]= $array["value_code"];
	}


	
	$rows.='<div class="forminputdiv">';
	$rows.='<label for="'.FORM_LABEL_jeraquico.'">'.ucfirst(FORM_LABEL_jeraquico).'<span class="small">'.ucfirst(LABEL_jeraquico).'</span></label>';
	$rows.='    <select id="'.FORM_LABEL_jeraquico.'" name="'.FORM_LABEL_jeraquico.'">';
	$rows.=	doSelectForm(array("1#$si","2#$no"),$array_vocabulario[polijerarquia]);
	$rows.='</select>';
	$rows.='</div>';


	foreach ($arrayCFGs as $key => $value) {	
		switch ($key){
			case 'CFG_MAX_TREE_DEEP':
				$rows.='<div class="forminputdiv">';
				$rows.='<label for="'.$key.'">'.$key.'<span class="small">'.ucfirst(LABEL_CFG_MAX_TREE_DEEP).'</span></label>';
				$rows.='    <select id="'.$key.'" name="'.$key.'">';
				$rows.=	doSelectForm(array("1#1","2#2","3#3","4#4","5#5","6#6"),$NEWarrayCFGs[$key]);
				$rows.='</select>';
				$rows.='</div>';
				break;

			case 'CFG_MIN_SEARCH_SIZE':
				$rows.='<div class="forminputdiv">';
				$rows.='<label for="'.$key.'" >'.$key.'<span class="small">'.ucfirst(LABEL_CFG_MIN_SEARCH_SIZE).'</span></label>';
				$rows.='    <select id="'.$key.'" name="'.$key.'">';
				$rows.=	doSelectForm(array("1#1","2#2","3#3","4#4","5#5","6#6"),$NEWarrayCFGs[$key]);
				$rows.='</select>';
				$rows.='</div>';
				break;

			case 'CFG_NUM_SHOW_TERMSxSTATUS':
				$rows.='<div class="forminputdiv">';
				$rows.='<label for="'.$key.'">'.$key.'<span class="small">'.ucfirst(LABEL_CFG_NUM_SHOW_TERMSxSTATUS).'</span></label>';
				$rows.='    <select id="'.$key.'" name="'.$key.'">';
				$rows.=	doSelectForm(array("50#50","100#100","150#150","200#200","250#250"),$NEWarrayCFGs[$key]);
				$rows.='</select>';
				$rows.='</div>';
				break;

			case 'CFG_SEARCH_METATERM':
				$rows.='<div class="forminputdiv">';
				$rows.='<label for="'.$key.'">'.$key.'<span class="small">'.ucfirst(NOTE_isMetaTermNote).'</span></label>';
				$rows.='    <select id="'.$key.'" name="'.$key.'">';
				$rows.=	doSelectForm(array("1#$si","00#$no"),$NEWarrayCFGs[$key]);
				$rows.='</select>';
				$rows.='</div>';
				break;

			default:
				$rows.='<div class="forminputdiv">';
				$rows.='<label for="'.$key.'">'.$key.'<span class="small">'.ucfirst(arrayReplace(array('_USE_CODE','_SHOW_CODE','CFG_VIEW_STATUS','CFG_SIMPLE_WEB_SERVICE','_SHOW_TREE','_PUBLISH_SKOS','CFG_ENABLE_SPARQL'),array(LABEL__USE_CODE,LABEL__SHOW_CODE,LABEL_CFG_VIEW_STATUS,LABEL_CFG_SIMPLE_WEB_SERVICE,LABEL__SHOW_TREE,LABEL__PUBLISH_SKOS,LABEL__ENABLE_SPARQL),$key)).'</span></label>';
				$rows.='    <select id="'.$key.'" name="'.$key.'">';
				$rows.=	doSelectForm(array("1#$si","00#$no"),$NEWarrayCFGs[$key]);
				$rows.='</select>';
				$rows.='</div>';
		}
	}
	return $rows;
}


function HTMLformImport()
{
	$rows.='<h1>'.ucfirst(IMPORT_form_legend).'</h1>';
	
	$rows.='<form enctype="multipart/form-data" method="post" class="myform" action="admin.php?doAdmin=import">';
	$rows.='<fieldset>';
	$rows.='<legend>'.ucfirst(IMPORT_form_legend).'</legend>';

	$LABEL_importTab=ucfirst(LABEL_importTab);
	$LABEL_importTag=ucfirst(LABEL_importTag);
	$LABEL_importSkos=ucfirst(LABEL_importSkos);
	
	$rows.='<div>';
	$rows.='<label>'.ucfirst(FORM_LABEL_format_import).'</label>';
	$rows.='    <select id="taskAdmin" name="taskAdmin">';
	$rows.=	doSelectForm(array("importTab#$LABEL_importTab","importTag#$LABEL_importTag","importSkos#$LABEL_importSkos"),$_POST[doAdmin]);
	$rows.='</select>';

	$rows.='</div>';
	
	$rows.='<div>';
	$rows.='<label>'.ucfirst(IMPORT_form_label).'</label>';
	$rows.='<input type="file" value="Parcourir" name="file" />';
	$rows.='</div>';
	
	$rows.='<div class="submit_form" align="center">';	
	$rows.='<input type="hidden" value="doAdmin" name="import" />';
	$rows.='<input type="button"   class="submit ui-corner-all" name="cancelar" type="button" onClick="location.href=\'index.php\'" value="'.ucfirst(LABEL_Cancelar).'"/>';	

	$rows.='<input type="submit"  class="submit ui-corner-all" value="Ok" name="sendfile" />';
	$rows.='</div>';
	$rows.='</fieldset>';
	$rows.='</form>';
	
		$rows.='<p>'.ucfirst(LABEL_importTab).':<pre>
		South America
			Argentina
				Buenos Aires
			Brazil
			Uruguay</pre></p>';

		$rows.='<p>'.ucfirst(LABEL_importTag).':<pre>
		South America
		BT: America		
		NT: Argentina
		UF: South-america
		RT: Latin America</pre></p>';
		
		$rows.='<p>'.ucfirst(LABEL_importSkos).': <a href="http://www.w3.org/TR/skos-reference/" title="SKOS Simple Knowledge Organization System">http://www.w3.org/TR/skos-reference/</a></p>';
		
	
	return $rows;
}


function HTMLformURI4term($ARRAYtermino)
{
	//SEND_KEY to prevent duplicated
	session_start();
	$_SESSION['SEND_KEY']=md5(uniqid(rand(), true));

	$rows.='<div id="bodyText">';
	
	$rows.='<script type="text/javascript">$("#form-tvocab").validate({});</script>';

	$rows.='<a class="topOfPage" href="index.php?tema='.$ARRAYtermino[idTema].'" title="'.LABEL_Anterior.'">'.LABEL_Anterior.'</a>
	<h1>'.$ARRAYtermino[titTema].'</h1>
	<form class="myform" name="altaURI" id="altaURI" action="index.php" method="post">';
	$rows.='  <fieldset>
		<legend>'.LABEL_URIEditor.'</legend>';

	
	
	$SQLURIdefinition=SQLURIdefinition();
		
	if(SQLcount($SQLURIdefinition)>0)
	{
		while($ARRAYURIdefinition=$SQLURIdefinition->FetchRow())
		{
			$arraySelectURItype[]=$ARRAYURIdefinition[uri_type_id].'#'.$ARRAYURIdefinition[uri_value];
		}
			
		
		$rows.='<div class="forminputdiv"><label for="uri_type_id" accesskey="u">'.ucfirst(LABEL_URItype).'</label>';
		$rows.='<select id="uri_type_id" name="uri_type_id">';
		$rows.=doSelectForm($arraySelectURItype,"");
		$rows.='</select>';
		$rows.='</div>';

		$rows.='<div><label for="'.LABEL_URI2termURL.'" accesskey="t">'.LABEL_URI2termURL.'</label>
		<textarea rows="1" cols="40" name="uri" id="uri"></textarea>
		</div>';


	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="hidden"  name="ks" id="ks" value="'.$_SESSION["SEND_KEY"].'"/>';
	$rows.='<input type="hidden"  name="tema_id" value="'.$ARRAYtermino[idTema].'" />';
	$rows.='<input type="hidden"  name="taskURI" value="addURI" />';
	$rows.='<input type="button"  class="submit ui-corner-all"  name="cancelar" type="button" onClick="location.href=\'index.php?tema='.$ARRAYtermino[idTema].'\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
	$rows.='<input type="submit"  class="submit ui-corner-all"  name="boton" value="'.LABEL_Enviar.'"/>';

	$rows.='</div>';
	}
	$rows.='  </fieldset>';
	$rows.='</form>';


	$rows.=$help_rows;
	
	$rows.='<script id="tvocab_script" type="text/javascript">
		$(document).ready(function() {
		var validator = $("#altaURI").validate({
			rules: {\'uri\':  {required: true,url:true},
					},
					debug: true,
					errorElement: "label",
					 submitHandler: function(form) {
					   form.submit();
					 }
					
			});	
		});
		</script>	';	
	$rows.='</div>';
	return $rows;
}



function HTMLformMapXCode($arrayTargetVocab) 
{
	$sql=SQLTerminosPreferidos();
	
	while($array=$sql->FetchRow()){
	{
		if(is_string($array[code]))
		{
			$arrayTerm=xmlVocabulary2array($arrayTargetVocab[tvocab_uri_service].'?task=fetchCode&arg='.urlencode($array[code]));			
			
			$rows.='<a title="" href="'.$PHP_SELF.'?tema='.$tema_id.'&amp;tvocab_id='.$arrayVocab[tvocab_id].'&amp;tgetTerm_id='.$v[term_id].'&amp;taskrelations=addTgetTerm&amp;ks='.$_SESSION["SEND_KEY"].'">'.FixEncoding($v[string]).'</a>';

			$arrayTterm["tterm_uri"]=$arrayTargetVocab[tvocab_uri_service].'?task=fetchTerm&arg='.$arrayTerm[result][term][term_id];	
			
			$arrayTterm["tterm_url"]=$arrayTargetVocab[tvocab_url].'?tema='.$arrayTerm[result][term][term_id];
	
			$arrayTterm[tterm_string]=$DB->qstr(trim($arrayTerm[result][term]["string"]),get_magic_quotes_gpc());
		
				
			$sql=SQL("insert","into $DBCFG[DBprefix]term2tterm (tema_id,tvocab_id,tterm_url,tterm_uri,tterm_string,cuando,uid) 
					values ($array[tema_id],$arrayTargetVocab[tvocab_id],$arrayTterm[tterm_url],$arrayTterm[tterm_uri],$arrayTterm[tterm_string],now(),$userId)");
			
			//~ echo $array[tema].'=>'.$arrayTerm[tterm_string];
		}
		
	}
	
}
}


function HTMLconfirmDeleteTerm($ARRAYtermino) 
{	
	
	
	$rows.='<form class="myform" name="deleteTerm" action="index.php" method="post">';
	$rows.='  <fieldset id="borrart" style="display:none;">';
	$rows.='	<legend>'.ucfirst(MENU_BorrarT).'</legend>';
	$rows.= '<p class="warning">'.sprintf(MSG__warningDeleteTerm,$ARRAYtermino[titTema]).'</p>';
	$rows.= '<p class="information">'.MSG__warningDeleteTerm2row.'</p>';
			
	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="hidden"  name="tema_id" value="'.$ARRAYtermino[tema_id].'" />';
	$rows.='<input type="hidden"  name="task" value="remterm" />';
	$rows.='<input type="button"  class="submit ui-corner-all"  name="cancelar" onClick="javascript:expandLink(\'borrart\')" value="'.ucfirst(LABEL_Cancelar).'"/>';
	$rows.='<input type="submit"   class="submit ui-corner-all" name="boton" value="'.ucfirst(MENU_BorrarT).'"/>';

	$rows.='</div>';
	$rows.='  </fieldset>';
	$rows.='</form>';
	
	

return $rows;	
}


function HTMLformMasiveDelete() 
{	
	
	
	$rows.='<form class="myform" name="massive_delete" action="admin.php" method="post">';
	$rows.='  <fieldset id="massive_delete_fieldset" ><legend>'.ucfirst(MENU_massiverem).'</legend>';
	
	$rows.= '<p class="warning"><strong>'.LABEL_warningMassiverem.'</strong>.</p>';
			
			
	$rows.='<div>';
	$rows.='<label for="massrem_teqterms">'.ucfirst(LABEL_target_terms).'</label>';
	$rows.='<input type="checkbox" name="massrem_teqterms" id="massrem_teqterms" value="1" alt="'.ucfirst(LABEL_target_terms).'" /> ';
	$rows.='</div>';

	$rows.='<div>';
	$rows.='<label for="massrem_url">'.ucfirst(LABEL_URI2terms).'</label>';
	$rows.='<input type="checkbox" name="massrem_url" id="massrem_url" value="1" alt="'.ucfirst(LABEL_URI2terms).'" /> ';
	$rows.='</div>';

	$rows.='<div>';
	$rows.='<label for="massrem_notes">'.ucfirst(LABEL_notes).'</label>';
	$rows.='<input type="checkbox" name="massrem_notes" id="massrem_notes" value="1" alt="'.ucfirst(LABEL_notes).'" /> ';
	$rows.='</div>';

	$rows.='<div>';
	$rows.='<label for="massrem_terms">'.ucfirst(LABEL_Terminos).'</label>';
	$rows.='<input class="checkall"  type="checkbox" name="massrem_terms" id="massrem_terms" value="1" alt="'.ucfirst(LABEL_Terminos).'" /> ';
	$rows.='</div>';
	

	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="button" class="submit ui-corner-all" name="cancelar" type="button" onClick="location.href=\'index.php\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
	$rows.='<input class="submit ui-corner-all" type="submit"  name="boton" value="'.ucfirst(LABEL_Enviar).'"/>';
	$rows.='<input type="hidden" name="doAdmin" value="massrem" />';
	$rows.='</div>';
	$rows.='</form>';
	$rows.='  </fieldset>';
	$rows.='<script type="text/javascript">
		$(function () {
		$(\'.checkall\').click(function () {
			$(this).parents(\'fieldset:eq(0)\').find(\':checkbox\').attr(\'checked\', this.checked);
		});
	});    
	</script>';
	
	

return $rows;	
}


function HTMLformUpdateEndpoit() 
{	

	$ARRAYlastUpdateEndpoint=fetchlastUpdateEndpoint();
	
	$msg_update=($ARRAYlastUpdateEndpoint["value"]) ? '<br/>'.MSG__dateUpdatedEndpoint.': '.$ARRAYlastUpdateEndpoint["value"].'.' : '';

	$rows.='<form class="myform" name="updateEndpoint" action="admin.php" method="post">';
	$rows.='  <fieldset id="massive_updateEndpoint"><legend>'.ucfirst(LABEL_updateEndpoint).'</legend>';
	
	$rows.= '<p class="warning"><strong>'.MSG__updateEndpoint.'</strong> '.$msg_update.'</p>';

	
			

	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="button" class="submit ui-corner-all" name="cancelar" type="button" onClick="location.href=\'index.php\'" value="'.ucfirst(LABEL_Cancelar).'"/>';
	$rows.='<input class="submit ui-corner-all" type="submit"  name="boton" value="'.ucfirst(LABEL_Enviar).'"/>';
	$rows.='<input type="hidden" name="doAdmin" value="updateEndpointNow" />';
	$rows.='</div>';
	$rows.='</form>';
	$rows.='  </fieldset>';
	

return $rows;	
}


function HTMLformLogin($task_result) 
{

	$rows.='<form class="myform" id="mylogin" name="mylogin" action="login.php" method="post">
		 <fieldset>
		<legend>'.ucfirst(LABEL_login).'</legend>';


	if(is_array($task_result))
	{		
		$rows.='<div>'.$task_result["msg"].'</div>';
	}
	

	$rows.='<div><label for="mail" accesskey="t">'.ucfirst(LABEL_mail).'</label>';
	$rows.='<input type="text" name="id_correo_electronico" class="campo" id="mail" maxlength="64" size="11"/>';
	$rows.='	</div>';

	$rows.='<div><label for="id_password" accesskey="t">'.ucfirst(LABEL_pass).'</label>';
	$rows.='<input type="password" name="id_password" class="campo" id="id_password" maxlength="64" size="11"/>';
	$rows.='	</div>';

 	$rows.='<div class="submit_form" style="margin-left:15em" align="center">';
 	$rows.='<input type="hidden"  name="task" value="login" />'; 	
	$rows.='<input type="submit"  class="submit ui-corner-all" name="boton" value="'.LABEL_Enviar.'"/>';
	$rows.='</div>';
	
	$rows.='<div style="margin-left:35em"><a href="login.php?task=recovery">'.LABEL_user_lost_password.'</a></div>';
	$rows.='  </fieldset>';
	$rows.='</form>';

	$rows.='</div>';
	
	return $rows;	
}



function HTMLformRecoveryPassword($user_name="") 
{
	$rows.='<form class="myform" id="myRecovery" name="myRecovery" action="login.php" method="post">
		 <fieldset>
		<legend>'.LABEL_user_recovery_password.'</legend>';

	$rows.='<div><label for="'.LABEL_mail .'" accesskey="t">'.ucfirst(LABEL_mail).'</label>';
	$rows.='<input type="text" name="id_correo_electronico_recovery" class="campo" id="id_correo_electronico_recovery" maxlength="64" size="11"/>';
	$rows.='	</div>';
	
 	$rows.='<div class="submit_form" align="center">';
	$rows.='<input type="hidden"  name="task" value="user_recovery" />'; 	
	$rows.='<input type="button" class="submit ui-corner-all" name="cancelar" type="button" onClick="location.href=\'login.php\'" value="'.ucfirst(LABEL_Cancelar).'"/>'; 		
	$rows.='<input type="submit"  class="submit ui-corner-all" name="boton" value="'.LABEL_Enviar.'"/>';
	$rows.='</div>';
	
	$rows.='  </fieldset>';
	$rows.='</form>';
	$rows.='</div>';
	
	return $rows;	
}
#######################################################################
?>
