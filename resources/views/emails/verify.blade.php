<!DOCTYPE html>
<html>
<body>
    <h2>Welcome to User Product App</h2>
    <p>Click the link below to set your password and activate your account:</p>
    <a href="{{ url('/api/verify-email/' . $token) }}">Set Password</a>
</body>
</html>
