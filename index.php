<?php
session_start();

// --- Authentication ---
$validUser = "jocarsa";
$validPass = "jocarsa";

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// If not logged in, process login
if (!isset($_SESSION['loggedin'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        if ($username === $validUser && $password === $validPass) {
            $_SESSION['loggedin'] = true;
            header("Location: index.php");
            exit;
        } else {
            $error = "Credenciales inválidas";
        }
    }
    // Display login form if not logged in.
    ?>
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Login</title>
      <style>
         body { font-family: Arial, sans-serif; background-color: #f7f7f7; }
         .login-container { width: 300px; margin: 100px auto; padding: 20px; background: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
         input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 10px; }
         input[type="submit"] { width: 100%; padding: 10px; background: #4CAF50; color: #fff; border: none; }
         .error { color: red; }
      </style>
    </head>
    <body>
       <div class="login-container">
         <h2>Login</h2>
         <?php if(isset($error)) { echo "<p class='error'>$error</p>"; } ?>
         <form method="post" action="index.php">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="submit" value="Ingresar">
         </form>
       </div>
    </body>
    </html>
    <?php
    exit;
}

// --- Email Web Client Interface ---

// Get selected folder and file from GET parameters
$folder = isset($_GET['folder']) ? $_GET['folder'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';

// Allow only "incoming" or "spam" as valid folder values
if ($folder !== 'incoming' && $folder !== 'spam') {
    $folder = '';
}

$baseDir = 'mail'; // Base folder where emails are stored
$folderPath = $folder ? $baseDir . '/' . $folder : '';

// If a file is selected, verify it exists and load its content
$emailData = null;
if ($folder && $file) {
    $filePath = $folderPath . '/' . basename($file); // use basename for safety
    if (file_exists($filePath)) {
        $jsonContent = file_get_contents($filePath);
        $emailData = json_decode($jsonContent, true);
    }
}

// If a folder is selected, get the list of email files
$emailList = [];
if ($folder && is_dir($folderPath)) {
    // Get all JSON files in the selected folder
    $files = glob($folderPath . '/*.json');
    // Sort files by modification time descending (latest first)
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    foreach ($files as $f) {
        $emailList[] = basename($f);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Web Client</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        #header {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
        }
        #container {
            display: flex;
            height: calc(100vh - 50px);
        }
        #nav {
            width: 200px;
            background-color: #f1f1f1;
            padding: 10px;
            box-sizing: border-box;
        }
        #emailList {
            width: 300px;
            border-right: 1px solid #ddd;
            padding: 10px;
            overflow-y: auto;
            box-sizing: border-box;
        }
        #emailContent {
            flex-grow: 1;
            padding: 10px;
            overflow-y: auto;
            box-sizing: border-box;
        }
        a {
            text-decoration: none;
            color: #333;
        }
        a:hover {
            text-decoration: underline;
        }
        .folder-link {
            display: block;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #ddd;
            border-radius: 3px;
        }
        .email-item {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        .email-item:hover {
            background-color: #f9f9f9;
        }
        .selected {
            background-color: #e0e0e0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <div id="header">
        <h1>Email Web Client</h1>
        <a href="index.php?logout=1" style="color: white;">Logout</a>
    </div>
    <div id="container">
        <!-- Left Navigation: Folder list -->
        <div id="nav">
            <h3>Folders</h3>
            <a class="folder-link" href="index.php?folder=incoming">Inbox (Received)</a>
            <a class="folder-link" href="index.php?folder=spam">Spam</a>
        </div>
        <!-- Middle: Email List -->
        <div id="emailList">
            <h3>Email List</h3>
            <?php if ($folder): ?>
                <ul style="list-style-type: none; padding: 0;">
                <?php foreach ($emailList as $emailFile): ?>
                    <li class="email-item <?php echo ($file === $emailFile) ? 'selected' : ''; ?>">
                        <a href="index.php?folder=<?php echo $folder; ?>&file=<?php echo urlencode($emailFile); ?>">
                            <?php echo htmlspecialchars($emailFile); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Select a folder from the left.</p>
            <?php endif; ?>
        </div>
        <!-- Right: Email Content -->
        <div id="emailContent">
            <h3>Email Content</h3>
            <?php if ($emailData): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emailData as $key => $value): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($key); ?></td>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Select an email to view its content.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

