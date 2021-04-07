<?php
	include 'database.php';       //Inclut et exécute le fichier databases.php contenant les fonctions de connexion à la base de données
	global $db;

	echo
	"<html>";
  
  //Génère les informations générales de la page
	echo "<head>
  <meta charset='utf-8' />
	<title>Activation</title>
  </head>";
  
  echo "<body>";
  

  //Recupère les éléments du lien concernent le pseudo et la clé du compte
  if((!isset($_GET['log'])) && (!isset($_GET['cle']))){           //Si le pseudo ou la clé du compte n'a pas bien été recupérer
  header('Location: index.php');
  }else{

  //Sinon, vérifier que le compte concerner existe bien
  
  $login = $_GET['log'];
  $cle = $_GET['cle'];
  
  $g = $db->prepare("SELECT * FROM users WHERE pseudo=:pseudo");
  $g->execute(['pseudo' => $login]);
  $resultk = $g->fetch();
  
  if($resultk == true)
  {

  //Si le compte existe bien, vérifier que la clé saisie correspond bien avec celle du compte
  
  if($resultk['cle'] == $cle)
  {

  //Si oui, vérifier que le compte est bien inactif
  
  $d = $db->prepare("SELECT * FROM users WHERE pseudo=:pseudo");
  $d->execute(['pseudo' => $login]);
  $result = $d->fetch();
  
  if($result['actif'] == "yes")
  {
  echo "<p>Votre compte est déja actif</p>";
  }else{

    //Si oui, préparer la requête de modification de l'element souhaiter dans la base de donnée

  	$f = $db->prepare("UPDATE users SET actif=:actif WHERE pseudo=:pseudo");
  	$f->execute(['pseudo' => $login,
  	'actif' => "yes"
    ]);
  	echo "<p>Votre compte à bien été activée</p>";
  	}
  }else{
  	echo "<p>La clé du compte n'est pas valide</p>";
  	}
  }else{
  header('Location: index.php');
  }
  }
  
  echo
  "</body>
  </html>";

?>