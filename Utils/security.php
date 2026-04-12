<?php 
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

function generateToken()
{
    return bin2hex(random_bytes(32));
}
?>