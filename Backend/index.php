<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOl</title>

    <link rel="stylesheet" href="../Frontend/style.css">
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="log-container" id="signUp" style="display: none;">
        <h1 class="form-title">Signup</h1>
        <form action="register.php" method="post">
            <div class="input-group">
                <input type="text" name="fName" id="fName" placeholder="First Name" required>
            </div>

            <div class="input-group">
                <input type="text" name="sName" id="sName" placeholder="Surname" required>
            </div>

            <div class="input-group">
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>

            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>

            <input type="submit" class="btn" value="Sign Up" name="signUp">
            <p class="or">------------or------------</p>
            
            <div class="icons">

            </div>
            <div class="links">
                <p>Already have an account?</p>
                <button class="signinbtn" id="signInButton">Signin</button>
            </div>
        </form>
    </div>

    <div class="log-container" id="signIn">
        <h1 class="form-title">Signin</h1>
        <form action="register.php" method="post">
            <div class="input-group">
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>

            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>

            <input type="submit" class="btn" value="Sign In" name="signIn">
            <p class="or">------------or------------</p>
            
            <div class="links">
                <p>Don't have an account?</p>
                <button class="signupbtn" id="signUpButton">Signup</button>
            </div>
        </form>
    </div>



    <script src="../Frontend/script.js"></script>
</body>
</html>