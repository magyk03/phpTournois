<?php
/*
   +---------------------------------------------------------------------+
   | phpTournois                                                         |
   +---------------------------------------------------------------------+
   +---------------------------------------------------------------------+
   | phpTournoisG4 �2005 by Gectou4 <Gectou4 Gectou4@hotmail.com>        |
   +---------------------------------------------------------------------+
         This version is based on phpTournois 3.5 realased by :
   | Copyright(c) 2001-2004 Li0n, RV, Gougou (http://www.phptournois.net)|
   +---------------------------------------------------------------------+
   | This file is part of phpTournois.                                   |
   |                                                                     |
   | phpTournois is free software; you can redistribute it and/or modify |
   | it under the terms of the GNU General Public License as published by|
   | the Free Software Foundation; either version 2 of the License, or   |
   | (at your option) any later version.                                 |
   |                                                                     |
   | phpTournois is distributed in the hope that it will be useful,      |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of      |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       |
   | GNU General Public License for more details.                        |
   |                                                                     |
   | You should have received a copy of the GNU General Public License   |
   | along with AdminBot; if not, write to the Free Software Foundation, |
   | Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA       |
   |                                                                     |
   +---------------------------------------------------------------------+
   | Authors: Li0n  <li0n@phptournois.net>                               |
   |          RV <rv@phptournois.net>                                    |
   |          Gougou                                                     |
   +---------------------------------------------------------------------+
*/

if (strpos(strtolower($_SERVER['PHP_SELF']), 'kernel.php')  !== false) {
	die ('You cannot open this page directly');
}

/*** inclusion des globals ***/
include ('globals.php');

/*** chargement de la classe base de donn&eacute;e ***/
include('db/mysql.inc.php');

/*** chargement du fichier de fonctions ***/
include('include/functions.inc.php');

function verif($var){
	$r=false;
	if (isset($var)){
		if(!empty($var)) {
			$r=true;
		}
	}
	return $r;
}

/*** chargement de la configuration statique***/
$config=array();
$mods=array();
$garde=array();
//$garde=array("a" => "",    "b" => "",    "c" => "","d" => "",    "e" => "","f" => "",	"g" => "",    "h" => "",    "i" => "",	"j" => "",    "k" => "",    "l" => "",	"m" => "",    "n" => "",    "o" => "","p" => "",	"q" => "",	"r" => "",	"s" => "",	"t" => "",	"u" => "",	"v" => "",	"w" => "",	"x" => "",	"y" => "",	"z" => "",);
//$is_grade=grade_s($s_joueur);

include('config.php');

if(!defined("PHPTOURNOIS_INSTALLED")) {
 	header("Location: install.php");
	die();
}

/*** ouverture de la base de donnees ***/
$db = new database;
$db->debug($dbdebug);
$db->connect($dbhost,$dbuser,$dbpass,$dbname);

/*** chargement de la configuration dynamique***/
$db->select("*");
$db->from("${dbprefix}config");
$db->exec();
$config = array_merge ($config,$db->fetch_array());
if(isset($m4url)) $config['m4url'] = $m4url;
else $config['m4url'] = '';
if(isset($aburl)) $config['aburl'] = $aburl;
else $config['aburl'] = '';

/*** chargement de la configuration des mods***/
$db->select("*");
$db->from("${dbprefix}mods");
$db->exec();
$mods = array_merge ($mods,$db->fetch_array());


