<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Form Register</h2>
    <form action="{{ route('register.post') }}" method="POST">
        @csrf
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="text" name="full_name" placeholder="Full Name"><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required><br>
        <button type="submit">Register</button>
    </form>
    <a href="{{ route('login') }}">Sudah punya akun? Login</a>
</body>
</html>
