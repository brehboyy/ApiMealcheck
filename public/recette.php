<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/connexion.php';



$app = new \Slim\App;


$app->post('/getById', function (Request $request, Response $response, array $args) use ($pdo) {
    $postdata = file_get_contents("php://input");
    if(isset($postdata)){
        $request = json_decode($postdata);
        $recetteId = (int)$request->id_recette;
        //$recetteId = $args['id'];
        $contenu = $pdo->prepare("SELECT r.REC_Identifier, r.REC_Title,r.REC_Description, r.REC_DescriptionFR, r.REC_PhotoUrl, r.REC_YieldNB, r.REC_Time, r.REC_ActiveTime, r.REC_CountReviews, GROUP_CONCAT(CONCAT(ri.RECING_Identifier, ' ', i.ING_Name, ' ', ri.RECING_Quantity) SEPARATOR ';') AS listIngredient FROM recette_ingredient2 ri INNER JOIN recette r ON r.REC_Identifier = ri.RECING_RecetteIdentifier INNER JOIN Ingredient i ON ri.RECING_IngredientIdentifier = i.ING_Identifier WHERE REC_Identifier = ?");
        $contenu->execute(array($recetteId));
        $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
        $liste[0]['listIngredient'] = explode(";",$liste[0]['listIngredient']);
        $code = true;
        $response = array("success"=>$code,"recette"=>$liste[0]);
    }
    else
    {
        $code = false;
        $message = "Données non valide";
        $response = array("success"=>$code,"message"=>$message);
    }
    $pdo = NULL;
    return json_encode($response);
});


$app->post('/getLivreByIdUser', function (Request $request, Response $response, array $args) use ($pdo) {
        
    $postdata = file_get_contents("php://input");
    if(isset($postdata)){
        try{
            $request = json_decode($postdata);
            $userId = (int)$request->id_user;
            $contenu = $pdo->prepare(
                'SELECT 
                    `REC_Identifier`,
                    `REC_Title`,
                    `REC_Category`,
                    `REC_Rating`,
                    `REC_Description`,
                    `REC_Photourl` 
                FROM 
                    Recette r 
                    INNER JOIN (
                        SELECT DISTINCT 
                            RECING_RecetteIdentifier
                        FROM 
                            recette_ingredient2
                        WHERE 
                            RECING_RecetteIdentifier 
                            NOT IN 
                            (
                                SELECT DISTINCT 
                                    RECING_RecetteIdentifier 
                                FROM 
                                    recette_ingredient2 
                                WHERE 
                                    `RECING_IngredientIdentifier` 
                                    NOT IN (
                                        SELECT
                                            f.FRI_MasterIngredientIdentifier 
                                        FROM 
                                            Frigo2 f 
                                        WHERE 
                                            f.FRI_UserIdentifier = ?
                                    )
                            )
                    ) 
                    l ON r.REC_Identifier = l.RECING_RECETTEIdentifier'
            );
            $contenu->execute(array($userId));
            $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
            $response = array();
            $code = true;
            $response = array("success"=>$code,"recette"=>$liste);
            echo json_encode($response);
        }catch(Exeption $e){
            $response = array();
            $code = false;
            $message = "Pb dans l'api!!!";
            $response = array("success"=>$code,"message"=>$e);
        }
    }
    else
    {
        $response = array();
        $code = false;
        $message = "Données non valide";
        $response = array("success"=>$code,"message"=>$message);
        echo json_encode($response);
    }
    $pdo = NULL;
    return json_encode($response);
});


$app->post('/getLivreByIdUserIdIng', function (Request $request, Response $response, array $args) use ($pdo) {
   $postdata = file_get_contents("php://input");
   if(isset($postdata)){
       $request = json_decode($postdata);
       $userId = (int)$request->id_user;
       $ingredientId = (int)$request->id_ingredient;
       $contenu = $pdo->prepare(
        'SELECT 
        `REC_Identifier`,
        `REC_Title`,
        `REC_Category`,
        `REC_Rating`,
        `REC_Description`,
        `REC_Photourl` 
    FROM 
        Recette r 
        INNER JOIN (
            SELECT DISTINCT 
                RECING_RecetteIdentifier
            FROM 
                recette_ingredient2
            WHERE 
                RECING_RecetteIdentifier = ? 
                AND RECING_RecetteIdentifier
                NOT IN 
                (
                    SELECT DISTINCT 
                        RECING_RecetteIdentifier
                    FROM 
                        recette_ingredient2 
                    WHERE 
                        `RECING_IngredientIdentifier` 
                        NOT IN (
                            SELECT
                                f.FRI_MasterIngredientIdentifier 
                            FROM 
                                Frigo2 f 
                            WHERE 
                                f.FRI_UserIdentifier = ?
                        )
                )
        ) 
        l ON r.REC_Identifier = l.RECING_RECETTEIdentifier'
       );
       $contenu->execute(array($userId, $ingredientId));
       $liste = $contenu->fetchAll(PDO::FETCH_ASSOC);
       $response = array();
       $code = true;
       $response = array("success"=>$code,"recette"=>$liste);
       echo json_encode($response);
   }
   else
   {
       $response = array();
       $code = false;
       $message = "Données non valide";
       $response = array("success"=>$code,"message"=>$message);
       echo json_encode($response);
   }
   $pdo = NULL;
    return json_encode($response);
});

$app->run();