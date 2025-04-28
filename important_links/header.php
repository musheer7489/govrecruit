<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title;?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="../<?=COMPANY_LOGO_URL;?>" type="image/x-icon">
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <div class="logo-text">
                    <h1><?=COMPANY_NAME;?></h1>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../">Home</a></li>
                    <li><a href="../login">Login</a></li>
                    <li><a href="help">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>