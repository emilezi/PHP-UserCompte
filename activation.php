<?php
	include 'database.php'; 
	global $db;

	echo
	"<html>";
  
	echo "<head>
  <meta charset='utf-8' />
	<title>Activation</title>
  </head>";
  
  echo "<body>";
  
  if((!isset($_GET['log'])) && (!isset($_GET['cle']))){
  header('Location: index.php');
  }else{
  
  $login = $_GET['log'];
  $cle = $_GET['cle'];
  
  $g = $db->prepare("SELECT * FROM users WHERE pseudo=:pseudo");
  $g->execute(['pseudo' => $login]);
  $resultk = $g->fetch();
  
  if($resultk == true)
  {
  if($resultk['cle'] == $cle)
  {
  
  $d = $db->prepare("SELECT * FROM users WHERE pseudo=:pseudo");
  $d->execute(['pseudo' => $login]);
  $result = $d->fetch();
  
  if($result['actif'] == "yes")
  {
  echo "<p>Votre compte est déja actif</p>";
  }else{
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