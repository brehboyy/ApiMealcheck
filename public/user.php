<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/connexion.php';



$app = new \Slim\App;


$app->post('/login', function (Request $request, Response $response, array $args) {
    $postdata = file_get_contents("php://input");

    if (isset($postdata))
    {	 
        try{
            $request = json_decode($postdata);
            $name = $request->username;
            $password = $request->password;
            $contenu = $pdo->prepare('SELECT USER_Identifier,USER_Password, USER_Login FROM user WHERE UPPER(USER_Login) =?');
            $contenu->execute(array(strtoupper($name)));
            $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
            //var_dump($liste);
    
            if(count($liste)>0)
            {
                if(password_verify($password, $liste[0]['USER_Password'])){
                    $code = true;
                    $message = "Bienvenue dans le Bendo ".$name;
                    $response = array("success" => $code, "message" => $message, "USERID" => $liste[0]["USER_Identifier"]);
                }else{
                    $code = false;
                    $message = "Mot de passe ou Username non valide";
                    $response = array("success"=>$code,"message"=>$message);
                }
            //	var_dump($response);
                
            }
            else
            {
                $code = false;
                $message = "Mot de passe ou Username non valide";
                $response = array("success"=>$code,"message"=>$message);
                
            }
        }catch(Exception $ex){
            $response = array("success" => false, "message" => $ex->getMessage());
        }
        
    }
    else
    {
        $code = false;
        $message = "Données non valide";
        $response = array("success"=>$code,"message"=>$message);
    }
    $pdo = null;
    return json_encode($response);
});


$app->post('/register', function (Request $request, Response $response, array $args) use ($pdo) {
        
    $postdata = file_get_contents("php://input");
    try{
        if (isset($postdata)) {
            $request = json_decode($postdata);
            $name = $request->username;
            $email = $request->email;
            $password = $request->password;
            $contenu = $pdo->prepare('SELECT TRUE FROM user WHERE USER_Login = ? OR USER_Email = ? LIMIT 1');
            $contenu->execute(array($name, $email));
            $liste = $contenu->fetchAll();
            if(count($liste)>0)
            {
                $code = false;
                $message = "Utilisateur login ou mail deja existant";
                $response = array("success"=>$code,"message"=>$message);
            }
            else
            {
                $query = $pdo->prepare("INSERT INTO user VALUES (NULL,?,?,?,?)");
                $options =  array('cost' => 11);
                $hash = password_hash($password, PASSWORD_BCRYPT,$options);
                $query->execute(array($name,$hash,$email,1));

                if(!$query)
                {
                    $code = false;
                    $message = "Une erreur serveur ... Recommencez ...";
                    $response = array("success"=>$code,"message"=>$message);
                }
                else
                {
                    $code = true;
                    $message = "Enregistrement réussi.";
                    $response = array("success"=>$code,"message"=>$message);
                }
            }
        }	
        else{
            $code = false;
            $message = "Données non valide";
            $response = array("success"=>$code,"message"=>$message);
        }
    }catch(Exception $ex){
        $response = array("success" => false, "message" => $ex->getMessage());
    }

    $pdo = null;

    return $response;
});


$app->post('/resetpassword', function (Request $request, Response $response, array $args) use ($pdo) {
        
    $postdata = file_get_contents("php://input");
    try{
        if (isset($postdata)) {
            $request = json_decode($postdata);
            $email = $request->email;
            $contenu = $pdo->prepare('SELECT USER_Email FROM user WHERE USER_Email = ?');
            $contenu->execute(array($email));
            $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
            if(count($liste)>0)
            {		
                $to      = 'ousmane16diarra@gmail.com';
                $subject = 'Equipe MealCheck';
                $headers = array('From' =>  'mealcheck22@gmail.com',
                        'Reply-To' => 'mealcheck22@gmail.com',
                    'X-Mailer' => 'PHP/' . phpversion());
                $message = 'Un email vous a été envoyé'. 'PHP/' . phpversion();
                
                mail($to, $subject, $message, $headers);
                $msg="hello";
                mail("ousmane16diarra@gmail.com","My subject",$msg);
                $code = true;
                $response = array("success"=>$code, 'message' => $message);
            }else{
                $code = false;
                $message = "Email inexistant";
                $response = array("success"=>$code, 'message' => $message);
            }
        }
    }catch(Exception $ex){
        $code = false;
        $response = array("success"=>$code, 'message' => $ex->getMessage());
    }
    return $response;
});

$app->run();