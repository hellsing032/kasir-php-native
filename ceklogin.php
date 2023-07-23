<?php
require 'function.php';

if(isset($_SESSION["login"])){
    //jika sudah login
} else { //jika belum login
    header("location:login.php");
}
?>