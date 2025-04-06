<?php
// header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEMP CLOUD STORAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --bg: #121212;
            --text: #f1f1f1;
            --primary: #4361ee;
            --muted: #aaaaaa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: #1e1e1e;
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #2c2c2c;
        }

        .logo {
            font-size: 1.8rem;
            color: var(--primary);
        }

        h1 {
            font-size: 1.4rem;
            margin: 0.5rem 0 0.2rem;
        }

        .tagline {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .container {
            flex: 1;
            width: 100%;
            max-width: 800px;
            margin: 1.5rem auto;
            padding: 0 ;
        }

        @media (max-width: 480px) {
            .logo {
                font-size: 1.5rem;
            }

            h1 {
                font-size: 1.2rem;
            }

            .tagline {
                font-size: 0.8rem;
            }

            header {
                padding: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo"><i class="fas fa-cloud-upload-alt"></i></div>
        <h1>TEMP CLOUD STORAGE</h1>
        <p class="tagline">Upload • Share • Expires</p>
    </header>

    <div class="container">
