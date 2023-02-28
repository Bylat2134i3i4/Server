<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("content-type: json/application; charset=utf-8");


require 'DataBaseInit.php';


if ($_SERVER['REQUEST_METHOD'] === "GET"){
  $url =  $_SERVER['QUERY_STRING']; // считываем тип запроса, то есть какой аргумент указан в url
  if ($url === "get_log_pass_id"){ //тут идет получение логина, id, пароля пользователей
    // запрос: http://cerver/index.php?get_log_pass_id
    $query = $base_connect->query("SELECT id, login, password FROM users");
    $persons_array=mysqli_fetch_all($query, MYSQLI_ASSOC);
  
    echo json_encode(["persons"=>$persons_array]);
  }
  else{//тут идет получение всех данных о конкретном пользователе запрос: http://cerver/index.php?id=1
    $url = explode("=", $url);

    $Type_Comand = $url[0];

    if ($Type_Comand === "delete_folder"){// тут идет удаление конкретной папки вместе с ее карточками
      // запрос выглядит так: http://cerver/index.php?delete_folder=1
      $id_folder = $url[1];
      $delete_folder = $base_connect->query("DELETE FROM folder WHERE id_folder=$id_folder");
      $delete_cards = $base_connect->query("DELETE FROM card WHERE id_folder=$id_folder");
    }
    if ($Type_Comand === "delete_card"){ // тут идет удаление конкретной карточки 
      // запрос выглядит так: http://cerver/index.php?delete_card=1
      $id_card = $url[1];
      $delete_card = $base_connect->query("DELETE FROM card WHERE id_card=$id_card");

      $query = $base_connect->query("SELECT id_folder as 'id' FROM card WHERE id_card=2");

      $id_folder =  mysqli_fetch_array($query)['id'];

      $query = $base_connect->query("SELECT amount_card as 'am' FROM folder WHERE id_folder=$id_folder");

      $new_amount = (integer) mysqli_fetch_array($query)['am'] - 1;

      $query = $base_connect->query("UPDATE folder SET amount_card=$new_amount WHERE id_folder=$id_folder");
    }
    if ($Type_Comand === "get_user"){// тут идет отправка всех данных (папки, карточки, личные данные) конкретного пользователя
      // запрос выглядит так: http://cerver/index.php?get_user=1
      $id_user = $url[1];
      $query = $base_connect->query("SELECT * FROM users WHERE id=$id_user");
      $person_arr = mysqli_fetch_all($query, MYSQLI_ASSOC);

      $query = $base_connect->query("SELECT * FROM folder WHERE user_Id=$id_user");
      $folder_arr = mysqli_fetch_all($query, MYSQLI_ASSOC);

      $cards_arr = [];
      foreach ($folder_arr as $arr){
        $id_fold = $arr['id_folder'];
        $query = $base_connect->query("SELECT * FROM card WHERE id_folder=$id_fold");
        $cards_arr[] = mysqli_fetch_all($query, MYSQLI_ASSOC);
      }

      echo json_encode(["person"=>$person_arr, "his_folders"=>$folder_arr, "his_cards"=>$cards_arr]);
    }   
}
}else{ // код для POST запросов
  $url =  $_SERVER['QUERY_STRING'];
  $url = explode("=", $url);
  $Type_Comand = $url[0];
  $data = json_decode(file_get_contents("php://input")); // получаем данные о клиенте

  if ($Type_Comand === "add_user"){//добавление нового пользователя в базу
    // запрос: http://cerver/index.php?add_user
    $query = $base_connect->query("SELECT MAX(id) as 'id' FROM users");
    $max_id= (integer) mysqli_fetch_array($query)['id'] + 1;

    $query = $base_connect->query("INSERT INTO users (id, name, login, password, icon) VALUES ('$max_id', '$data->name', '$data->login', '$data->password', '$data->icon')");

  }
  if ($Type_Comand === "add_folder"){//добавление новой папки в базу
    // запрос: http://cerver/index.php?delete_folder=2
    $query = $base_connect->query("SELECT MAX(id_folder) as 'id' FROM folder");
    $max_id = (integer) mysqli_fetch_array($query)['id'] + 1;

    $query = $base_connect->query("INSERT INTO folder (user_Id, id_folder, name, icon, amount_card, user_name) VALUES ('$data->user_Id', '$max_id', '$data->name', '$data->icon', '$data->amount_card', '$data->user_name')");
  }
  if ($Type_Comand === "add_card"){//добавление новой карточки в базу
    // запрос: http://cerver/index.php?add_card
    $query = $base_connect->query("SELECT MAX(id_card) as 'id' FROM card");
    $max_id = (integer) mysqli_fetch_array($query)['id'] + 1;

    $query = $base_connect->query("INSERT INTO card (id_card, id_folder, front, back, card_type, time_create) VALUES ('$max_id', '$data->id_folder', '$data->front', '$data->back', '$data->card_type', '$data->time_create')");

    $query = $base_connect->query("SELECT amount_card as 'am' FROM folder WHERE id_folder='$data->id_folder'");

    $new_amount = (integer) mysqli_fetch_array($query)['am'] + 1;

    $query = $base_connect->query("UPDATE folder SET amount_card='$new_amount' WHERE id_folder='$data->id_folder'");
  }
  // if ($Type_Comand === "change_icon"){
  //   $query = $base_connect->query("UPDATE persons")
  // }
}

$base_connect->close();

?>
