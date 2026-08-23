<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/subscription.php';

$errors = [];
$success = false;

// Fetch subscription plans for dropdown
$stmt = db()->prepare('SELECT * FROM subscription_plans WHERE status = :active ORDER BY price ASC');
$stmt->execute([':active' => 'ACTIVE']);
$allPlans = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $name = sanitize_input($_POST['business_name'] ?? '');
        $ownerName = sanitize_input($_POST['owner_name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $businessType = sanitize_input($_POST['business_type'] ?? ''); // optional
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $planId = (int)($_POST['plan_id'] ?? 0);

        // Validation
        $fieldErrors = validate_required($_POST, ['business_name', 'owner_name', 'email', 'password', 'confirm_password']);
        if ($fieldErrors) {
            $errors = array_merge($errors, array_values($fieldErrors));
        }
        if (!validate_email($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        if (!empty($phone) && !validate_phone($phone)) {
            $errors[] = 'Please enter a valid phone number.';
        }
        if ($planId <= 0) {
            $errors[] = 'Please select a subscription plan.';
        }

        if (empty($errors)) {
            $pdo = db();
            try {
                $slug = slugify($name);

                // Check duplicate business by email or slug
                $check = $pdo->prepare('SELECT COUNT(*) FROM restaurants WHERE email = :email OR slug = :slug');
                $check->execute([':email' => $email, ':slug' => $slug]);
                $businessExists = (int)$check->fetchColumn() > 0;

                // Check duplicate staff email
                $staffCheck = $pdo->prepare('SELECT COUNT(*) FROM restaurant_staff WHERE email = :email');
                $staffCheck->execute([':email' => $email]);
                $staffEmailExists = (int)$staffCheck->fetchColumn() > 0;

                if ($businessExists || $staffEmailExists) {
                    $errors[] = 'A business with this email or name already exists.';
                } else {
                    $pdo->beginTransaction();

                    // Insert business (restaurant table)
                    $stmt = $pdo->prepare('
                        INSERT INTO restaurants (name, slug, owner_name, email, phone, description, status)
                        VALUES (:name, :slug, :owner_name, :email, :phone, :description, :status)
                    ');
                    $stmt->execute([
                        ':name' => $name,
                        ':slug' => $slug,
                        ':owner_name' => $ownerName,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':description' => $businessType, // store type in description for now
                        ':status' => RESTAURANT_STATUS_PENDING,
                    ]);
                    $businessId = (int)$pdo->lastInsertId();

                    // Insert owner staff
                    $staffStmt = $pdo->prepare('
                        INSERT INTO restaurant_staff (restaurant_id, name, email, password_hash, role)
                        VALUES (:business_id, :name, :email, :password_hash, :role)
                    ');
                    $staffStmt->execute([
                        ':business_id' => $businessId,
                        ':name' => $ownerName,
                        ':email' => $email,
                        ':password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_BCRYPT_COST]),
                        ':role' => ROLE_OWNER,
                    ]);
                    $staffId = (int)$pdo->lastInsertId();

                    // Create pending subscription
                    $subscriptionId = create_subscription($businessId, $planId, SUBSCRIPTION_STATUS_PENDING, SUBSCRIPTION_PAYMENT_PENDING);

                    $pdo->commit();

                    audit_log('RESTAURANT', $staffId, $businessId, 'Business registered via public form', ['plan_id' => $planId]);

                    $success = true;
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Your Business — DineX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow p-8">
        <h1 class="text-3xl font-bold text-gray-900">Register your business</h1>
        <p class="mt-2 text-sm text-gray-500">Join DineX and start accepting QR orders for your café, hotel, or restaurant.</p>

        <?php if ($success): ?>
            <div class="mt-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                <strong>Registration submitted!</strong> Your account is pending founder approval. You will be able to login after activation.
            </div>
            <a href="index.php" class="mt-4 inline-block text-orange-600">Back to home</a>
        <?php else: ?>
            <?php if ($errors): ?>
                <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="mt-6 grid grid-cols-1 gap-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-medium">Business Name</label>
                    <input type="text" name="business_name" required class="mt-1 w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Business Type</label>
                    <select name="business_type" class="mt-1 w-full border rounded-lg px-3 py-2">
                        <option value="">Select type</option>
                        <option value="Restaurant">Restaurant</option>
                        <option value="Cafe">Café</option>
                        <option value="Hotel">Hotel</option>
                        <option value="Cloud Kitchen">Cloud Kitchen</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Owner Name</label>
                    <input type="text" name="owner_name" required class="mt-1 w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Email</label>
                    <input type="email" name="email" required class="mt-1 w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Phone</label>
                    <input type="text" name="phone" class="mt-1 w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Password</label>
                    <input type="password" name="password" required minlength="8" class="mt-1 w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="8" class="mt-1 w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Subscription Plan</label>
                    <select name="plan_id" required class="mt-1 w-full border rounded-lg px-3 py-2">
                        <option value="">Select plan</option>
                        <?php foreach ($allPlans as $plan): ?>
                            <option value="<?= (int)$plan['id'] ?>">
                                <?= e($plan['name']) ?> — ₹<?= e(number_format($plan['price'], 2)) ?> / <?= e(strtolower($plan['billing_cycle'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="mt-2 bg-orange-600 text-white px-4 py-3 rounded-lg hover:bg-orange-700">Create Account</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
