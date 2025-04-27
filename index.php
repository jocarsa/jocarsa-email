<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>jocarsa | paleturquoise Control Panel</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
    <style>
    @import url('https://static.jocarsa.com/fuentes/ubuntu-font-family-0.83/ubuntu.css');

    body {
        margin: 0;
        font-family: Ubuntu, sans-serif;
        background-color: #AFEEEE; /* Paleturquoise background */
        color: #333;
    }
    #container {
        display: flex;
        height: 100vh;
    }
    /* Column widths: 25%, 25%, 50% */
    #nav {
        flex: 0 0 15%;
        overflow-y: auto;
        box-sizing: border-box;
        padding: 20px;
        background-color: #354747;
        padding-right: 0px;
    }
    #emailList {
        flex: 0 0 15%;
        overflow-y: auto;
        box-sizing: border-box;
        padding: 20px;
        background-color: #698f8f;
        padding-right: 0px;
    }
    #content {
        flex: 0 0 70%;
        overflow-y: auto;
        box-sizing: border-box;
        padding: 20px;
        background-color: #F0FFFF;
    }
    /* Navigation Column Styles */
    #nav h3 {
        margin: 0 0 15px;
        background: #F0FFFF;
        color: black;
        width: calc(100% - 60px);
        border-radius: 50px;
        padding: 10px;
        text-align: center;
        box-shadow: -30px 0px 30px rgba(0,0,0,0.3);
    }
    #nav a {
        display: block;
        padding: 12px 15px;
        margin: 8px 0;
        color: #fff;
        border-radius: 34px 0 0 34px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }
    #nav a:hover,
    #nav a.active {
        background-color: #698f8f;
    }

    /* Email List Styles */
    #emailList h3 {
        margin: 0 0 15px;
        color: #fff;
    }
    #emailList ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    #emailList li {
        padding: 12px;
        border-radius: 34px 0 0 34px;
    }
    #emailList li:hover {
        background-color: #D0F0F8;
    }
    #emailList li.selected {
        background-color: #F0FFFF;
        box-shadow: -10px 0px 10px rgba(0,0,0,0.3);
    }
    #emailList li a {
        color: #004C4C;
        text-decoration: none;
        display: block;
    }

    /* Content Column Styles */
    #content h3 {
        margin: 0 0 15px;
        color: #458B74; /* Paleturquoise color */
    }
    .email-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .email-header h3 {
        margin: 0;
    }
    .email-header button {
        padding: 6px 12px;
        background-color: #f44336;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .email-header button:hover {
        background-color: #c62828;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        border: 1px solid #B0E0E6;
        padding: 10px 12px;
        text-align: left;
        vertical-align: top;
    }
    th {
        background-color: #698f8f;
        color: #fff;
    }
    tr:nth-child(even) {
        background-color: rgb(250,250,250);
    }
    button {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 12px;
        background-color: #698f8f;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #20B2AA;
    }
    form input[type="text"] {
        padding: 6px;
        margin-right: 6px;
        border: 1px solid #B0E0E6;
        border-radius: 3px;
    }
    form button {
        padding: 6px 10px;
        border-radius: 3px;
    }
    </style>
