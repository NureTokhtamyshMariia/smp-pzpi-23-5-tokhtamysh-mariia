<?php
if (!isset($_SESSION['username'])) {
    header('Location: /pages/login.php');
    exit;
}

$userProfile = [];
$profileFilePath = __DIR__ . '/../db/profile.php';
if (file_exists($profileFilePath)) {
    include $profileFilePath;
}

$uploadDirectory = __DIR__ . '/../uploads/';
$errorMessage = '';
$successMessage = '';


function validateUserProfileData($firstName, $lastName, $birthDate, $bio) {
    if (empty($firstName) || empty($lastName) || empty($birthDate) || empty($bio)) {
        return "All text fields are required";
    }
    if (strlen($firstName) <= 1 || strlen($lastName) <= 1) {
        return "First and last name must be longer than 1 character";
    }
    if (strtotime($birthDate) > strtotime('-16 years')) {
        return "The user must be at least 16 years old";
    }
    if (strlen($bio) < 50) {
        return "Biography must be at least 50 characters long";
    }
    return null;
}

function processProfileImageUpload($file, $uploadDirectory, $firstName, $lastName) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['error' => 'You must upload a profile picture'];
    }
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Unsupported file type'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'File upload error: error code ' . $file['error']];
    }

    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('photo_' . md5($firstName . $lastName), true) . '.' . $ext;
    $destination = $uploadDirectory . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['error' => 'Error saving the uploaded file'];
    }

    return ['filename' => $filename];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $birthDate = trim($_POST['birth_date']);
    $bio = trim($_POST['bio']);

    $errorMessage = validateUserProfileData($firstName, $lastName, $birthDate, $bio);

    if (!$errorMessage) {
        $newUserProfile = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_date' => $birthDate,
            'bio' => $bio,
            'profile_picture' => $userProfile['profile_picture'] ?? ''
        ];

        if (isset($_FILES['profile_picture'])) {
            $uploadResult = processProfileImageUpload($_FILES['profile_picture'], $uploadDirectory, $firstName, $lastName);
            if (!empty($uploadResult['error'])) {
                $errorMessage = $uploadResult['error'];
            } else {
                $newUserProfile['profile_picture'] = $uploadResult['filename'];
            }
        } elseif (empty($userProfile['profile_picture'])) {
            $errorMessage = "You must upload a profile picture";
        }

        if (!$errorMessage) {
            $_SESSION['profile'] = $newUserProfile;
            $profileContent = "<?php\n\$userProfile = " . var_export($newUserProfile, true) . ";\n?>";

            if (file_put_contents($profileFilePath, $profileContent) === false) {
                $errorMessage = "Error saving profile data";
            } else {
                $successMessage = "Profile updated successfully!";
                $userProfile = $newUserProfile;
            }
        }
    }
}
?>

<main class="profile-page">
  <h1>👤 Personal Information</h1>

  <?php if ($errorMessage): ?>
    <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
  <?php endif; ?>

  <?php if ($successMessage): ?>
    <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="profile-form profile-flex">
    <div class="profile-left">
      <div class="profile-picture-container">
        <?php if (!empty($userProfile['profile_picture'])): ?>
          <img src="/smp-pzpi-23-5-tokhtamysh-mariia-lab4/uploads/<?php echo htmlspecialchars($userProfile['profile_picture']); ?>?t=<?php echo time(); ?>" alt="Profile Picture" class="profile-picture">
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="profile_picture">Change Profile Picture:</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
        <?php if (!empty($userProfile['profile_picture'])): ?>
          <p class="form-note">If you don’t select a new photo, your current one will remain.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="profile-right">
      <div class="form-group">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($userProfile['first_name'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($userProfile['last_name'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label for="birth_date">Date of Birth:</label>
        <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($userProfile['birth_date'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label for="bio">Biography:</label>
        <textarea id="bio" name="bio" required><?php echo htmlspecialchars($userProfile['bio'] ?? ''); ?></textarea>
      </div>
      <button type="submit" class="btn btn--primary">Save Changes</button>
    </div>
  </form>
</main>
