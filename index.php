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
      <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
      <style>
         body { font-family: 'Roboto', sans-serif; background-color: #f7f7f7; }
         .login-container { width: 320px; margin: 120px auto; padding: 20px; background: #fff; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
         input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 3px; }
         input[type="submit"] { width: 100%; padding: 10px; background: #4CAF50; color: #fff; border: none; border-radius: 3px; cursor: pointer; }
         input[type="submit"]:hover { background: #45a049; }
         .error { color: red; text-align: center; }
      </style>
    </head>
    <body>
       <div class="login-container">
         <h2 style="text-align:center;">Login</h2>
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

// --- Deletion Action ---
// If a deletion is requested, verify parameters and delete the file.
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    if (isset($_GET['folder']) && ($_GET['folder'] === 'incoming' || $_GET['folder'] === 'spam') && isset($_GET['file'])) {
        $folderToDelete = $_GET['folder'];
        $fileToDelete = basename($_GET['file']);
        $filePath = "mail/$folderToDelete/$fileToDelete";
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    // Redirect back to the folder view after deletion
    header("Location: index.php?folder=" . urlencode($_GET['folder']));
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
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f9;
            color: #333;
        }
        #header {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 15px 20px;
            text-align: center;
            font-size: 1.4em;
        }
        #header a {
            color: #ecf0f1;
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.8em;
        }
        #container {
            display: flex;
            height: calc(100vh - 60px);
        }
        #nav {
            width: 200px;
            background-color: #34495e;
            padding: 15px;
            box-sizing: border-box;
        }
        #nav h3 {
            color: #ecf0f1;
            margin-top: 0;
        }
        #nav a {
            display: block;
            padding: 8px 10px;
            margin: 8px 0;
            background-color: #3b5998;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
        }
        #nav a:hover {
            background-color: #2a4887;
        }
        #emailList {
            width: 300px;
            border-right: 1px solid #ddd;
            padding: 15px;
            overflow-y: auto;
            background-color: #ecf0f1;
            box-sizing: border-box;
        }
        #emailList h3 {
            margin-top: 0;
        }
        #emailList ul {
            list-style-type: none;
            padding: 0;
        }
        .email-item {
            padding: 10px;
            border-bottom: 1px solid #ccc;
            overflow: hidden;
        }
        .email-item a {
            color: #2c3e50;
            text-decoration: none;
            display: block;
        }
        .email-item:hover {
            background-color: #d0dce3;
        }
        .selected {
            background-color: #b0c4de;
        }
        #emailContent {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            background-color: #fff;
            box-sizing: border-box;
        }
        #emailContent h3 {
            margin-top: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid #bdc3c7;
            padding: 8px;
        }
        th {
            background-color: #2980b9;
            color: #fff;
        }
        .delete-button {
            display: inline-block;
            padding: 8px 12px;
            background-color: #e74c3c;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .delete-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div id="header">
        Email Web Client
        <a href="index.php?logout=1">Logout</a>
    </div>
    <div id="container">
        <!-- Left Navigation: Folder list -->
        <div id="nav">
            <h3>Folders</h3>
            <a href="index.php?folder=incoming">Inbox (Received)</a>
            <a href="index.php?folder=spam">Spam</a>
        </div>
        <!-- Middle: Email List -->
        <div id="emailList">
            <h3>Email List</h3>
            <?php if ($folder): ?>
                <ul>
                <?php foreach ($emailList as $emailFile): ?>
                    <li class="email-item <?php echo ($file === $emailFile) ? 'selected' : ''; ?>">
                        <a href="index.php?folder=<?php echo urlencode($folder); ?>&file=<?php echo urlencode($emailFile); ?>">
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
                            <th>Clave</th>
                            <th>Valor</th>
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
                <!-- Delete Email Button -->
                <a class="delete-button" href="index.php?action=delete&folder=<?php echo urlencode($folder); ?>&file=<?php echo urlencode($file); ?>" onclick="return confirm('¿Seguro que deseas eliminar este correo?');">Delete Email</a>
            <?php else: ?>
                <p>Select an email to view its content.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

