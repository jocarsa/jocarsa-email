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
      @import url('https://static.jocarsa.com/fuentes/ubuntu-font-family-0.83/ubuntu.css');
         body { font-family: Ubuntu, sans-serif; background-color: #f7f7f7; }
         .login-container { width: 320px; margin: 120px auto; padding: 20px; background: #fff; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
         input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 3px; }
         input[type="submit"] { width: 100%; padding: 10px; background: #4CAF50; color: #fff; border: none; border-radius: 3px; cursor: pointer; }
         input[type="submit"]:hover { background: #45a049; }
         .error { color: red; text-align: center; }
         input{
        	box-sizing:border-box;
        }
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

// --- Email Deletion Action ---
// (Keep your existing email deletion code here)
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

// --- SPAM WORDS MANAGEMENT CRUD ---
// If the URL includes manage=spamwords, process spam word actions.
if (isset($_GET['manage']) && $_GET['manage'] === 'spamwords') {
    // Process only add, delete, and update actions. (The 'edit' action is only for displaying the inline form.)
    if (isset($_REQUEST['actionSpam']) && $_REQUEST['actionSpam'] !== 'editSpam') {
        $actionSpam = $_REQUEST['actionSpam'];
        $spamFilePath = 'spamfilter.txt';
        $spamWords = [];
        if (file_exists($spamFilePath)) {
            $spamWords = file($spamFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
        switch ($actionSpam) {
            case 'addSpam':
                if (isset($_POST['newSpam'])) {
                    $newSpam = trim($_POST['newSpam']);
                    if ($newSpam !== '' && !in_array($newSpam, $spamWords)) {
                        $spamWords[] = $newSpam;
                        file_put_contents($spamFilePath, implode("\n", $spamWords) . "\n");
                    }
                }
                break;
            case 'deleteSpam':
                if (isset($_GET['index'])) {
                    $index = intval($_GET['index']);
                    if (isset($spamWords[$index])) {
                        unset($spamWords[$index]);
                        $spamWords = array_values($spamWords);
                        file_put_contents($spamFilePath, implode("\n", $spamWords) . "\n");
                    }
                }
                break;
            case 'updateSpam':
                if (isset($_POST['index']) && isset($_POST['updatedSpam'])) {
                    $index = intval($_POST['index']);
                    $updatedSpam = trim($_POST['updatedSpam']);
                    if (isset($spamWords[$index]) && $updatedSpam !== '') {
                        $spamWords[$index] = $updatedSpam;
                        file_put_contents($spamFilePath, implode("\n", $spamWords) . "\n");
                    }
                }
                break;
        }
        // Redirect to clear form submissions and avoid reprocessing
        header("Location: index.php?manage=spamwords");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>jocarsa | email</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
    <style>
    @import url('https://static.jocarsa.com/fuentes/ubuntu-font-family-0.83/ubuntu.css');
    /* General layout styling */
    body {
        margin: 0;
        font-family: Ubuntu, sans-serif;
        background-color: #f4f7f9;
        color: #333;
    }
    /* Header styles */
    #header {
        background-color: #34495e;
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
    /* Navigation styles (preserving current color scheme) */
    #nav {
        width: 220px;
        background-color: #34495e;
        padding: 20px;
        box-sizing: border-box;
        padding-right:0px;
    }
    #nav h3 {
        color: #ecf0f1;
        margin-top: 0;
        margin-bottom: 15px;
    }
    #nav a {
        display: block;
        padding: 12px 15px;
        margin: 8px 0;
        background-color: #3b5998;
        color: #fff;
        border-radius: 4px 0px 0px 4px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }
    #nav a:hover {
        background-color: #2a4887;
    }
    #nav .activo {
        background-color: #ecf0f1;
        color: #34495e;
    }
    /* Container layout for email client */
    #container {
        display: flex;
        height: calc(100vh - 0px);
    }
    /* Email list styling */
    #emailList {
        width: 320px;
        padding: 20px;
        overflow-y: auto;
        background-color: #ecf0f1;
        box-sizing: border-box;
        padding-right:0px;
    }
    #emailList h3 {
        margin-top: 0;
        margin-bottom: 15px;
    }
    #emailList ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .email-item {
        padding: 12px;
        border-bottom: 1px solid #ccc;
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
        background-color: #ffffff;
        position: relative;
    }
    /* Email content styling */
    #emailContent {
        flex-grow: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: #fff;
        box-sizing: border-box;
    }
    #emailContent h3 {
        margin-top: 0;
        margin-bottom: 15px;
    }
    /* Enhanced table styles for JSON content presentation */
    #emailContent table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    #emailContent th, 
    #emailContent td {
        border: 1px solid #ddd;
        padding: 10px 12px;
        text-align: left;
        vertical-align: top;
    }
    #emailContent th {
        background-color: #2980b9;
        color: #fff;
    }
    #emailContent tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    /* Delete button styling */
    .delete-button {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 12px;
        background-color: #e74c3c;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s ease;
    }
    .delete-button:hover {
        background-color: #c0392b;
    }
    /* Spam management section styling */
    #spamManagement {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }
    #spamManagement h3 {
        margin-top: 0;
        margin-bottom: 15px;
    }
    #spamManagement table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
    }
    #spamManagement table, 
    #spamManagement th, 
    #spamManagement td {
        border: 1px solid #bdc3c7;
        padding: 10px;
    }
    #spamManagement th {
        background-color: #2980b9;
        color: #fff;
    }
    .action-link {
        margin-right: 10px;
        text-decoration: none;
        color: #2980b9;
    }
    .action-link:hover {
        text-decoration: underline;
    }
    .form-inline input[type="text"] {
        padding: 6px;
        margin-right: 6px;
    }
    .form-inline input[type="submit"] {
        padding: 6px 10px;
    }
    input {
        box-sizing: border-box;
    }
