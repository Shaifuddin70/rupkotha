
<?php include 'includes/functions.php';
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Commerce Site</title>
    <script src="assets/script.js" defer></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
<div class="top-bar container-fluid">
    <nav class="nav">
       <div class="container d-flex justify-content-between align-items-center">
           <i class="uil uil-bars navOpenBtn"></i>
           <a href="#" class="logo"><img src="assets/images/rupkotha-properties-bangladesh.jpg" alt=""></a>

           <ul class="nav-links mb-0">
               <i class="uil uil-times navCloseBtn"></i>
               <li><a href="#">Home</a></li>
               <li><a href="#">Services</a></li>
               <li><a href="#">Products</a></li>
               <li><a href="#">About Us</a></li>
               <li><a href="#">Contact Us</a></li>
           </ul>


           <div class="search-box d-flex justify-content-between align-items-center">
               <input type="text" placeholder="Search for products..."  />
               <i class="fa-solid fa-magnifying-glass-dollar"></i>

           </div>
       </div>
    </nav>
</div>

<!-- Main header -->
<div class="main-header container">

