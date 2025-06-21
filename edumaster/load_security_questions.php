<?php
require 'db.php';

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    header("Location: forgot_password.php?error=empty_email");
    exit;
}

$stmt = $pdo->prepare("SELECT security_question_1, security_question_2 FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: forgot_password.php?error=user_not_found");
    exit;
}

$question1 = htmlspecialchars($user['security_question_1']);
$question2 = htmlspecialchars($user['security_question_2']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Answer Security Questions</title>
  <link rel="stylesheet" href="login.css"/>
</head>
<body>
  <div class="section">
    <div class="container">
      <div class="row full-height justify-content-center">
        <div class="col-12 text-center align-self-center py-5">
          <div class="section pb-5 pt-5 pt-sm-2 text-center">
            <h4 class="mb-4 pb-3">Answer Security Questions</h4>
            <form action="verify_security_answers.php" method="POST">
              <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

              <div class="form-group">
                <label><?= $question1 ?></label>
                <input type="text" name="security_answer_1" class="form-style" required>
              </div>

              <div class="form-group mt-2">
                <label><?= $question2 ?></label>
                <input type="text" name="security_answer_2" class="form-style" required>
              </div>

              <button type="submit" class="btn mt-4">Submit</button>
              <p class="mt-3"><a href="login.html" class="link">Back to Login</a></p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