</style>

</head>
<body>
 
    <div id="container">
        <!-- Left Navigation: Folder list and Spam Words link -->
        <div id="nav">
        
            <h3>jocarsa | email</h3>
            <a href="index.php?folder=incoming" <?php echo (@$_GET['folder'] == 'incoming') ? 'class="activo"' : ''; ?>>Recibidos</a>
				<a href="index.php?folder=spam" <?php echo (@$_GET['folder'] == 'spam') ? 'class="activo"' : ''; ?>>Spam</a>
				<a href="index.php?manage=spamwords" <?php echo (isset($_GET['manage']) && @$_GET['manage'] == 'spamwords') ? 'class="activo"' : ''; ?>>Palabras de Spam</a>
				<a href="index.php?logout=1">Cerrar sesión</a>
        </div>

        <?php if (isset($_GET['manage']) && $_GET['manage'] === 'spamwords'): 
            // Load spam words for display
            $spamFilePath = 'spamfilter.txt';
            $spamWords = [];
            if (file_exists($spamFilePath)) {
                $spamWords = file($spamFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
            // Check if an edit request was made via GET
            $editIndex = null;
            if (isset($_GET['actionSpam']) && $_GET['actionSpam'] === 'editSpam' && isset($_GET['index'])) {
                $editIndex = intval($_GET['index']);
            }
        ?>
        <!-- SPAM WORDS MANAGEMENT INTERFACE -->
        <div id="spamManagement">
            <h3>Gestión de Spam</h3>
            <!-- Form to add a new spam word -->
            <form method="post" action="index.php?manage=spamwords&actionSpam=addSpam" class="form-inline">
                <input type="text" name="newSpam" placeholder="Nuevo spam word" required>
                <input type="submit" value="Agregar">
            </form>
            <!-- Table displaying current spam words -->
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Palabra de Spam</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($spamWords)): ?>
                        <tr>
                            <td colspan="3">No hay spam words definidos.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($spamWords as $index => $word): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <?php if ($editIndex === $index): ?>
                                    <!-- Inline edit form -->
                                    <form method="post" action="index.php?manage=spamwords&actionSpam=updateSpam" class="form-inline">
                                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                                        <input type="text" name="updatedSpam" value="<?php echo htmlspecialchars($word); ?>" required>
                                        <input type="submit" value="Guardar">
                                    </form>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($word); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($editIndex !== $index): ?>
                                    <a class="action-link" href="index.php?manage=spamwords&actionSpam=editSpam&index=<?php echo $index; ?>">Editar</a>
                                    <a class="action-link" href="index.php?manage=spamwords&actionSpam=deleteSpam&index=<?php echo $index; ?>" onclick="return confirm('¿Seguro que deseas eliminar esta palabra?');">Eliminar</a>
                                <?php else: ?>
                                    <a class="action-link" href="index.php?manage=spamwords" title="Cancelar edición">Cancelar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php else: 
            // --- EMAIL CLIENT INTERFACE (existing) ---
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
                $filePath = $folderPath . '/' . basename($file);
                if (file_exists($filePath)) {
                    $jsonContent = file_get_contents($filePath);
                    $emailData = json_decode($jsonContent, true);
                }
            }

            // If a folder is selected, get the list of email files
            $emailList = [];
            if ($folder && is_dir($folderPath)) {
                $files = glob($folderPath . '/*.json');
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                foreach ($files as $f) {
                    $emailList[] = basename($f);
                }
            }
        ?>
        <!-- Email Client Interface -->
        <div id="emailList">
            <h3>Lista de correos</h3>
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
                <p>Selecciona una capeta de la columna de la izquierda</p>
            <?php endif; ?>
        </div>
        <div id="emailContent">
            <h3>Contenido del correo</h3>
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
                <a class="delete-button" href="index.php?action=delete&folder=<?php echo urlencode($folder); ?>&file=<?php echo urlencode($file); ?>" onclick="return confirm('¿Seguro que deseas eliminar este correo?');">Delete Email</a>
            <?php else: ?>
                <p>Selecciona un correo para ver su contenido</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

