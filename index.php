<?php
	session_start();                        //Démarre une nouvelle session ou reprend une session existante
	include 'database.php';                 //Inclut et exécute le fichier databases.php contenant les fonction de connexion à la base de données
  global $db;
	echo "<html>";

	echo                                    //Génère les informations générales de la page
  "<head>
  <meta charset='utf-8' />
  <title>Compte</title>
  </head>";
  
  	echo "<body>";
  
  	if((isset($_SESSION['pseudo'])) && (isset($_SESSION['prenom'])) && (isset($_SESSION['nom'])))       //Vérifie, si il y a une session actif
  	{

    //Si oui, afficher les éléments du compte utilisateur

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
      
    //Sinon afficher la page de connexion

    if(isset($_POST['formsignin'])){        //Si l'utilisateur clique sur le bouton signin
    
    extract($_POST);

    //Vérifier que l'utilisateur a bien renseigner tous les champs
    
    if(!empty($npassword) && !empty($ncpassword) && !empty($nemail) && !empty($npseudo) && !empty($nnom) && !empty($nprenom)){

    //Si la condition précedente est correcte, vérifier que l'utilisateur n'est pas soumis de caractère indésirable tel que '<>' dans les champs pseudo, nom et prenom tel que une addresse mail valide dans le champs email
    	
    if (preg_match("#^[a-z0-9.]+@[a-z0-9.]+$#i", $nemail) && preg_match("#^[a-z0-9]+$#i", $npseudo) && preg_match("#^[^<>]+$#i", $nnom) && preg_match("#^[^<>]+$#i", $nprenom))
    {

    //Si la condition précedente est correcte, vérifier que les deux mots de passe saisie par l'utilisateur sont correcte
    
    if($npassword == $ncpassword){
    
    $options = [
    'cost' => 12,
    ];

    //Si toutes les conditions sont bonnes
    
    $hashpass = password_hash($npassword, PASSWORD_BCRYPT, $options);     //Crypter le mots de passe
    
    $c = $db->prepare("SELECT email FROM users WHERE email=:email");      //Prépare et execute une requête sql afin de vérifier si un utilisateur n'ayant pas une addresse mail identique existe dans la base de donnée
    $c->execute(['email' => $nemail]);
    $result1 = $c->rowCount();
    
    $x = $db->prepare("SELECT pseudo FROM users WHERE pseudo=:pseudo");   //Prépare et execute une requête sql afin de vérifier si un utilisateur n'ayant pas de pseudo identique existe dans la base de donnée
    $x->execute(['pseudo' => $npseudo]);
    $result2 = $x->rowCount();
    
    if(($result1 == 0) && ($result2 == 0)){                 //Si les deux conditions sont bonnes

    //Créer le compte utilisateur saisie dans la base de donnée et lui envoie un mail de vérification
    
    $cle = md5(microtime(TRUE)*100000);   //Génère la clé de l'id du compte
    $date = date('Y-m-d h:i:s');

    //Prépare et execute la requête sql pour procéder à la création du compte dans la base de donnée
    	
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

    //Envoie le mail de vérification à l'utilisateur concerner et lui genère à l'intérieur le lien de l'activation avec le clé du compte et le pseudo
    
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

    //Elements de formulaire de connexions
  	echo "<h1>Connectez vous</h1>";
  	echo "<form method='post'>
    <input type='text' name='lemail' id='lemail' placeholder='Votre addresse email' required><br/>
    <br/>
  	<input type='password' name='lpassword' id='lpassword' placeholder='Votre mots de passe' required><br/>
  	<br/>
  	<input type='submit' name='formlogin' id='formlogin' value='Valider'>
  	</form>";
  
  	if(isset($_POST['formlogin'])){           //Si l'utilisateur clique sur le bouton login
    
    extract($_POST);

    //Vérifier que l'utilisateur a bien renseigner tous les champs

    if(!empty($lpassword) && !empty($lemail))
    {

    //Si la condition précedente est correcte, vérifier si l'utilisateur à saissie son pseudo ou son address mail
    
   	if (preg_match("#^[a-z0-9.]+@[a-z0-9.]+$#i", $lemail)){
		$q = $db->prepare("SELECT * FROM users WHERE email = :email");      //Si l'utilisateur à saissie son address mail, prépare et execute une requête sql afin de vérifier qu'il existe bien dans la base de donnée
   	$q->execute(['email' => $lemail]);
   	$result = $q->fetch();
   	}else{
   	$q = $db->prepare("SELECT * FROM users WHERE pseudo = :pseudo");      //Si l'utilisateur à saissie son pseudo, prépare et execute une requête sql afin de vérifier qu'il existe bien dans la base de donnée
   	$q->execute(['pseudo' => $lemail]);
   	$result = $q->fetch();
   	}
    
    if($result == true)
    {                                                               //Si l'utilisateur existe bien, vérifier qu'il est bien actif
    if($result['actif'] == "yes")
    {                                                               //Si l'utilisateur est actif, vérifier que le mots de passe saissie correspond bien au mots de passe de l'utilisateur de la base de données
     if(password_verify($lpassword, $result['password']))
     {

    //Si toutes les conditions sont bonnes, definire tous les éléments de l'utilisateur de la base de donnée dans la fonction session et rediriger l'utilisateur vers la page d'accueil
     
     $_SESSION['prenom'] = $result['prenom'];
     $_SESSION['nom'] = $result['nom'];
     $_SESSION['pseudo'] = $result['pseudo'];
     $_SESSION['email'] = $result['email'];

     header('Location: index.php');

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
    
    //Elements de formulaire de création de compte
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