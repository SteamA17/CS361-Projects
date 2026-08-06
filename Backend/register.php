<?php

    include 'connect.php';

    if(isset($_POST['signUp'])){
        $firstName=$_POST['fName'];
        $surName=$_POST['sName'];
        $email=$_POST['email'];
        $password=$_POST['password'];
        $password=md5($password);

        $checkEmail="SELECT * from users1 where email='$email'";
        $result = $conn->query($checkEmail);

        if($result->num_rows>0){
            echo "Email address already exists!!";
        }else{
            $insertQuery="INSERT INTO users1(firstName,surName,email,password) VALUES ('$firstName','$surName','$email','$password')";
            if($conn->query($insertQuery)==TRUE){
                header("Location: index.php");
            }else{
                echo"Error:".$conn->error;
            }
        }
    }

    if(isset($_POST['signIn'])){
        $email=$_POST['email'];
        $password=$_POST['password'];
        $password=md5($password);

        
        $sql="SELECT * FROM users1 WHERE email='$email' and password='$password'";
        $result=$conn->query($sql);
        if($result->num_rows>0){
            session_start();
            $row=$result->fetch_assoc();
            $_SESSION['id']=$row['id'];
            $_SESSION['firstName']=$row['firstName'];
            $_SESSION['email']=$row['email'];
            header("Location: dashboard.php");
            exit();
        }else{
            echo"Not Found, Incorrect Email or Password";
        }
    }
?>