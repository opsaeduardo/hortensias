<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-YSZJZZ8ZVT"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-YSZJZZ8ZVT');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo base_url() ?>public/css/style.css?=1.22">
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CODIGO QR -->
    <script src="https://unpkg.com/qrious@4.0.2/dist/qrious.js"></script>
    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Madimi+One&family=Playpen+Sans:wght@300&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <!-- JQUERY -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- PARALLAX -->
    <script src="https://cdn.jsdelivr.net/npm/simple-parallax-js@5.5.1/dist/simpleParallax.min.js"></script>
    <!-- ANIMATE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- ICON -->
    <link rel="shortcut icon" href="<?php echo base_url() ?>public/img/NEGATIVO.png" type="image/x-icon">
    <!-- STRIPE -->
    <script src="https://js.stripe.com/v3/"></script>
    <!-- CAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" />
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>


    <title>Carrera de las Hortensias</title>
</head>

<body>

    <!-- MODAL DE SELECTOR DE PAGO -->
    <div class="modal fade" tabindex="-1" role="dialog" id="selectorPago">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-light">Selecciona el método de pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row text-white">
                        <div class="border-end col-12 col-md-6 d-flex flex-column align-items-center justify-content-center">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h6 class="text-center"><i id="ticket-nombre"></i></h6>
                            </div>
                            <h6><i>VAMOS ANCIANOS (INSCRIPCIÓN)</i></h6>
                            <div class="d-flex justify-content-between align-items-center mt-1 w-100">
                                <h4 class="text-center">Costo:</h4>
                                <h6 class="text-center"><i id="ticket-costoTotal"></i></h6>
                            </div>
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4 class="text-center">Descuento:</h4>
                                <h6 class="text-center"><i id="ticket-descuento"></i></h6>
                            </div>
                            <hr class="w-100">
                            <div class="d-flex justify-content-between align-items-center mt-1 w-100">
                                <h4 class="text-center">TOTAL:</h4>
                                <h6 class="text-center"><i id="ticket-costoReal"></i></h6>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 d-flex flex-column align-items-center justify-content-center">
                            <div id="pagoTarjeta" class="col-12 col-md-6 text-center img-modal">
                                <img src="<?php echo base_url(); ?>/public/img/tarjeta.png" alt="pagoTarjeta" class="img-fluid">
                                <p class="text-white"><i>Tarjeta (Stripe)</i></p>
                            </div>
                            <div id="pagoOXXO" class="col-12 col-md-6 text-center img-modal">
                                <img src="<?php echo base_url(); ?>/public/img/oxxo.png" alt="pagoOXXO" class="img-fluid">
                                <p class="text-white"><i>OXXO</i></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL STRIPE -->
    <div class="modal" tabindex="-2" role="dialog" id="stripeModal">
        <div style="height: auto;" class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="background-color:rgba(255, 255, 255, 0.49); backdrop-filter: blur(5px);border-radius: 15px;">
                <div class="modal-header" style="background: #6772e5;">
                    <h5 class="modal-title title-stripe"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalStripeContent">
                    <div id="checkout">
                    </div>
                </div>
                <div class="modal-footer text-center" style="background: #6772e5;">
                    <div style="display: flex;flex-wrap: nowrap;justify-content: center;align-items: center" class="row d-flex">
                        <div class="col-4">
                            <img style="width: 100%; height: auto;" src="<?php echo base_url() ?>public/img/stripe-logo.gif" alt="STRIPE">
                        </div>
                        <div class="col-8">
                            <p style="font-size: 15px; text-align: justify; padding-top: 0.5px; letter-spacing: 1px; color: white;">
                                <strong>
                                    Stripe, líder mundial en pagos en línea, para procesar tu transacción. Stripe cumple con los estándares más rigurosos de seguridad en la industria. Puedes realizar tu pago con confianza.
                                </strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTON ARRIBA -->
    <div hidden class="boton-arriba">
        <i class="fas fa-chevron-circle-up"></i>
    </div>

    <!-- NAVEGACION -->
    <div class="container-video">
        <nav id="nav" class="navbar navbar-expand-lg navbar-dark fixed-top">
            <div class="container-fluid">
                <img src="<?php echo base_url() ?>public/img/NEGATIVO.png" alt="Logo">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    </ul>
                    <div id="navbar-fondo" class="d-flex justify-content-center">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item m-2">
                                <a id="divEvento" class="nav-link" aria-current="page" href="javascript:void(0)">
                                    <i class="fas fa-calendar"></i> Momentos
                                </a>
                            </li>
                            <li class="nav-item m-2">
                                <a id="divCarrera" class="nav-link" href="javascript:void(0)">
                                    <i class="fas fa-running"></i> Carrera
                                </a>
                            </li>
                            <li class="nav-item m-2">
                                <a id="divGanadores" class="nav-link" href="javascript:void(0)">
                                    <i class="fas fa-medal"></i> Ganadores
                                </a>
                            </li>
                            <li class="nav-item m-2">
                                <a id="divRecorrido" class="nav-link" href="javascript:void(0)">
                                    <i class="fas fa-map-marked-alt"></i> Ruta
                                </a>
                            </li>
                            <li class="nav-item m-2" style="display: flex; flex-wrap: wrap; align-content: center;">
                                <button class="botonRegistro">
                                    <a id="divInscripcion" class="nav-link" href="javascript:void(0)" style="color: black;">
                                        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 10px;"><i style="font-size: 20px;" class="fas fa-sign-in-alt"></i>
                                            <p style="padding: 0; margin: 0;">Carrera Fredy Valencia</p>
                                        </div>
                                    </a>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        <div class="container-video-text-background">
            <i id="texto">CARRERA DE LAS HORTENSIAS</i>
        </div>
        <!-- VIDEO FONDO -->
        <div class="video-wrapper">
            <video muted autoplay loop playsinline poster preload="metadata" autoplay>
                <source src="<?php echo base_url() ?>public/img/video2.webm" type="video/mp4">
                Tu navegador no soporta el elemento video.
            </video>
        </div>
    </div>

    <!-- MOMENTOS DE LA CARRERA -->
    <div id="container-collage" class="mt-4 container-collage">
        <h2 class="mb-4">
            <i>¡En <strong>TAPACHULA</strong> vivimos grandes momentos!</i>
        </h2>
        <div class="container-collage-individual">
            <div class="item1">
                <img class="e-right" src="<?php echo base_url() ?>public/img/Entrega-kits2.jpg" alt="Kits">
            </div>
            <div class="item2">
                <h2>
                    ENTREGA DE KITS
                </h2>
            </div>
            <div class="item3">
                <p>
                    Contenido <strong>KIT DEL CORREDOR <i class="fas fa-shopping-bag"></i>:</strong>
                    <br>
                    • ACCESO AL ICE BREAKER.
                    <br>
                    • PLAYERA DRY FI.
                    <br>
                    • NÚMERO DE CORREDOR.
                    <br>
                    • ACCESO A CONCIERTO.
                </p>
            </div>
        </div>
        <div class="container-collage-individual">
            <div class="item1">
                <img class="e-up" src="<?php echo base_url() ?>public/img/Valentin.jpg" alt="SanValentin">
            </div>
            <div class="item2">
                <h2>MATCH SAN VALENTÍN</h2>
            </div>
            <div class="item3">
                <p>Concurso especial en pareja por el día de San Valentín <i class="fas fa-heart"></i>.
                    <br>
                    La pareja, con mejor outfit alusivo a San Valentín, ganó
                    <strong>UN AÑO GRATIS</strong>
                    en anytime fitness.
                </p>
            </div>
        </div>
        <div class="container-collage-individual">
            <div class="item1">
                <img class="e-left" src="<?php echo base_url() ?>public/img/icebraker.jpg" alt="IceBreaker">
            </div>
            <div class="item2">
                <h2>
                    ICE BREAKER
                </h2>
            </div>
            <div class="item3">
                <p>
                    <strong>@ZINOMUSIC 🎶 </strong>fue el encargado de crear una atmósfera vibrante y llena de ritmo.
                    <br>
                    La diversión y la música se entrelazaron para formar un evento <strong>MEMORABLE</strong>.
                </p>
            </div>
        </div>
        <div class="div-up">
            <div class="div-up-div text-white">
                <h4 class="m-4">
                    Celebrando su primer aniversario en Tapachula y el quinto de presencia en el estado de Chiapas,
                    <i>
                        <strong>la comunidad saludable más grande y alegre del mundo</strong>
                    </i>
                    llevó a cabo este gran evento para sus socios.
                </h4>
            </div>
            <div class="logo-any">
                <img src="<?php echo base_url() ?>public/img/Anytime-Fitness-logo 1.png" alt="Any Time">
            </div>
        </div>
    </div>

    <!-- VIDEO PROMOCIONAL -->
    <div id="carrera" class="container-video-promo pb-4">
        <p class="m-4 texto-experiencia">
            <i>
                <strong>
                    ¡Nuestra Carrera de las Hortensias
                    <i>
                        <strong>
                            2024!
                        </strong>
                    </i>
                </strong>
            </i>
        </p>
        <div class="video-wrapper-promo">
            <video muted src="<?php echo base_url() ?>public/img/video.webm" autoplay loop playsinline poster preload="metadata">
        </div>
        <p class="m-4 texto-experiencia">
            <i>
                <strong>
                    ¡Te esperamos este
                    <i>
                        <strong>
                            2025!
                        </strong>
                    </i>
                </strong>
            </i>
        </p>
        <button class="botonRegistro p-2" style="color: #3c0058;">Regístrate</button>
    </div>

    <!-- GANADORES -->
    <div id="ganadores" class="container-winers-title mt-4 div-ganadores">
        <div class="container-winers-background">
        </div>
        <div class="container-winers-text">
            <h2 class="container-winers-h2">
                <i><strong>¡GANADORES!</strong></i>
            </h2>
            <p class="container-winers-p">
                Ya sea que compitas por la victoria o simplemente disfrutes del recorrido, la
                <i>
                    <strong>Carrera de las Hortensias</strong>
                </i>
                &nbsp;te dejará con recuerdos imborrables y un profundo aprecio por la
                maravillosa diversidad que Chiapas tiene para ofrecer.
            </p>
        </div>
    </div>

    <!-- PODIO -->
    <div style="display: none;" class="container-winers div-ganadores">
        <div class="container-win-individual">
            <div class="win-img">
                <img class="e-right" src="<?php echo base_url() ?>public/img/2do-pro-femenil.jpg" alt="Segundo Lugar">
            </div>
            <div class="win-name">
                <h2>SEGUNDO LUGAR</h2>
            </div>
            <div class="lugar silver">
                2
            </div>
            <div class="win-text">
                <p>
                    El segundo lugar <strong>NO SE QUEDÓ ATRÁS</strong> en premios:
                    <br>
                    • Mochila Underarmour azul/Nike Rosa
                    <br>
                    • Cordón Anytime Fitness edición especial
                    <br>
                    • Proteína vegetal Birdman 30g sabor matcha
                    <br>
                    Y más <strong>SORPRESAS</strong>...
                </p>
            </div>
        </div>

        <div class="container-win-individual">
            <div class="win-img">
                <img class="e-up" src="<?php echo base_url() ?>public/img/1-juvenil-varonil.jpg" alt="Primer Lugar">
            </div>
            <div class="win-name">
                <h2>PRIMER LUGAR</h2>
            </div>
            <div class="lugar gold">
                1
            </div>
            <div class="win-text">
                <p>
                    El <strong>GANADOR</strong>, obtuvo ¡increíbles premios!:
                    <br>
                    • 1 Mochila Nike
                    <br>
                    • 1 audífonos JBL
                    <br>
                    • 1 Iceshaker
                    <br>
                    • 1 proteína Birdman sabor chocolate
                    <br>
                    <strong>¡Y MUCHO MÁS!</strong>
                </p>
            </div>
        </div>

        <div class="container-win-individual">
            <div class="win-img">
                <img class="e-left" src="<?php echo base_url() ?>public/img/3er-juvenil-varonil.jpg" alt="Tercer Lugar">
            </div>
            <div class="win-name">
                <h2>TERCER LUGAR</h2>
            </div>
            <div class="lugar bronze">
                3
            </div>
            <div class="win-text">
                <p>
                    El tercer lugar también fue recompensado con premios <strong>IMPRESIONANTES</strong>, entre ellos:
                    <br>
                    • 1 Iceshaker
                    <br>
                    • 1 Proteína Birdman sabor chocolate
                    <br>
                    • 1 Cangurera deportiva Athletic
                    <br>
                    ¡Y otros <strong>PREMIOS MÁS!</strong>
                </p>
            </div>
        </div>
    </div>

    <!-- RECORRIDO -->
    <div class="recorrido">
        <a href="<?php echo base_url() ?>public/img/Ruta.png" data-lightbox="ImagenRuta" data-title="Ruta de Recorrido">
            <img src="<?php echo base_url() ?>public/img/Ruta.png" alt="Ico">
        </a>

    </div>

    <!-- FORMULARIO -->
    <div id="divFormulario" class="container-form form-inputs d-flex">
        <a style="width: 70%; height: auto;" href="<?php echo base_url() ?>public/img/cartel.jpeg" data-lightbox="Cartel" data-title="Cartel">
            <img style="width: 100%; height: 100%;" src="<?php echo base_url() ?>public/img/cartel.jpeg" alt="Cartel">
        </a>
        <form style="margin: auto;" action="<?php echo base_url(); ?>/Home/agregarParticipante" method="post" id="formAgregarParticipante" enctype="multipart/form-data">
            <div class="row div-form-inputs">
                <div class="col-12">
                    <h2 class="text-center text-dark"><i style="text-shadow: 0px 0px 5px rgb(0, 0, 0, 0.5); font-weight: 600;">Regístrate</i></h2>
                    <h4 class="text-center text-dark"><i style="text-shadow: 0px 0px 5px rgb(0, 0, 0, 0.5); font-weight: 600;">Carrera Fredy Valencia <b>(EL PUMA)</b> 84° Aniversario</i></h4>
                </div>
                <div class="col-md-5 col-12">
                    <label class="m-1" for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre Completo" class="form-control">
                </div>
                <div class="col-md-5 col-12">
                    <label class="m-1" for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" class="form-control">
                </div>
                <div class="col-md-2 col-12">
                    <label class="m-1" for="edad">Edad:</label>
                    <input maxlength="2" type="text" id="edad" name="edad" placeholder="Edad" class="form-control">
                </div>
                <div class="col-md-4 col-6">
                    <label class="m-1" for="sexo">Sexo:</label>
                    <select class="form-select" id="sexo" name="sexo">
                        <option value=""></option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                    </select>
                </div>
                <div class="col-md-4 col-6">
                    <label class="m-1" for="telefono">Teléfono:</label>
                    <input type="number" id="telefono" name="telefono" placeholder="Número Telefónico" class="form-control">
                </div>
                <div id="divCupones" class="col-md-4 col-12">
                    <label class="m-1" for="cupon">Cupón:</label>
                    <input type="text" id="cupon" name="cupon" placeholder="Ingresa el cupón" class="form-control">
                    <small><i id="textoCupon" class="text-white fw-bold">Si cuentas con un cupón, ingrésalo aquí.</i></small>
                </div>
                <div class="mt-2" style=" display: flex; justify-content: space-between; flex-wrap: wrap;">
                    <div>
                        <input type="checkbox" class="form-check-input" name="" id="boton-check">
                        <label for="">Acepto el <a style="color: #420360 !important;" target="_blank" href="<?php echo base_url(); ?>aviso-de-privacidad">Aviso de Privacidad </a> y los <a style="color: #420360 !important;" target="_blank" href="<?php echo base_url(); ?>terminos-y-condiciones"> Términos y Condiciones</a></label>
                    </div>
                    <button type="submit" id="agregarParticipante" class="btn btn-success">Registrarme</button>
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                </div>
            </div>
        </form>
    </div>

    <!-- FOOTER -->
    <div class="footer-amifit">
        <div class="carrera">
            <img width="200" height="150" src="<?php echo base_url() ?>public/img/NEGATIVO.png" alt="Ico">
        </div>
        <div class="logotipo">
            <p><i><strong>POWERED BY:</strong></i></p>
            <img src="<?php echo base_url() ?>public/img/logo_amarillo 1.png" alt="Logo Amifit">
        </div>
        <div class="contacto">
            <a class="icono" target="_blank" href="https://www.facebook.com/Anytimetapachula/"><i class="fab fa-facebook"></i></a>
            <a class="icono" target="_blank" href="https://www.tiktok.com/@anytimefitness.tapachula"><i class="fab fa-tiktok"></i></a>
            <a class="icono" target="_blank" href="https://www.instagram.com/anytimefitnesstapachula/"> <i class="fab fa-instagram"></i></a>
        </div>
    </div>

    <!-- BOOTSTRAP -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    <!-- FONTAWESOME -->
    <script src="https://kit.fontawesome.com/8c0737a2ba.js" crossorigin="anonymous"></script>
    <!-- JS -->
    <script src="<?php echo base_url() ?>public/js/main.js?v=<?php echo time(); ?>"></script>

    <!-- sweetalert -->
    <script src="<?php echo base_url(); ?>/public/js/sweetalert/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <!-- CATPCHA -->
    <script src="https://www.google.com/recaptcha/api.js?render=6Ld13XwqAAAAAE3-CIZxbpQ_HOSPgMLZ3hVLxDPJ"></script>
    <!-- RUTA -->
    <script>
        var ruta = "<?php echo base_url(); ?>";
        var stripePublicId = "<?php echo env('PUBLIC_ID') ?>";
        var datos = <?php echo json_encode($_GET); ?>;
    </script>
</body>

</html>