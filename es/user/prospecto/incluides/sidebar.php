<?php
    // Obtener los datos del prospecto
    $query = "SELECT p.*, u.correo, u.foto FROM prospecto AS p 
            INNER JOIN usuario AS u ON p.usuario = u.numero 
            WHERE u.numero = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prospecto = $result->fetch_assoc();

    if (!$prospecto) {
        die("No se encontró el perfil del prospecto.");
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Enlaza jQuery -->
    <!-- Para sidebar prospecto <i class="ph ph-lego-smiley"></i> -->
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: "Inter", sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #efeff6;
        }

        .container {
            width: 100%;
            grid-template-columns: 1fr;
        }

        .right {
            background-color: #4CAF50;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 24px;
        }

        .left {
            background-color: white;
            display: flex;
            justify-content: center;
            width: 100%;
            align-items: center;
            color: white;
            font-size: 24px;
        }

        .sidebar {
            position: fixed;
            left: 0;
            /* Asegura que el sidebar esté alineado completamente a la izquierda */
            top: 0;
            /* Posiciona el sidebar desde el inicio de la pantalla */
            width: 330px;
            height: 100vh;
            /* Ocupa toda la altura de la pantalla */
            display: flex;
            flex-direction: column;
            gap: 10px;
            background-color: #000;
            padding: 24px;
            transition: all 0.3s;
            z-index: 1000;
            /* Asegura que el sidebar esté encima de otros elementos */
        }

        .sidebar .head {
            display: flex;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f6f6f6;
        }

        .user-img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
        }

        .user-img img {
            width: 100%;
            object-fit: cover;
        }

        .user-details .title,
        .menu .title {
            font-size: 10px;
            font-weight: 500;
            color: white;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .user-details .name {
            font-size: 14px;
            font-weight: 500;
            color: white;
        }

        .nav {
            flex: 1;
        }

        .menu ul li {
            position: relative;
            list-style: none;
            margin-bottom: 5px;
        }

        .menu ul li a {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: white;
            text-decoration: none;
            padding: 12px 8px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .menu ul li>a:hover,
        .menu ul li.active>a {
            color: #000;
            background-color: #f6f6f6;
        }

        .menu ul li .icon {
            font-size: 20px;
        }

        .menu ul li .text {
            flex: 1;
        }

        .menu ul li .arrow {
            font-size: 14px;
            transition: all 0.3s;
        }

        menu ul li.active .arrow {
            transform: rotate(180deg);
        }

        .menu .sub-menu {
            display: none;
            margin-left: 20px;
            padding-left: 20px;
            padding-top: 5px;
            border-left: 1px solid #f6f6f6;
        }

        .menu .sub-menu li a {
            padding: 10px 8px;
            font-size: 12px;
        }

        .menu:not(:last-child) {
            padding-bottom: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #fff;
        }

        .menu-btn {
            position: absolute;
            right: -14px;
            top: 3.5%;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: black;
            border: 2px solid #000;
            background-color: white;
        }

        .menu-btn:hover i {
            color: gray;
        }

        .menu-btn i {
            transition: all 0.3s;
        }

        .sidebar.active {
            width: 92px;
        }

        .sidebar.active .menu-btn i {
            transform: rotate(180deg);
        }

        .sidebar.active .user-details {
            display: none;
        }

        .sidebar.active .menu .title {
            text-align: center;
        }

        .sidebar.active .menu ul li .arrow {
            display: none;
        }

        .sidebar.active .menu>ul>li>a {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar.active .menu>ul>li>a .text {
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            padding: 10px;
            border-radius: 4px;
            color: white;
            background-color: black;
            opacity: 0;
            visibility: hidden;
        }

        .sidebar.active .menu>ul>li>a .text::after {
            content: "";
            position: absolute;
            left: -5px;
            top: 20%;
            width: 20px;
            height: 20px;
            border-radius: 2px;
            background-color: black;
            transform: rotate(45deg);
            z-index: -1;
        }

        .sidebar.active .menu>ul>li>a:hover .text {
            left: 50px;
            opacity: 1;
            visibility: visible;
        }

        .sidebar.active .menu .sub-menu {
            position: absolute;
            top: 0;
            left: 50px;
            border-radius: 20px;
            padding: 10px 20px;
            border: 1px solid #000;
            background-color: black;
            box-shadow: 0px 10px 8px rgba(0, 0, 0, 0.1);
        }

        /* RIGHT SIDE */

        .containeres {
            height: 100vh;
            width: 100%;
            background-color: #f6f6f6;
            display: flex;
            flex-direction: column;
        }

        .content {
            margin-left: 350px;
        }

        .squarecontent {
            background-color: white;
            width: 90%;
            height: 430px;
            margin-top: 40px;
            border-radius: 40px;
        }

        /* Nuevo estilo para la barra superior */
        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: #000;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            z-index: 1001;
        }

        .top-bar a {
            color: #fff;
            text-decoration: none;
        }

        .top-bar .logo {
            display: flex;
            align-items: center;
        }

        .top-bar .logo svg {
            margin-right: 10px;
        }

        .top-bar .logo-text {
            font-size: 20px;
            font-weight: bold;
        }

        /* Ajuste del sidebar para que no se superponga con la barra superior */
        .sidebar {
            top: 60px;
            height: calc(100vh - 60px);
        }

        .content {
            margin-left: 350px;
            margin-top: 60px; /* Añadido para dar espacio a la barra superior */
        }

        /* Responsivo */
        @media (max-width: 768px) {

            /* Ocultar la sidebar inicialmente */
            .sidebar.active {
                width: 0;
                overflow: hidden;
                padding: 0;
            }

            .sidebar {
                width: 250px;
                height: 100vh;
                padding: 24px;
            }

            .content {
                margin-left: 0;
            }

            /* Ajustar la posición del botón de menú */
            .menu-btn {
                left: 235px;
            }

            .menu-btn i {
                font-size: 24px;
                left: 20px;
            }

            .sidebar.active .menu-btn {
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1100;

            }

            .sidebar.active .menu-btn {
                top: 80px; /* Ajustado para que no se superponga con la barra superior */
            }
        }
        
    </style>
</head>

<body>
    <!-- Nueva barra superior -->
    <div class="top-bar">
        <a href="">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-cucumber" width="40" height="40" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M20 10.99c-.01 5.52 -4.48 10 -10 10.01v-2.26l-.01 -.01c-4.28 -1.11 -6.86 -5.47 -5.76 -9.75a8 8 0 0 1 9.74 -5.76c3.53 .91 6.03 4.13 6.03 7.78v-.01z" />
                    <path d="M10.5 8l-.5 -1" />
                    <path d="M13.5 14l.5 1" />
                    <path d="M9 12.5l-1 .5" />
                    <path d="M11 14l-.5 1" />
                    <path d="M13 8l.5 -1" />
                    <path d="M16 12.5l-1 -.5" />
                    <path d="M9 10l-1 -.5" />
                </svg>
                <span class="logo-text">TalentBridge</span>
            </div>
        
        </a>
        
        <!-- <a href="#" style="color: #fff; text-decoration: none;">TalentBridge</a> -->
    </div>
    <section class="sidebarfool">
        <div>
            <div class="sidebar active">
                <div class="menu-btn">
                    <i class="ph-bold ph-caret-left"></i>
                </div>
                <div class="head">
                    <div class="user-img">
                        <img
                            src="<?php echo $prospecto['foto'] ? '../../../../Outsourcing/img/' . htmlspecialchars($prospecto['foto']) : 'img/default.jpg'; ?>"
                            alt="Foto de perfil">
                    </div>
                    <div class="user-details">
                        <p class="title">Experto en almejas</p>
                        <p class="name">Bienvenido</p>
                    </div>
                </div>
                <div class="nav">
                    <div class="menu">
                        <p class="title">Main</p>
                        <ul>
                            <li class="">
                                <a href="index.php">
                                    <i class="icon ph-bold ph-house-simple"></i>
                                    <span class="text">Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="profile.php">
                                    <i class="icon ph-bold ph-lego-smiley"></i>
                                    <span class="text">Ver perfil</span>
                                </a>
                            </li>
                            <li>
                                <a href="buscar_vacantes.php">
                                    <i class="icon ph-bold ph-read-cv-logo"></i>
                                    <span class="text">Buscar vacantes</span>
                                    <i class="arrow ph-bold ph-caret-down"></i>
                                </a>
                            </li>
                            <li>
                                <a href="gestionar_contratos.php">
                                    <i class="icon ph-bold ph-scroll"></i>
                                    <span class="text">Gestionar contratos</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- <div class="menu">
                        <p class="title">Ajustes</p>
                        <ul>
                            <li class="">
                                <a href="#">
                                    <i class="icon ph-bold ph-gear"></i>
                                    <span class="text">Almefiguración</span>
                                </a>
                            </li>
                    </div> -->
                    <div class="menu">
                        <p class="title"></p>
                        <ul>
                            <li class="">
                                <a href="/Outsourcing/es/logout.php">
                                    <i class="icon ph-bold ph-gear"></i>
                                    <span class="text">Cerrar sesión</span>
                                </a>
                            </li>
                    </div>
    </section>

    <script>
        $(".menu > ul > li").click(function (e) {
            // Remove active class from already active siblings
            $(this).siblings().removeClass("active");
            // Add active class to the clicked item
            $(this).toggleClass("active");
            // Open submenu
            $(this).find("ul").slideToggle();
            // Open menu and slide up
            $(this).siblings().find("ul").slideUp();
            // remove active class of submenu

        });

        $(".menu-btn").click(function () {
            $(".sidebar").toggleClass("active");
        })

        $(document).click(function (e) {
            // Verifica si el clic fue fuera del sidebar y el menú
            if (!$(e.target).closest('.sidebar, .menu > ul > li').length) {
                // Si es fuera, desactiva los elementos del menú
                $(".menu > ul > li").removeClass("active");
                $(".menu > ul > li ul").slideUp(); // Cierra cualquier submenu

            }
        });
    </script>
</body>

</html>