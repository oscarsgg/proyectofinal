<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="css/prospectProfile.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Enlaza jQuery -->
    <title>Document</title>


</head>

<body>
    <section class="sidebarfool">
        <div class="container">
            <div class="sidebar active">
                <div class="menu-btn">
                    <i class="ph-bold ph-caret-left"></i>
                </div>
                <div class="head">
                    <div class="user-img">
                        <img src="img/soto.jpeg" alt="No photo">
                    </div>
                    <div class="user-details">
                        <p class="title">Administrador del sistema</p>
                        <p class="name">Pedro Sanchez</p>
                    </div>
                </div>
                <div class="nav">
                    <div class="menu">
                        <p class="title">Admin</p>
                        <ul>
                            <li class="">
                                <a href="#">
                                    <i class="icon ph-bold ph-house-simple"></i>
                                    <span class="text">Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="icon ph-bold ph-user"></i>
                                    <span class="text">Administración</span>
                                    <i class="arrow ph-bold ph-caret-down"></i>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">
                                            <span class="text">Registrar admin</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="icon ph-bold ph-chart-bar"></i>
                                    <span class="text"> Usuarios</span>
                                    <i class="arrow ph-bold ph-caret-down"></i>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">
                                            <span class="text">Buscar usuarios</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="icon ph-bold ph-user"></i>
                                    <span class="text">Analíticas</span>
                                    <i class="arrow ph-bold ph-caret-down"></i>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">
                                            <span class="text">Reportes</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="menu">
                        <p class="title">Ajustes</p>
                        <ul>
                            <li class="">
                                <a href="#">
                                    <i class="icon ph-bold ph-gear"></i>
                                    <span class="text">Almefiguración</span>
                                </a>
                            </li>
                    </div>
                    <div class="menu">
                        <p class="title"></p>
                        <ul>
                            <li class="">
                                <a href="#">
                                    <i class="icon ph-bold ph-gear"></i>
                                    <span class="text">Cerrar sesión</span>
                                </a>
                            </li>
                    </div>
                    <script src="barra.js"></script> <!-- Enlaza tu archivo JS aquí -->
    </section>

</body>

<main>
    <div class="containeres">
        <main>
            <div class="profile-header">
                <img src="img/soto.jpeg" alt="Foto de perfil" class="profile-image" id="profile-image">
                <div class="profile-info">
                    <div>
                        <h1><span class="editable" id="name">Oscar Gael Soto García</span></h1>
                        <p class="editable" id="job-title">Contador Publico</p>
                        <p><span class="editable" id="work-hours">01/01/2005</span> · <span class="editable"
                                id="email">gael.garcia@gmail.com</span></p>
                    </div>
                </div>
            </div>

            <section class="tasks-section">
                <h2>Información</h2>
                <div class="tabs">
                    <div class="tab active">Habilidades</div>
                </div>
                <div class="task-list" id="task-list">
                    <div class="task-">
                        <section>
                            <p class="editable multi-line" id="about">Normalmente trabajo en turno matutino, cualquier
                                duda no dudes en contactarme!</p>
                        </section>
                    </div>
                </div>
            </section>
        </main>

        <aside class="sbar">
            <section>
                <h2>Acerca de mí</h2>
                <p class="editable multi-line" id="about-sidebar">Normalmente trabajo en turno matutino, cualquier duda
                    no dudes en contactarme!</p>
            </section>
            <section>
                <h2>Grados</h2>
                <div class="teams-list" id="teams-list">
                    <div class="team-item">
                        <div class="team-icon"></div>
                        <span class="editable">Licenciatura en contaduría</span>
                    </div>
                    <div class="team-item">
                        <div class="team-icon"></div>
                        <span class="editable">Maestría en finanzas</span>
                    </div>
                    <div class="team-item">
                        <div class="team-icon"></div>
                        <span class="editable">Congresista en Palacio NF</span>
                    </div>
                    <div class="team-item">
                        <div class="team-icon"></div>
                        <span class="editable">Doctorado en finanzas de valores</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <button id="edit-all" class="edit-button" style="position: fixed; bottom: 20px; right: 20px;">Editar Todo</button>
</main>

</html>