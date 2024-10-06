<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if admin is logged in
$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'];
$isPremiumUser = isset($_SESSION['isPremiumUser']) && $_SESSION['isPremiumUser'];

$filePath = 'items.json';
$usersFilePath = 'premium_users.json';

// Function to read items
function readItems() {
    global $filePath;
    if (file_exists($filePath)) {
        $json = file_get_contents($filePath);
        return json_decode($json, true);
    }
    return [];
}

// Function to write items
function writeItems($items) {
    global $filePath;
    file_put_contents($filePath, json_encode($items, JSON_PRETTY_PRINT));
}

// Function to read premium users
function readPremiumUsers() {
    global $usersFilePath;
    if (file_exists($usersFilePath)) {
        $json = file_get_contents($usersFilePath);
        return json_decode($json, true);
    }
    return [];
}

// Function to write premium users
function writePremiumUsers($users) {
    global $usersFilePath;
    file_put_contents($usersFilePath, json_encode($users, JSON_PRETTY_PRINT));
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read existing items
    $items = readItems();
    $premiumUsers = readPremiumUsers();

    // Handle item addition request
    if (isset($_POST['addItem'])) {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $image = $_POST['image'] ?? '';
        $freeLink = $_POST['freeLink'] ?? '';
        $premiumLink = $_POST['premiumLink'] ?? '';
        $type = $_POST['type'] ?? '';

        // Validate input
        if ($name && $description && $image && $freeLink && $premiumLink && $type) {
            $items[] = [
                'name' => $name,
                'description' => $description,
                'image' => $image,
                'freeLink' => $freeLink,
                'premiumLink' => $premiumLink,
                'type' => $type
            ];
            writeItems($items);

            // Redirect to avoid form resubmission
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo 'All fields are required!';
        }
    }

    // Handle item deletion request
    if (isset($_POST['deleteItem'])) {
        $index = $_POST['index'];
        unset($items[$index]);
        $items = array_values($items);
        writeItems($items);

        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle item edit request
    if (isset($_POST['editItem'])) {
        $index = $_POST['index'];
        $items[$index] = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'image' => $_POST['image'],
            'freeLink' => $_POST['freeLink'],
            'premiumLink' => $_POST['premiumLink'],
            'type' => $_POST['type']
        ];
        writeItems($items);

        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Admin login check
    if (isset($_POST['adminLogin']) && $_POST['adminUsername'] === 'jar' && $_POST['adminPassword'] === 'constantine') {
        $_SESSION['isAdmin'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Premium user login check
    if (isset($_POST['premiumLogin'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        foreach ($premiumUsers as $user) {
            if ($user['email'] === $email && $user['password'] === $password) {
                $_SESSION['isPremiumUser'] = true;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=invalid');
        exit;
    }

    // Add premium user by admin
    if (isset($_POST['addPremiumUser'])) {
        $email = $_POST['newEmail'];
        $password = $_POST['newPassword'];
        $days = (int)$_POST['days'];

        if (filter_var($email, FILTER_VALIDATE_EMAIL) && strpos($email, '@gmail.com') !== false) {
            $expiry = time() + ($days * 86400);
            $premiumUsers[] = ['email' => $email, 'password' => $password, 'expiry' => $expiry];
            writePremiumUsers($premiumUsers);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle logout
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle premium purchase request
    if (isset($_POST['buyPremium'])) {
        header('Location: https://wa.me/1234567890?text=I%20want%20to%20purchase%20premium%20access');
        exit;
    }
}

// Read items for display
$items = readItems();
$premiumUsers = readPremiumUsers();
$categories = ['apps', 'games', 'konten', 'string', 'module', 'chatgpt_spesial'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxTecno Website</title>
    <style>
        /* CSS styles */
        body {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #000;
            color: #FFF;
            font-family: Arial, sans-serif;
        }
        .navbar {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #1A1A1A;
            padding: 10px;
            flex-wrap: wrap;
        }
        .navbar-title {
            color: #0F0;
            font-size: 24px;
            font-weight: bold;
            margin-right: auto;
        }
        .button-container {
            display: flex;
            gap: 10px;
        }
        .navbar a, .navbar button {
            color: #FFF;
            padding: 10px 20px;
            margin: 0;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: transform 0.3s, background-color 0.3s;
        }
        .navbar a.youtube-btn {
            background-color: #F00;
        }
        .navbar a.telegram-btn {
            background-color: #0088cc;
        }
        .navbar button.buy-premium-button {
            background-color: #FFD700;
            color: #000;
        }
        .navbar button.login-button, .navbar button.premium-login-button {
            background-color: #4CAF50;
            color: #FFF;
        }
        .navbar button.admin-login-button {
            background-color: #FF6347;
            color: #FFF;
        }
        .navbar a.youtube-btn:hover {
            transform: scale(1.1);
            background-color: #A00;
        }
        .navbar a.telegram-btn:hover {
            transform: scale(1.1);
            background-color: #005f99;
        }
        .navbar button.buy-premium-button:hover {
            background-color: #FFC300;
        }
        .navbar button.login-button:hover, .navbar button.premium-login-button:hover {
            background-color: #45a049;
        }
        .navbar button.admin-login-button:hover {
            background-color: #FF4500;
        }
        .floating-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #1A1A1A;
            border: 2px solid #FFF;
            padding: 20px;
            border-radius: 10px;
            z-index: 1000;
            text-align: center;
        }
        .floating-popup input, .floating-popup select {
            padding: 10px;
            margin-top: 10px;
            border: 2px solid #FFF;
            border-radius: 5px;
            width: 80%;
        }
        .close-popup {
            background-color: #F00;
            color: #FFF;
            border: none;
            padding: 5px 10px;
            margin-top: 10px;
            cursor: pointer;
        }
        .close-popup:hover {
            background-color: #C00;
        }
        .category-button {
            background-color: #444;
            color: #FFF;
            font-weight: bold;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .category-button:hover {
            background-color: #666;
        }
        .item-list {
            margin: 20px;
            padding: 20px;
            border: 1px solid #FFF;
            border-radius: 10px;
        }
        .item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .item img {
            width: 150px;
            height: 150px;
            margin-right: 10px;
            border-radius: 5px;
        }
        .add-button {
            display: block;
            background-color: #0F0;
            color: #000;
            font-weight: bold;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px auto;
        }
        .add-button:hover {
            background-color: #0C0;
        }
        .delete-button, .edit-button {
            background-color: #F00;
            color: #FFF;
            border: none;
            padding: 5px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        .delete-button:hover, .edit-button:hover {
            background-color: #C00;
        }
        .download-button {
            background-color: #007BFF;
            color: #FFF;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
            transition: background-color 0.3s, transform 0.3s;
        }
        .download-button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .disabled {
            background-color: #555;
            cursor: not-allowed;
        }
        .premium-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #1A1A1A;
            border: 2px solid #FFF;
            padding: 20px;
            border-radius: 10px;
            z-index: 1000;
            text-align: center;
        }
        .premium-popup button {
            margin: 10px;
            padding: 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .buy-button {
            background-color: #FF4500;
            color: #FFF;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
            transition: background-color 0.3s, transform 0.3s;
        }
        .buy-button:hover {
            background-color: #FF6347;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-title">MaxTecno WEBSITE</div>
        <div class="button-container">
            <a class="youtube-btn" href="https://youtube.com/@fixtecno?si=A3yWI-Z__LPHgAXn" target="_blank">YouTube</a>
            <a class="telegram-btn" href="https://t.me/maxtecnoo" target="_blank">Telegram Grup</a>
            <button class="buy-premium-button" id="buyPremiumBtn" onclick="showPremiumPricing()">Beli Premium</button>
            <button class="premium-login-button" id="loginPremiumBtn"><?php echo $isPremiumUser ? 'Logout' : 'Login Premium'; ?></button>
            <button class="admin-login-button" id="adminLoginBtn"><?php echo $isAdmin ? 'Admin Success' : 'Admin Login'; ?></button>
            <?php if ($isAdmin): ?>
                <button class="add-button" onclick="showForm()">+</button>
                <button class="add-button" onclick="showAddUserForm()">Add Premium User</button>
            <?php endif; ?>
        </div>
    </div>

    <div style="text-align: center; margin: 20px;">
        <button class="category-button" onclick="showCategory('apps')">Apps</button>
        <button class="category-button" onclick="showCategory('games')">Games</button>
        <button class="category-button" onclick="showCategory('konten')">Konten Aplikasi</button>
        <button class="category-button" onclick="showCategory('string')">String Set Edit</button>
        <button class="category-button" onclick="showCategory('module')">Module Set Edit</button>
        <button class="category-button" onclick="showCategory('chatgpt_spesial')">ChatGpt Mod Spesial (Indonesia Only)</button>
    </div>

    <?php foreach ($categories as $category): ?>
        <div class="item-list" id="<?php echo $category; ?>List" style="display: none;">
            <h3><?php echo ucfirst(str_replace('_', ' ', $category)); ?></h3>
            <?php foreach ($items as $index => $item): ?>
                <?php if ($item['type'] === $category): ?>
                    <div class="item">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div>
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <?php if ($category === 'chatgpt_spesial'): ?>
                                <p><a href="<?php echo htmlspecialchars($item['freeLink']); ?>" class="buy-button" target="_blank">Beli</a></p>
                            <?php else: ?>
                                <p><a href="<?php echo htmlspecialchars($item['freeLink']); ?>" class="download-button" target="_blank">Download »Free Mode«</a></p>
                                <p>
                                    <a href="<?php echo htmlspecialchars($item['premiumLink']); ?>" 
                                       class="download-button <?php echo !$isPremiumUser ? 'disabled' : ''; ?>" 
                                       target="<?php echo !$isPremiumUser ? '' : '_blank'; ?>" 
                                       onclick="<?php echo !$isPremiumUser ? 'event.preventDefault(); alert(\'You must be logged in to access this feature.\');' : ''; ?>">
                                        Download »Premium Mode«
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php if ($isAdmin): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="deleteItem" value="1">
                                <input type="hidden" name="index" value="<?php echo $index; ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                            <button class="edit-button" onclick="showEditForm('<?php echo $index; ?>', '<?php echo htmlspecialchars($item['name']); ?>', '<?php echo htmlspecialchars($item['description']); ?>', '<?php echo htmlspecialchars($item['image']); ?>', '<?php echo htmlspecialchars($item['freeLink']); ?>', '<?php echo htmlspecialchars($item['premiumLink']); ?>', '<?php echo htmlspecialchars($item['type']); ?>')">Edit</button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <!-- Add Item Form -->
    <div id="addForm" class="floating-popup">
        <h3>Add New Item</h3>
        <form method="post" action="">
            <input type="hidden" name="addItem" value="1">
            <input type="text" name="name" placeholder="Name" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <select name="type" required>
                <option value="">Select Type</option>
                <option value="apps">Apps</option>
                <option value="games">Games</option>
                <option value="konten">Konten Aplikasi</option>
                <option value="string">String Set Edit</option>
                <option value="module">Module Set Edit</option>
                <option value="chatgpt_spesial">ChatGpt Spesial</option>
            </select>
            <input type="text" name="image" placeholder="Image URL (150x150)" required>
            <input type="text" name="freeLink" placeholder="Free Mode Link" required>
            <input type="text" name="premiumLink" placeholder="Premium Mode Link" required>
            <button type="submit">Add</button>
            <button type="button" onclick="hideForm()">Cancel</button>
        </form>
        <p>Ensure the image is 150x150</p>
    </div>

    <!-- Edit Item Form -->
    <div id="editForm" class="floating-popup">
        <h3>Edit Item</h3>
        <form method="post" action="">
            <input type="hidden" name="editItem" value="1">
            <input type="hidden" id="editIndex" name="index" value="">
            <input type="text" id="editName" name="name" placeholder="Name" required>
            <textarea id="editDescription" name="description" placeholder="Description" required></textarea>
            <select id="editType" name="type" required>
                <option value="">Select Type</option>
                <option value="apps">Apps</option>
                <option value="games">Games</option>
                <option value="konten">Konten Aplikasi</option>
                <option value="string">String Set Edit</option>
                <option value="module">Module Set Edit</option>
                <option value="chatgpt_spesial">ChatGpt Spesial</option>
            </select>
            <input type="text" id="editImage" name="image" placeholder="Image URL (150x150)" required>
            <input type="text" id="editFreeLink" name="freeLink" placeholder="Free Mode Link" required>
            <input type="text" id="editPremiumLink" name="premiumLink" placeholder="Premium Mode Link" required>
            <button type="submit">Save</button>
            <button type="button" onclick="hideEditForm()">Cancel</button>
        </form>
    </div>

    <!-- Admin Login Form -->
    <div id="adminLoginForm" class="floating-popup">
        <h3>Admin Login</h3>
        <form method="post" action="">
            <input type="text" name="adminUsername" placeholder="Username" required>
            <input type="password" name="adminPassword" placeholder="Password" required>
            <button type="submit" name="adminLogin">Login</button>
            <button type="button" class="close-popup" onclick="hideAdminLogin()">Cancel</button>
        </form>
    </div>

    <!-- Premium Login Form -->
    <div id="premiumLoginForm" class="floating-popup">
        <h3>Premium User Login</h3>
        <form method="post" action="">
            <input type="email" name="email" placeholder="Email (@gmail.com)" required pattern="[a-z0-9._%+-]+@gmail\.com$">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="premiumLogin">Login</button>
            <button type="button" class="close-popup" onclick="hidePremiumLogin()">Cancel</button>
        </form>
    </div>

    <!-- Add Premium User Form -->
    <div id="addUserForm" class="floating-popup">
        <h3>Add Premium User</h3>
        <form method="post" action="">
            <input type="hidden" name="addPremiumUser" value="1">
            <input type="email" name="newEmail" placeholder="Email (@gmail.com)" required pattern="[a-z0-9._%+-]+@gmail\.com$">
            <input type="password" name="newPassword" placeholder="Password" required>
            <input type="number" name="days" placeholder="Days (e.g. 30)" required min="1">
            <button type="submit">Add</button>
            <button type="button" onclick="hideAddUserForm()">Cancel</button>
        </form>
    </div>

    <!-- Premium Pricing Popup -->
    <div id="premiumPricing" class="premium-popup">
        <h3>HARGA PREMIUM LIST</h3>
        <p>10K » 15 HARI VIP👑</p>
        <p>30K » 45 HARI VIP👑</p>
        <p>50K » PERMANEN VIP👑</p>
        <p>[PREMIUM MODE]👑 » AKSES SEMUA LINK DOWNLOAD TANPA IKLAN SEDIKITPUN DOWNLOAD JADI JAUH LEBIH MUDAH DAN SELALU DAPAT UPDATE</p>
        <button onclick="buyPremium()" style="background-color: #28a745; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">Beli</button>
        <button onclick="hidePremiumPricing()" style="background-color: #dc3545; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">Close</button>
    </div>

    <script>
        // Show the add form
        function showForm() {
            document.getElementById('addForm').style.display = 'block';
        }

        // Hide the add form
        function hideForm() {
            document.getElementById('addForm').style.display = 'none';
        }

        // Show the edit form with pre-filled data
        function showEditForm(index, name, description, image, freeLink, premiumLink, type) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('editIndex').value = index;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editImage').value = image;
            document.getElementById('editFreeLink').value = freeLink;
            document.getElementById('editPremiumLink').value = premiumLink;
            document.getElementById('editType').value = type;
        }

        // Hide the edit form
        function hideEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }

        // Show admin login form
        document.getElementById('adminLoginBtn').addEventListener('click', function() {
            if (this.textContent === 'Admin Login') {
                document.getElementById('adminLoginForm').style.display = 'block';
            }
        });

        // Hide admin login form
        function hideAdminLogin() {
            document.getElementById('adminLoginForm').style.display = 'none';
        }

        // Show premium login form
        document.getElementById('loginPremiumBtn').addEventListener('click', function() {
            if (this.textContent === 'Login Premium') {
                document.getElementById('premiumLoginForm').style.display = 'block';
            }
        });

        // Hide premium login form
        function hidePremiumLogin() {
            document.getElementById('premiumLoginForm').style.display = 'none';
        }

        // Show the selected category and hide the others
        function showCategory(category) {
            <?php foreach ($categories as $category): ?>
                document.getElementById('<?php echo $category; ?>List').style.display = 'none';
            <?php endforeach; ?>
            document.getElementById(category + 'List').style.display = 'block';
        }

        // Initially show the Apps category
        showCategory('apps');

        // Show add user form
        function showAddUserForm() {
            document.getElementById('addUserForm').style.display = 'block';
        }

        // Hide add user form
        function hideAddUserForm() {
            document.getElementById('addUserForm').style.display = 'none';
        }

        // Show premium pricing popup
        function showPremiumPricing() {
            document.getElementById('premiumPricing').style.display = 'block';
        }

        // Hide premium pricing popup
        function hidePremiumPricing() {
            document.getElementById('premiumPricing').style.display = 'none';
        }

        // Redirect to WhatsApp for purchase
        function buyPremium() {
            window.location.href = 'https://wa.me/1234567890?text=I%20want%20to%20purchase%20premium%20access';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const scaleFactor = 1 / window.devicePixelRatio;
            const metaViewport = document.querySelector('meta[name="viewport"]');
            if (metaViewport) {
                metaViewport.content = `width=device-width, initial-scale=${scaleFactor}`;
            }
        });
    </script>
</body>
</html>
