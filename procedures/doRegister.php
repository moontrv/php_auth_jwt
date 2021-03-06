<?php
require __DIR__ . '/../inc/bootstrap.php';

$password = request()->get('password');
$confirmPassword = request()->get('confirm_password');
$email = request()->get('email');

if ($password != $confirmPassword) {
    $session->getFlashBag()->add('success', 'Passwords do NOT match');
    redirect($base_url.'/register.php');
}

$user = findUserByEmail($email);
if (!empty($user)) {
    $session->getFlashBag()->add('error', 'User Already Exists');
    redirect($base_url.'/register.php');
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$user = createUser($email, $hashed);

$expTime = time() + 3600;

$jwt = \Firebase\JWT\JWT::encode([
    'iss' => request()->$base_url,
    'sub' => "{$user['id']}",
    'exp' => $expTime,
    'iat' => time(),
    'nbf' => time(), 
    'is_admin' => $user['role_id'] == 1
], getenv("SECRET_KEY"),'HS256');

$accessToken = new Symfony\Component\HttpFoundation\Cookie('access_token', $jwt, $expTime, '/', getenv('COOKIE_DOMAIN'));

$session->getFlashBag()->add('success', 'User Added and Successfully Logged In');
redirect($base_url.'/',['cookies' => [$accessToken]]);