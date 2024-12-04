<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/vacantes.css">
    <script src="siderbar.js"></script>
    <title>Vacantes</title>
</head>



<header class="header">
    <div class="main-nav-bar">
        <div class="marginbar">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-cucumber" width="68"
                height="68" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round"
                stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path
                    d="M20 10.99c-.01 5.52 -4.48 10 -10 10.01v-2.26l-.01 -.01c-4.28 -1.11 -6.86 -5.47 -5.76 -9.75a8 8 0 0 1 9.74 -5.76c3.53 .91 6.03 4.13 6.03 7.78v-.01z" />
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
            <a href="#caracteristicas">Vacantes</a>
            <a href="#testimonios">Empresas</a>
            <a href="#contacto">Certificaciones</a>
            <a href="#resenas">Reseñas</a>
        </nav>
    </div>
</header>
<body>
    <section>
        <div class="job-filter-container">
            <div class="job-filter-header">
                <h2>Trabajos listados</h2>
            </div>

            <input type="text" class="search-bar" placeholder="Buscar trabajo">

            <div>
                <div class="section-filter">
                    <div class="section-title">
                        <div class="title-wrapper">
                            <span>Experiencia</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24"
                                height="24" stroke-width="2">
                                <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                            </svg>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                    <div class="content-filter">
                        <div class="item-filter">0-2 años</div>
                        <div class="item-filter">3-5 años</div>
                        <div class="item-filter">5-10 años</div>
                        <div class="item-filter">10+ años</div>
                    </div>
                </div>

                <div class="section-filter">
                    <div class="section-title">
                        <div class="title-wrapper">
                            <span>Habilidades y calificaciones</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24"
                                height="24" stroke-width="2">
                                <path
                                    d="M6 4h11a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-11a1 1 0 0 1 -1 -1v-14a1 1 0 0 1 1 -1m3 0v18">
                                </path>
                                <path d="M13 8l2 0"></path>
                                <path d="M13 12l2 0"></path>
                            </svg>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                    <div class="content-filter">
                        <div class="item-filter">Avanzado</div>
                        <div class="item-filter">Intermedio</div>
                        <div class="item-filter">Basico</div>
                    </div>
                </div>

                <div class="section-filter">
                    <div class="section-title">
                        <div class="title-wrapper">
                            <span>Grado</span>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                    <div class="content-filter">
                        <div class="item-filter">Licenciatura</div>
                        <div class="item-filter">Ingenieria</div>
                        <div class="item-filter">Maestría</div>
                        <div class="item-filter">Doctorado</div>
                        <div class="item-filter">No especificado</div>

                    </div>
                </div>

                <div class="section-filter">
                    <div class="section-title">
                        <div class="title-wrapper">
                            <span>Tipos de trabajo</span>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                    <div class="content-filter">
                        <div class="item-filter">Tiempo completo</div>
                        <div class="item-filter">Medio tiempo</div>
                        <div class="item-filter">Freelance</div>
                        <div class="item-filter">Contrato</div>
                    </div>
                </div>

                <div class="section-filter">
                    <div class="section-title">
                        <div class="title-wrapper">
                            <span>Empresas</span>
                        </div>
                        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                    <div class="content-filter">
                        <div class="item-filter">Google</div>
                        <div class="item-filter">Microsoft</div>
                        <div class="item-filter">Apple</div>
                        <div class="item-filter">Amazon</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="marginxd">
        <div>
                <div class="job-listing">
                    <div class="job-title">Profesor de Base de Datos</div>
                    <div class="job-details">
                        <div class="job-company">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24"
                                height="24" stroke-width="2">
                                <path d="M8 9l5 5v7h-5v-4m0 4h-5v-7l5 -5m1 1v-6a1 1 0 0 1 1 -1h10a1 1 0 0 1 1 1v17h-8">
                                </path>
                                <path d="M13 7l0 .01"></path>
                                <path d="M17 7l0 .01"></path>
                                <path d="M17 11l0 .01"></path>
                                <path d="M17 15l0 .01"></path>
                            </svg>
                            Universidad Tecnológica de Tijuana
                        </div>
                        <div class="job-location">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24"
                                height="24" stroke-width="2">
                                <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                                <path
                                    d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z">
                                </path>
                            </svg>
                            Tijuana, Baja California, México
                        </div>
                        <div class="job-type"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
                                <path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" />
                                <path d="M12 12l0 .01" />
                                <path d="M3 13a20 20 0 0 0 18 0" />
                            </svg>Medio</div>
                    </div>
                    <div class="job-content">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestiae vitae non quo quibusdam esse
                        repellendus omnis, voluptates ducimus voluptate, assumenda commodi numquam laborum dolor dicta?
                        Laboriosam, a. Error, numquam! Excepturi?
                    </div>
                    <button class="learn-more-btn">Conocer más</button>
                </div>
                <div class="job-listing">
                    <div class="job-title">Gerente de recursos humanos</div>
                    <div class="job-details">
                        <div class="job-company">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24"
                                height="24" stroke-width="2">
                                <path d="M8 9l5 5v7h-5v-4m0 4h-5v-7l5 -5m1 1v-6a1 1 0 0 1 1 -1h10a1 1 0 0 1 1 1v17h-8">
                                </path>
                                <path d="M13 7l0 .01"></path>
                                <path d="M17 7l0 .01"></path>
                                <path d="M17 11l0 .01"></path>
                                <path d="M17 15l0 .01"></path>
                            </svg>
                            Coca Cola México
                        </div>
                        <div class="job-location">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24"
                                height="24" stroke-width="2">
                                <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                                <path
                                    d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z">
                                </path>
                            </svg>
                            Tijuana, Baja California, México
                        </div>
                        <div class="job-type">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
                                <path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" />
                                <path d="M12 12l0 .01" />
                                <path d="M3 13a20 20 0 0 0 18 0" />
                            </svg>Básico
                        </div>
                    </div>
                    <div class="job-content">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam nisi aliquam, fuga vitae
                        officiis minus ducimus sint ad, tenetur soluta vero quasi temporibus molestiae, hic repudiandae
                        itaque quas velit. Consequuntur.
                    </div>
                    <button class="learn-more-btn">Conocer más</button>
                </div>
                <div class="job-listing">
                    <div class="job-title">Ingeniero Civil</div>
                    <div class="job-details">
                        <div class="job-company">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24"
                                height="24" stroke-width="2">
                                <path d="M8 9l5 5v7h-5v-4m0 4h-5v-7l5 -5m1 1v-6a1 1 0 0 1 1 -1h10a1 1 0 0 1 1 1v17h-8">
                                </path>
                                <path d="M13 7l0 .01"></path>
                                <path d="M17 7l0 .01"></path>
                                <path d="M17 11l0 .01"></path>
                                <path d="M17 15l0 .01"></path>
                            </svg>
                            BuildIncorp
                        </div>
                        <div class="job-location">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24"
                                height="24" stroke-width="2">
                                <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                                <path
                                    d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z">
                                </path>
                            </svg>
                            Tijuana, Baja California, México
                        </div>
                        <div class="job-type"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
                                <path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" />
                                <path d="M12 12l0 .01" />
                                <path d="M3 13a20 20 0 0 0 18 0" />
                            </svg>Avanzado</div>
                    </div>
                    <div class="job-content">
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Tenetur fuga assumenda amet, sapiente
                        soluta fugiat rerum, itaque veritatis asperiores excepturi ea optio! Enim perspiciatis nulla
                        delectus in similique, officiis ipsa.
                    </div>
                    <button class="learn-more-btn">Conocer más</button>
                </div>
            </div>
        </div>
    </section>
</body>