<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>TalentBridge</title>
    <style>
        /* Responsive styles */
        @media (max-width: 768px) {
            
            .hero-section {
                height: auto;
            }
            .hero-title {
                font-size: 2rem;
            }
            .hero-subtitle {
                font-size: 1rem;
            }
            .containerslide {
                padding: 10px;
                padding-top: 25rem;
            }
            .card-container-slide {
                flex-direction: column;
            }
            .cardslide {
                margin-bottom: 20px;
                overflow: hidden;
            }
            .textbody {
                margin-left: 1rem;
                margin-right: 1rem;
            }
            .image-container {
                width: 96%;
                height: 20%;
                margin-left: 0;
                padding-left: 10px;

            }
            
            .overlay-container {
                width: 90%;
                
            }
            .card-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="main-nav-bar">
            <div class="marginbar">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-cucumber" width="68" height="68" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <!-- SVG path data -->
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
            </div>
            <nav class="main-nav">
                <a href="#inicio">Inicio</a>
                <a href="homevacantes.php">Vacantes</a>
                <a href="#testimonios">Empresas</a>
                <a href="/Outsourcing/en/index.php">English</a>
                <a href="/Outsourcing/es/index.php">Español</a>
            </nav>
        </div>

        <!-- SLIDER -->
        <section>
            <div class="hero-section slider-box">
                <ul>
                    <li><img src="img/slider5.jpeg" alt="No image" class="hero-image"></li>
                    <li><img src="img/slider6.jpeg" alt="No image" class="hero-image"></li>
                    <li><img src="img/slider2.png" alt="No image" class="hero-image"></li>
                    <li><img src="img/slider3.png" alt="No image" class="hero-image"></li>
                </ul>
                <div class="hero-overlay">
                    <div class="containerslide">
                        <div class="card-container-slide">
                            <div class="cardslide">
                                <div class="card-content-slide cardbackground">
                                    <h2 class="card-title-slide slide-title-left">¡Únete a esta increíble comunidad!</h2>
                                    <p>Conéctate con el talento que necesitas, cuando lo necesites.</p>
                                    <a href="login.php" class="slide-button">Iniciar sesión</a>
                                    <a href="registro.php" class="slide-button">Registrarse</a>
                                </div>
                            </div>
                            <div class="cardslide">
                                <div class="card-content">
                                    <h2 class="slide-title-right">TalentBridge</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section>
            <div class="footer-bar">
                <p class="spawntext">Encuentra el trabajo perfecto a tus estándares</p>
            </div>
        </section>
    </header>

    <main>
        <section>
            <div class="container">
                <div class="design-section">
                    <h1 class="textcolor">¿Quiénes somos?</h1>
                    <p class="textbody textcolor">
                        TalentBridge se especializa en ofrecer servicios de subcontratación que ayudan a las empresas a optimizar sus operaciones y reducir costos. 
                        Nuestro enfoque está en proporcionar soluciones personalizadas en diversas áreas clave.
                    </p>
                </div>
            </div>
            <div class="image-container">
                <img src="img/trabajo1.png" alt="Imagen de fondo" class="background-image">
                <div class="overlay-container">
                    <h1>Explora las últimas actualizaciones de trabajo</h1>
                    <p>Descubre increíbles ofertas exclusivas para ti.</p>
                    <a href="registro.php" class="slide-button">Empezar ahora</a>
                </div>
            </div>
            <div class="container">
                <div class="design-section containercolor">
                    <h1 class="textcolor">Descubre lo que tenemos para ti</h1>
                    <p class="textbody textcolor">
                        Nos comprometemos a impulsar tu carrera y ofrecerte las mejores oportunidades. Ya sea que estés buscando una nueva vacante,
                        una certificación que te diferencie en el mercado laboral o reseñas que avalen nuestro compromiso con la calidad.
                    </p>
                </div>
            </div>     
        </section>
        <section>
            <div class="containertext">
                <h1>¿Qué esperas para unirte?</h1>
                <div class="promo-content">
                    <img src="img/body1.png" alt="" class="promo-image">
                    <div class="promo-text">
                        <h2>Desarrolla tu perfil profesional</h2>
                        <p>
                            Incrementa tus habilidades con nuestras certificaciones y programas de formación diseñados para mejorar tus habilidades y conocimientos.
                            Invierte en tu futuro y conviértete en un candidato más competitivo en el mercado laboral.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- SERVICIOS QUE OFRECEMOS -->
    </main>

    <footer class="footer-down">
        <p>Todos los derechos reservados a TalentBridge</p>
        <p>Cualquier duda o incoveniente, contactarse a 664 358 0968</p>
    </footer>
</body>
</html>