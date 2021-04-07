<?php
	session_start();
	include 'database.php'; 
  global $db;
	echo "<html>";

	echo
  "<head>
  <meta charset='utf-8' />
  <title>Compte</title>
  </head>";
  
  	echo "<body>";
  
  	if((isset($_SESSION['pseudo'])) && (isset($_SESSION['prenom'])) && (isset($_SESSION['nom'])))
  	{
  
  	echo "<h1>Votre compte utilisateur :</h1>";

    echo "<p>Votre nom : ".$_SESSION['nom']."</p>";
    echo "<p>Votre prenom : ".$_SESSION['prenom']."</p>";
    echo "<p>Votre pseudo : ".$_SESSION['pseudo']."</p>";

    echo "<br/>";

    echo "<form method='post'>
  	<input type='submit' name='formdeconnexion' id='formdeconnexion' value='Déconnexion'>
  	</form>";
  
  	if(isset($_POST['formdeconnexion'])){
  	session_destroy();
  	header('Location: index.php');
  	}
   	
  	}else{
   

   if(isset($_POST['formsignin'])){
    
   extract($_POST);
    
   if(!empty($npassword) && !empty($ncpassword) && !empty($nemail) && !empty($npseudo) && !empty($nnom) && !empty($nprenom)){
    	
    if (preg_match("#^[a-z0-9.]+@[a-z0-9.]+$#i", $nemail) && preg_match("#^[a-z0-9]+$#i", $npseudo) && preg_match("#^[^<>]+$#i", $nnom) && preg_match("#^[^<>]+$#i", $nprenom))
    {
    
    if($npassword == $ncpassword){
    
    $options = [
    'cost' => 12,
    ];
    
    $hashpass = password_hash($npassword, PASSWORD_BCRYPT, $options);
    
    $c = $db->prepare("SELECT email FROM users WHERE email=:email");
    $c->execute(['email' => $nemail]);
    $result1 = $c->rowCount();
    
    $x = $db->prepare("SELECT pseudo FROM users WHERE pseudo=:pseudo");
    $x->execute(['pseudo' => $npseudo]);
    $result2 = $x->rowCount();
    
    if(($result1 == 0) && ($result2 == 0)){
    
    $cle = md5(microtime(TRUE)*100000);
    $date = date('Y-m-d h:i:s');
    	
    $q = $db->prepare("INSERT INTO users(prenom,nom,pseudo,email,password,actif,cle,recoverycle,recoverydate) VALUES(:prenom,:nom,:pseudo,:email,:password,:actif,:cle,:recoverycle,:recoverydate)");
    $q->execute([
    'prenom' => $nprenom,
    'nom' => $nnom,
    'pseudo'=> $npseudo,
    'email' => $nemail,
    'password' => $hashpass,
    'actif' => "no",
    'cle' => $cle,
    'recoverycle' => $cle,
    'recoverydate' => $date
    ]);
    
    $email = $nemail;
    $login = $npseudo;
    
    $destinataire = $email;
    $sujet = $titre_mail_activation ;
    $entete = "From: services@localhost" ;
    
    $message = "Pour activer votre compte, veuillez cliquer sur le lien ci-dessous
    ou copier/coller dans votre navigateur Internet
    
    activation.php?log=".urlencode($login)."&cle=".urlencode($cle)."
    
    ---------------
    Ceci est un mail automatique, Merci de ne pas y répondre";
    
    mail($destinataire, $sujet, $message, $entete);
    
    }else{
    echo "<p>Un compte portent ce mail ou pseudo existe deja</p>";
    }
    }else{
    echo "<p>Les deux mots de passe ne correspondent pas</p>";
    }
    }
    else
    {
    echo "<p>Certains champs ne respectent pas la forme demandée</p>";
    }
 	}
    }else{
  
  	echo "<h1>Connectez vous</h1>";
  	echo "<form method='post'>
    <input type='text' name='lemail' id='lemail' placeholder='Votre addresse email' required><br/>
    <br/>
  	<input type='password' name='lpassword' id='lpassword' placeholder='Votre mots de passe' required><br/>
  	<br/>
  	<input type='submit' name='formlogin' id='formlogin' value='Valider'>
  	</form>";
  
  	if(isset($_POST['formlogin'])){
    
    extract($_POST);
    
    if(!empty($lpassword) && !empty($lemail))
    {
    
   	if (preg_match("#^[a-z0-9.]+@[a-z0-9.]+$#i", $lemail)){
		$q = $db->prepare("SELECT * FROM users WHERE email = :email");
   	$q->execute(['email' => $lemail]);
   	$result = $q->fetch();
   	}else{
   	$q = $db->prepare("SELECT * FROM users WHERE pseudo = :pseudo");
   	$q->execute(['pseudo' => $lemail]);
   	$result = $q->fetch();
   	}
    
    if($result == true)
    {
    if($result['actif'] == "yes")
    {
     if(password_verify($lpassword, $result['password']))
     { 
     header('Location: index.php');
     
     $_SESSION['prenom'] = $result['prenom'];
     $_SESSION['nom'] = $result['nom'];
     $_SESSION['pseudo'] = $result['pseudo'];
     $_SESSION['email'] = $result['email'];

     echo "<p>Connexion en cours...</p>";
    }else{
      echo "Le mots de passe n'est pas correcte";
    }
    }else{
    echo "<p>Le compte n'est pas actif</p>";
    }
    }else{
    echo "<p>Le compte portent le pseudo ou le mail".$lemail."n'existe pas</p>";
    }
    }else{
    echo "<p>Veuillez bien renseigner tous les champs</p>";
    }
    }
  
  	echo "<h1>Créez votre compte</h1>
    <form method='post'>
  	<input type='text' name='nprenom' id='nprenom' placeholder='Saisissez votre prenom' required><br/>
  	<br/>
  	<input type='text' name='nnom' id='nnom' placeholder='Saisissez votre nom' required><br/>
  	<br/>
  	<input type='text' name='npseudo' id='npseudo' placeholder='Saisissez votre pseudo' required><br/>
  	<br/>
  	<input type='email' name='nemail' id='nemail' placeholder='Saisissez votre addresse mail' required><br/>
  	<br/>
  	<input type='password' name='npassword' id='npassword' placeholder='Saisissez votre mots de passe' required><br/>
  	<br/>
  	<input type='password' name='ncpassword' id='ncpassword' placeholder='Retapez votre mots de passe' required><br/>
  	<br/>
  	<input type='submit' name='formsignin' id='formsignin' value='Créer le compte'>
  	</form>";
    
   }
    
   }
  

  	echo
  	"</body>
	</html>";

?>