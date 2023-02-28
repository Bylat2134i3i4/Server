<?php
$base_connect = new mysqli("localhost", "root", "", "currentwork");

mysqli_query($base_connect, "SET NAMES 'UTF-8'");

if (!$base_connect){
  echo "Не удалось установить связь с базой данных";
  exit;
}

?>