/*** compression pour les php > 4.0.4pl1 ***/
$phpver = phpversion();
$useragent = (isset($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;

if($config['gzip'] == 1 && $phpver >= '4.0.4pl1' && (strstr($useragent,'compatible') || strstr($useragent,'Gecko')) && extension_loaded('zlib')) {
	ob_start('ob_gzhandler');		
}
else {
	ob_start();
	$config['gzip'] == 0;
}
/*** demarrage de la session ***/
//include('sessions.inc.php');

include('include/Member.class.php');

if ($op == 'logout')$logout=true;
else $logout = false;

$Sess = New Member($_POST['pseudo'],$_POST['passwd'],$_POST['remember'], $logout);

$s_joueur = $Sess->m_id;
$getLang  = $Sess->getLang();
$s_lang   = $getLang->lang;
	
if ($s_joueur == 0) {
	$s_theme=$config['default_theme'];

	if( empty($s_lang) || !isset($s_lang) ) {
		$s_lang=$config['default_lang'];
		$Sess->lang($config['default_lang'],$s_joueur);
	}
	$s_type=0;
}else{
	//if($Sess->origine == 'FR') $s_lang = 'francais';
	//else if($Sess->origine == 'UK') $s_lang = 'english';
	//else if($Sess->origine == 'EN') $s_lang = 'english';
	if( !empty($Sess->langue) && isset($Sess->langue) ) {
		$s_lang = $Sess->langue;
		$Sess->lang($Sess->langue,$s_joueur);
	}
	else $s_lang = $config['default_lang'];
	 
	if(!empty($Sess->theme)) $s_theme = $Sess->theme;
	else $s_theme = $config['default_theme'];
	$s_type = '1';
}

$getTournois = $Sess->getTournois();
$s_tournois=$getTournois->tournois;
//petit hack
if (isset($_GET['id_tournois']) && is_numeric($_GET['id_tournois'])) 
{
	$s_tournois = $_GET['id_tournois'];
	$Sess->tournois($_GET['id_tournois'],$Sess->m_id);
}
/*** chargement de la langue ***/
if(isset($_GET['lang'])) {
	$s_lang=$_GET['lang'];
	$Sess->lang($_GET['lang'],$s_joueur);
}

if(isset($s_lang)) {
	include("lang/$s_lang.inc.php");
}
else {
	include('lang/'.$config['default_lang'].'.inc.php');
	$s_lang=$config['default_lang'];
}

/*** chargement du theme menu ***/
if(isset($s_theme)) $s_theme=$Sess->theme;

/*** chargement du theme ***/
if(isset($s_theme) && $s_theme!=NULL && $s_theme!='') {
	include("themes/$s_theme/theme.php");
}
else {
	include('themes/'.$config['default_theme'].'/theme.php');
	//SessionSetVar('s_theme',$config['default_theme']);
	$s_theme=$config['default_theme'];
}


if(isset($s_tournois) && !empty($s_tournois)) {
	
	$tournoi = tournois($s_tournois);

	$nom_tournois=$tournoi->nom;
	$status_tournois=$tournoi->status;
	$type_tournois=$tournoi->type;
	$modeelimination_tournois=$tournoi->elimination;
	$modescore_tournois=$tournoi->modescore;
	$modematchscore_tournois=$tournoi->modematchscore;
	$modeinscription_tournois=$tournoi->modeinscription;
 	$modefichier_tournois=$tournoi->modefichier;
	$modeequipe_tournois=$tournoi->modeequipe;
	$nb_finales_winner_tournois=$tournoi->winner;
	$nb_finales_looser_tournois=$tournoi->looser;
	$nb_poules_tournois=$tournoi->poules;
	$nb_manches_max_tournois=$tournoi->manchesmax;


	if($modeequipe_tournois=='E') {
		$champX="tag";
		$equipeX="equipe";
		$equipesX="equipes";
		$EquipeX="Equipe";
		$EquipesX="Equipes";
		$show="show_equipe";
		$nom_participant="nom_equipe";

	}
	else {
		$champX="pseudo";
		$equipeX="joueur";
		$equipesX="joueurs";
		$EquipeX="Joueur";
		$EquipesX="Joueurs";
		$show="show_joueur";
		$nom_participant="nom_joueur";
	}
}

/*** chargement de la variable des 'gardes' de l'utilisateur  ***/
	if ($s_joueur){
	
	$db->select("id,grade");
	$db->from("${dbprefix}joueurs");
	$db->where("id = $s_joueur");
	$res=$db->exec();

	
while ($grade_ch = $db->fetch($res)) {
	 //  A est 'le' chef
	if (eregi('a', $grade_ch->grade)) {
   $grade['a'] = 'a'; 
    }
	// B peut tout faire sauf modifier 'le chef'
	if (eregi('b', $grade_ch->grade)) {
   $grade['b'] = 'b'; 
    }
	// C peut modifier la configuration (mods comprit)
	if (eregi('c', $grade_ch->grade)) {
   $grade['c'] = 'c'; 
    }
	// D g�re les downloads
	if (eregi('d', $grade_ch->grade)) {
   $grade['d'] = 'd'; 
    }
	//E peut g�rer les &eacute;quipe
	if (eregi('e', $grade_ch->grade)) {
   $grade['e'] = 'e'; 
    }
	//F peut g&eacute;rer la FAQ
	if (eregi('f', $grade_ch->grade)) {
   $grade['f'] = 'f'; 
    }//G peut cr&eacute;er des pages ou des menus (paske G4 il cr&eacute;er lol)
	if (eregi('g', $grade_ch->grade)) {
   $grade['g'] = 'g'; 
    }
	//H peut g�rer les liens ( <a Href=''> ) 
	if (eregi('h', $grade_ch->grade)) {
   $grade['h'] = 'h'; 
    }
	//I peut g�rer le livre d'or (???)
	if (eregi('i', $grade_ch->grade)) {
   $grade['i'] = 'i'; 
    }
	//J peut g�rer les joueurs
	if (eregi('j', $grade_ch->grade)) {
   $grade['j'] = 'j'; 
    }
	// K pour M4 ou AB
	if (eregi('k', $grade_ch->grade)) {
   $grade['k'] = 'k'; 
    }
	//L peut utiliser la maling List
	if (eregi('l', $grade_ch->grade)) {
   $grade['l'] = 'l'; 
    }
	// M Mod&eacute;rateur
	if (eregi('m', $grade_ch->grade)) {
   $grade['m'] = 'm'; 
    }
	// N newser
	if (eregi('n', $grade_ch->grade)) {
   $grade['n'] = 'n'; 
    }
	// O ind&eacute;finie libre pour des mods ou custom rank
	if (eregi('o', $grade_ch->grade)) {
   $grade['o'] = 'o'; 
    }
	// P g�re les partenaire
	if (eregi('p', $grade_ch->grade)) {
   $grade['p'] = 'p'; 
    }
	// Q g�re la gallerie
	if (eregi('q', $grade_ch->grade)) {
   $grade['q'] = 'q'; 
    }
	// S g�re les server
	if (eregi('r', $grade_ch->grade)) {
   $grade['r'] = 'r'; 
    }
	// S g�re les sponsors
	if (eregi('s', $grade_ch->grade)) {
   $grade['s'] = 's'; 
    }
	// T admin de tous les tournois (est orga & admin)
	if (eregi('t', $grade_ch->grade)) {
   $grade['t'] = 't'; 
    }
	// U admin des ladder (cr&eacute;er, g�re..).
	if (eregi('u', $grade_ch->grade)) {
   $grade['u'] = 'u'; 
    }
	// V ind&eacute;finie libre pour des mods ou custom rank
	if (eregi('v', $grade_ch->grade)) {
   $grade['v'] = 'v'; 
    }
	// W Peut &eacute;diter les pouvoirs
	if (eregi('w', $grade_ch->grade)) {
   $grade['w'] = 'w'; 
    }
	// X est manager d'une Team 
	if (eregi('x', $grade_ch->grade)) {
   $grade['x'] = 'x'; 
    }
	// Y est wararranger ou leader d'une Team 
	if (eregi('y', $grade_ch->grade)) {
   $grade['y'] = 'y'; 
    }
	//le Z est le rang 'user' un membre qui n'a pas "z" est bannit...
	if (eregi('z', $grade_ch->grade)) {
   $grade['z'] = 'z'; 
    }
}

/*** Si le joueur est bannit on l'exclut ***/
if ($grade['z'] != 'z')  {
//setcookie("data","banned",time()-9999999);
if ($page!="banned") {
js_goto("?page=banned");
}
}
}

/*** Correction du s_type selon rang admin ***/
if(($grade['a']=='a' || $grade['b']=='b')&& $s_type!='2') {
//SessionSetVar("s_type","2");
}

/*** chargement du BBcode de lalex ***/
// Les tableaux contenant les donn&eacute;es des BBCodes
// bbTags : contient le nom de chaque tag
$bbTags = Array();
// htmlTags : contient les donn&eacute;es de traduction en code HTML
$htmlTags = Array();
// Contient les tags ouverts dans le fichier XML
$xmlstack = Array();
// Contient le tag BBCode en cours du fichier XML
$xmlcurtag = "";
	

/*** chargement des parametres du mail ***/
require('include/class.phpmailer.php');

class phpTMailer extends PHPMailer {

 	var $WordWrap = 75;

	function phpTMailer () {
		global $config;
		$this->IsHTML(true);

		if($config['mail']=='S' && $config['smtpserver']) {
			 $this->IsSMTP();
			 $this->Host = $config['smtpserver'];			 
		}

		if($config['smtpuser'] && $config['smtppassword']) {
			$this->SMTPAuth = true;
			$this->Username = $config['smtpuser'];
			$this->Password = $config['smtppassword'];
		}
	}
}

//forum :

//end forum

$config['version'] = "v3.5 G4.2 php5";
?>