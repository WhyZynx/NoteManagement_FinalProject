<?php
include 'db.php';

if (isset($_POST['btnLogin'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['display_name'];
        $_SESSION['is_verified'] = $user['is_verified'];
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Email hoặc mật khẩu không chính xác!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giao diện Đăng nhập - Rose Theme</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Quicksand', sans-serif;
            background-color: #F8E7EB;
        }
        .footer-bg {
            background-color: #8E717D;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col text-gray-700">

    <header class="p-6 flex justify-between items-center w-full max-w-7xl mx-auto">
        <button class="text-2xl hover:opacity-70 transition-opacity">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <div class="flex items-center space-x-4 md:space-x-8">
            <span class="flex items-center cursor-pointer font-semibold">
                <i class="fa-solid fa-globe mr-2"></i> EN
            </span>
            <button class="bg-[#D996AA] text-white px-6 py-2 rounded-xl font-bold shadow-sm hover:bg-[#c47d93] transition-all">
                Login
            </button>
            <button class="font-bold hover:text-[#A65E7E]">Sign Up</button>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center px-4 py-10">
        <div class="max-w-6xl w-full grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            
            <div class="bg-white rounded-[45px] p-10 md:p-14 shadow-xl shadow-rose-200/50 max-w-md mx-auto w-full">
                <h1 class="text-4xl font-bold text-[#A65E7E] text-center mb-10">Login</h1>
                
                <form class="space-y-6">
                    <div class="relative">
                        <input type="text" placeholder="Email address or Username" 
                            class="w-full px-6 py-4 rounded-full border-2 border-[#D996AA] focus:outline-none focus:border-[#A65E7E] transition-colors">
                    </div>
                    
                    <div class="relative">
                        <input type="password" placeholder="Password" 
                            class="w-full px-6 py-4 rounded-full border-2 border-[#D996AA] focus:outline-none focus:border-[#A65E7E] transition-colors">
                        <i class="fa-regular fa-eye absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer"></i>
                    </div>

                    <div class="text-right">
                        <a href="#" class="text-xs text-[#A65E7E] font-semibold hover:underline">Forget Password?</a>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-[#D996AA] text-white px-12 py-3 rounded-full font-bold shadow-md hover:bg-[#c47d93] hover:scale-105 transition-all">
                            Login
                        </button>
                    </div>

                    <p class="text-sm mt-8">
                        Don't have an account? <a href="#" class="text-[#A65E7E] font-bold hover:underline">Sign up</a>
                    </p>
                </form>
            </div>

            <div class="hidden md:flex justify-center items-center">
                <img src="web_img/rose.png" 
                     alt="Rose Illustration" 
                     class="max-w-full h-auto object-contain">
            </div>
        </div>
    </main>

    <footer class="footer-bg text-white p-10 md:p-16">
        <div class="max-w-7xl mx-auto">
            <div class="w-full md:w-1/3">
                <h3 class="font-bold text-xl mb-4">Support</h3>
                <ul class="space-y-3 text-gray-200">
                    <li><a href="#" class="hover:text-white transition-colors">Help</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                </ul>
            </div>
        </div>
    </footer>

</body>
</html>