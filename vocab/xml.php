<?php
#   TemaTres : aplicación para la gestión de lenguajes documentales #       #
#                                                                        #
#   Copyright (C) 2004-2008 Diego Ferreyra tematres@r020.com.ar
#   Distribuido bajo Licencia GNU Public License, versión 2 (de junio de 1.991) Free Software Foundation
#  
###############################################################################################################
#
include("config.tematres.php");
if($_GET[zthesTema])
{
	echo do_zthes(do_nodo_zthes($_GET[zthesTema]));
}
elseif($_GET[skosTema])
{
	echo do_skos(do_nodo_skos($_GET[skosTema]));
}
elseif($_GET[skosMeta]==1)
{
	echo do_skos("",true);
}
elseif($_GET[skosNode])
{
	echo do_skosNode(do_nodo_skos($_GET[skosNode]));
}
elseif($_GET[vdexTema])
{
	echo do_VDEX($_GET[vdexTema]);
}
elseif($_GET[bs8723Tema])
{
	echo do_BS8723s(do_nodo_BS8723($_GET[bs8723Tema]));
}
elseif($_GET[madsTema])
{
	echo do_mads($_GET[madsTema]);
}
elseif($_GET[xtmTema])
{
	return do_topicMap($_GET[xtmTema]);
}
elseif($_GET[dcTema])
{
	return do_dublin_core($_GET[dcTema]);
}
elseif($_GET[jsonTema])
{
	echo do_json($_GET[jsonTema]);
}
elseif($_GET[jsonldTema])
{
	echo do_jsonld($_GET[jsonldTema]);
}
elseif($_GET[rss])
{
	return do_rss();
};
if(($_SESSION[$_SESSION["CFGURL"]][ssuser_nivel]=='1')&&($_GET[dis])){

	switch($_GET[dis]){
		case 'zline':
		return doTotalZthes("line");
		break;

		case 'zfile':
		return doTotalZthes("file");
		break;

		case 'rline':
		return doTotalSkos("line");
		break;

		case 'rfile':
		$params=array("hasTopTerm"=>$_GET["hasTopTermSKOS"]);		
		return doTotalSkos("file",$params);
		break;

		case 'vfile':
		return doTotalVDEX("file");
		break;

		case 'siteMap':
		return do_sitemap("file");
		break;

		case 'rxtm':
		return doTotalTopicMap("file");
		break;

		case 'lxtm':
		return doTotalTopicMap("line");
		break;

		case 'BSfile':
		return doTotalBS8723("file");
		break;

		case 'wxr':
        echo doTotalWXR("file");
        break;

		case 'txt':
		$params=array("hasTopTerm"=>$_GET["hasTopTerm"],
					  "includeNote"=>$_GET["includeNote"],
					  "includeCreatedDate"=>$_GET["includeCreatedDate"],
					  "includeTopTerm"=>$_GET["includeTopTerm"],
					  "includeModDate"=>$_GET["includeModDate"]);
		echo txtAlfabetico($params);
		break;


		case 'jtxt':
		echo txtJerarquico();
		break;

		case 'rsql':
		echo do_mysql_dump();
		break;


	}
};
?>
