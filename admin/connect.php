 <?php
 $conn=new mysqli('localhost','root','');
 if($conn->connect_error){
 die('Ketnoithatbai:'.$conn->connect_error);
 }
 echo'Ketnoithanhcong';
 ?>