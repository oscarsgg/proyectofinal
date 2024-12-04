
<?php 
    function connect(): mysqli{
        $db = mysqli_connect("localhost","root","","outsourcing");
        if($db){
            return $db;
        }else{
            die;
        }
    }
?>