</head>
<body>
    <div id="container">
        <div id="nav">
            <h3>paleturquoise</h3>
            <a href="#" data-action="folder" data-folder="incoming" class="active">Recibidos</a>
            <a href="#" data-action="folder" data-folder="spam">Spam</a>
            <a href="#" data-action="spamwords">Palabras de Spam</a>
            <a href="?logout=1">Cerrar sesión</a>
        </div>
        <div id="emailList">
            <h3 id="listTitle">Recibidos</h3>
            <ul id="listItems"></ul>
        </div>
        <div id="content">
            <div class="email-header">
                <h3 id="contentTitle">Contenido</h3>
                <button id="deleteEmailButton">Eliminar</button>
            </div>
            <div id="contentBody"></div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const navLinks = document.querySelectorAll('#nav a[data-action]');
        const listTitle = document.getElementById('listTitle');
        const listItems = document.getElementById('listItems');
        const contentTitle = document.getElementById('contentTitle');
        const contentBody = document.getElementById('contentBody');
        const deleteEmailButton = document.getElementById('deleteEmailButton');
        let currentFolder = 'incoming';
        let currentFile = null;

        loadFolder('incoming');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                clearAll();
                const action = link.dataset.action;
                if (action === 'folder') {
                    currentFolder = link.dataset.folder;
                    loadFolder(currentFolder);
                } else if (action === 'spamwords') {
                    loadSpamwords();
                }
            });
        });

        deleteEmailButton.addEventListener('click', () => {
            if (currentFile) {
                fetch(`api/emails.php?folder=${currentFolder}&file=${currentFile}`, { method: 'DELETE' })
                    .then(res => res.json())
                    .then(r => {
                        // 1. Encontrar el <li> actualmente seleccionado
                        const selectedLi = document.querySelector('#emailList li.selected');
                        if (!selectedLi) return;

                        // 2. Calcular cuál será el siguiente a abrir:
                        //    primero intento con el siguiente hermano, si no existe uso el anterior
                        const nextLi = selectedLi.nextElementSibling || selectedLi.previousElementSibling;

                        // 3. Eliminar el elemento seleccionado
                        selectedLi.remove();

                        if (nextLi) {
                            // 4a. Si hay un siguiente <li>, disparar su click para cargarlo
                            nextLi.querySelector('a').click();
                        } else {
                            // 4b. Si no hay más correos, limpiar el área de contenido
                            contentBody.innerHTML = '';
                            contentTitle.textContent = 'Contenido';
                            currentFile = null;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error al eliminar el correo.');
                    });
            }
        });

        function clearAll() {
            listItems.innerHTML = '';
            contentBody.innerHTML = '';
            contentTitle.textContent = 'Contenido';
            currentFile = null;
        }

        function loadFolder(folder) {
            listTitle.textContent = folder === 'incoming' ? 'Recibidos' : 'Spam';
            fetch(`api/emails.php?folder=${folder}`)
                .then(res => res.json())
                .then(files => files.forEach(file => {
                    const li = document.createElement('li');
                    const link = document.createElement('a');
                    link.textContent = file.replace('.json','');
                    li.dataset.folder = folder;
                    li.dataset.file = file;
                    li.appendChild(link);
                    li.addEventListener('click', onEmailClick);
                    listItems.appendChild(li);
                }));
        }

        function onEmailClick() {
            [...listItems.children].forEach(li => li.classList.remove('selected'));
            this.classList.add('selected');
            const { folder, file } = this.dataset;
            contentTitle.textContent = file.replace('.json','');
            currentFile = file;
            fetch(`api/emails.php?folder=${folder}&file=${file}`)
                .then(res => res.json())
                .then(data => renderEmail({ ...data, _folder: folder, _file: file }));
        }

        function renderEmail(data) {
            contentBody.innerHTML = '';
            const table = document.createElement('table');
            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            ['Clave','Valor'].forEach(text => {
                const th = document.createElement('th'); th.textContent = text; headerRow.appendChild(th);
            });
            thead.appendChild(headerRow); table.appendChild(thead);
            const tbody = document.createElement('tbody');
            Object.entries(data).filter(([k]) => !k.startsWith('_')).forEach(([key,value]) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${key}</td><td>${value}</td>`;
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            contentBody.appendChild(table);
        }

        function loadSpamwords() {
            listTitle.textContent = 'Palabras de Spam';
            fetch('api/spamwords.php')
                .then(res => res.json())
                .then(words => {
                    listItems.innerHTML = '';
                    words.forEach((w,i) => {
                        const li = document.createElement('li'); li.textContent = w; li.dataset.index = i;
                        li.addEventListener('click', onSpamwordClick);
                        listItems.appendChild(li);
                    });
                    showAddSpamForm();
                });
        }
        function onSpamwordClick() {
            [...listItems.children].forEach(li => li.classList.remove('selected'));
            this.classList.add('selected');
            const i = this.dataset.index;
            contentTitle.textContent = 'Editar Palabra'; contentBody.innerHTML = '';
            fetch(`api/spamwords.php?index=${i}`)
                .then(res => res.json()).then(word => showEditSpamForm(i, word));
        }
        function showAddSpamForm() {
            contentTitle.textContent = 'Agregar Palabra'; contentBody.innerHTML = '';
            const form = document.createElement('form');
            form.innerHTML = '<input type="text" name="newSpam" placeholder="Nueva palabra" required> <button type="submit">Agregar</button>';
            form.addEventListener('submit', e => {
                e.preventDefault();
                const w = form.newSpam.value.trim();
                fetch('api/spamwords.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({word: w}) })
                    .then(res => res.json()).then(r => { alert(r.message); loadSpamwords(); });
            });
            contentBody.appendChild(form);
        }
        function showEditSpamForm(i, w) {
            const form = document.createElement('form');
            form.innerHTML = `<input type="text" name="updatedSpam" value="${w}" required> <button type="submit">Guardar</button> <button type="button" id="del">Eliminar</button>`;
            form.addEventListener('submit', e => {
                e.preventDefault();
                const nw = form.updatedSpam.value.trim();
                fetch(`api/spamwords.php?index=${i}`, { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({word: nw}) })
                    .then(res => res.json()).then(r => { alert(r.message); loadSpamwords(); });
            });
            form.querySelector('#del').addEventListener('click', () => {
                if (confirm('¿Eliminar esta palabra?')) {
                    fetch(`api/spamwords.php?index=${i}`, { method:'DELETE' })
                        .then(res => res.json()).then(r => { alert(r.message); loadSpamwords(); });
                }
            });
            contentBody.innerHTML = '';
            contentBody.appendChild(form);
        }
    });
    </script>
</body>
</html>

