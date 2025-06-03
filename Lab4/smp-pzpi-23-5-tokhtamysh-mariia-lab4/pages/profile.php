<?php

if (!isset($_SESSION['username'])) {
    header('Location: /pages/login.php');
    exit;
}

$profile = [];
$profile_file = __DIR__ . '/../db/profile.php';
if (file_exists($profile_file)) {
    include $profile_file;
}

$upload_dir = __DIR__ . '/../uploads/';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $birth_date = trim($_POST['birth_date']);
    $bio = trim($_POST['bio']);

    if (empty($first_name) || empty($last_name) || empty($birth_date) || empty($bio)) {
        $error = "All text fields are required";
    } elseif (strlen($first_name) <= 1 || strlen($last_name) <= 1) {
        $error = "First and last name must be longer than 1 character";
    } elseif (strtotime($birth_date) > strtotime('-16 years')) {
        $error = "The user must be at least 16 years old";
    } elseif (strlen($bio) < 50) {
        $error = "Bio must be at least 50 characters long";
    } else {
        $new_profile = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'birth_date' => $birth_date,
            'bio' => $bio,
            'profile_picture' => $profile['profile_picture'] ?? ''
        ];

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file = $_FILES['profile_picture'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($file['type'], $allowed_types)) {
                $error = "Unsupported file type";
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $error = "File upload error: error code " . $file['error'];
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('img_', true) . '.' . $ext;
                $destination = $upload_dir . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    $error = "Error saving the file";
                } else {
                    $new_profile['profile_picture'] = $filename;
                }
            }
        } elseif (empty($profile['profile_picture'])) {
            $error = "You must upload a photo";
        }

        if (!$error) {
            $_SESSION['profile'] = $new_profile;
            $profile_data = "<?php\n\$profile = " . var_export($new_profile, true) . ";\n?>";

            if (file_put_contents($profile_file, $profile_data) === false) {
                $error = "Error saving data";
            } else {
                $success = "Data saved successfully!";
                $profile = $new_profile;
            }
        }
    }
}
?>

<main class="profile-page">
  <h1>👤Your Profile</h1>

  <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="profile-form profile-flex">
    <div class="profile-left">
      <div class="profile-picture-container">
        <?php if (!empty($profile['profile_picture'])): ?>
          <img src="/smp-pzpi-23-5-tokhtamysh-mariia-lab4/uploads/<?php echo htmlspecialchars($profile['profile_picture']); ?>?t=<?php echo time(); ?>" alt="Profile Picture" class="profile-picture">
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="profile_picture">Upload a New Photo:</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
        <?php if (!empty($profile['profile_picture'])): ?>
          <p class="form-note">If you don’t select a new photo, the current one will be kept.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="profile-right">
      <div class="form-group">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label for="birth_date">Date of Birth:</label>
        <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($profile['birth_date'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label for="bio">About You:</label>
        <textarea id="bio" name="bio" required><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
      </div>
      <button type="submit" class="btn btn--primary">Save Changes</button>
    </div>
  </form>
</main>
