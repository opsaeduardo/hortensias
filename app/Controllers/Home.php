<?php

namespace App\Controllers;

use App\Models\participantes;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'public/PHPMailer/Exception.php';
require 'public/PHPMailer/PHPMailer.php';
require 'public/PHPMailer/SMTP.php';

define('METHOD', 'AES-256-CBC');
define('SECRET_KEY', 'op5813Sa2135So56N');
define('SECRET_IV', '7884588');

class Home extends BaseController
{
    // 1) NUEVAS CONSTANTES PARA QR ENCRYPT:
    private const ENCRYPTEQR_METHOD  = 'AES-256-CBC';
    private const ENCRYPTEQR_SECRET_KEY = 'm1S3cret0QRP4r4ElCl13nt3';  // 32 caracteres exactos
    private const ENCRYPTEQR_SECRET_IV  = 'ivSecretQRAqui16b'; // 16 caracteres exactos

    // DECLARACION DE MODULOS
    protected $moduloCupon = 'Cupones';
    protected $moduloPaseDoble = 'PaseDoble';
    protected $moduloRegistro = 'Registro';

    // PRECIO DE INSCRIPCION
    protected $precio = 850;

    // COMSION FIJA DE STRIPE DE 3 PESOS
    protected $comisionFija = 3;

    // VISTA
    public function index(): string
    {
        return view('index');
    }

    // TERMINOS Y CONDICIONES
    public function terms()
    {
        return view('terminosycondiciones/terminos');
    }

    // AVISO DE PRIVACIDAD
    public function privacidad()
    {
        return view('avisodeprivacidad/privacidad');
    }

    // VISTA DE REGISTRO
    public function registro()
    {
        return view('registro/registro');
    }

    // METODO QUE VERIFICA EL STATUS DE LOS MODULOS
    public function consultarModulos()
    {
        $model = new participantes();

        $consulta = $model->consultarModulos($this->moduloCupon);

        return json_encode($consulta);
    }

    // VERIFICA QUE EL LOS MODULOS ESTEN ACTIVOS
    public function moduloCupones()
    {
        $modelo = new participantes();

        $consultarCupones = $modelo->consultarModulos($this->moduloCupon);

        if ($consultarCupones == '0') {
            return json_encode('0');
        } else {
            return json_encode($consultarCupones);
        }
    }

    // VERIFICA QUE EL MODULO KEYFOB (2X1) ESTE ACTIVO
    public function moduloPaseDoble()
    {
        $modelo = new participantes();

        $consultarCupones = $modelo->consultarModulos($this->moduloPaseDoble);

        if ($consultarCupones == '0') {
            return json_encode('0');
        } else {
            return json_encode($consultarCupones);
        }
    }

    // METODO QUE VERIFICA QUE LOS MODULOS ESTEN ACTIVOS Y REGISTRADOS
    public function verificarModulos()
    {
        $modelo = new participantes();

        // VERIFICA EL STATUS DE CUPONES
        $estadoCupones = $modelo->consultarModulos($this->moduloCupon);
        $estadoCupones = ($estadoCupones == '0') ? '0' : $estadoCupones;

        // VERIFICA EL STATUS DE LOS PASES DOBLES
        $estadoPaseDoble = $modelo->consultarModulos($this->moduloPaseDoble);
        $estadoPaseDoble = ($estadoPaseDoble == '0') ? '0' : $estadoPaseDoble;

        return json_encode([
            'cupones' => $estadoCupones,
            'paseDoble' => $estadoPaseDoble
        ]);
    }


    // METODO QUE VERIFICA QUE LOS REGISTROS ESTEN ACTIVOS
    public function verificarRegistro()
    {
        $modelo = new participantes();

        $consultarRegistro = $modelo->consultarModulos($this->moduloRegistro);

        if ($consultarRegistro == '0') {
            return json_encode('0');
        } else {
            return json_encode($consultarRegistro);
        }
    }

    // METODO QUE VERIFICA EL LIMITE DE REGISTROS
    public function verificarLimiteRegistros()
    {
        $modelo = new participantes();

        $limite = $modelo->verificarLimiteRegistros();

        return json_encode($limite);
    }

    // METODO QUE AGREGA AL PARTICIPANTE
    public function agregarParticipante()
    {

        $modelo = new participantes();

        // VERIFICA EL LIMITE DE REGISTROS
        $limiteRegistro = $modelo->verificarLimiteRegistros();
        if ($limiteRegistro == 'LimiteExcedido') {
            return json_encode('LimiteExcedido');
        }

        // OBTENCION DE LOS DATOS DE LA VISTA
        $nombre = $this->request->getVar('nombre');
        $correo = $this->request->getVar('correo');
        $playera = 'Sin Playera';
        $sexo = $this->request->getVar('sexo');
        $telefono = $this->request->getVar('telefono');
        $cupon = $this->request->getVar('cupon');
        $keyfob = '';
        $edad = $this->request->getVar('edad');

        // // SE VALIDA EL CATPCHA
        // $captcha = $this->request->getVar('g-recaptcha-response');
        // $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        // $recaptcha_secret = '6Ld13XwqAAAAAIViPRIwK7zDVkEi6Tgxf26KEXjs';
        // $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $captcha);
        // $recaptcha = json_decode($recaptcha);

        // // return json_encode($recaptcha);
        // if ($recaptcha->score < 0.5) {
        //     return json_encode('CaptchaError');
        // }

        // SE CALCULA LA CATEGORÍA
        $categoria = '';

        if ($edad >= 0 && $edad <= 18) {
            $categoria = 'Infantiles';
        } elseif ($edad > 18 && $edad <= 29) {
            $categoria = 'Libre';
        } elseif ($edad >= 30 && $edad <= 45) {
            $categoria = 'Master';
        } elseif ($edad >= 46) {
            $categoria = 'Veteranos';
        }


        // SE CARGAN LOS DATOS DEL PARTICIPANTE
        $data = [
            'Nombre' => $nombre,
            'Correo' => $correo,
            'Sexo' => $sexo,
            'Telefono' => $telefono,
            'TipoPlayera' => $playera,
            'Categoria' => $categoria,
            'Cupon' => $cupon,
            'Keyfob' => $keyfob,
            'Edad' => $edad,
            // 'UserAgent' => $_SERVER['HTTP_USER_AGENT'],
            'Fecha' => date('Y-m-d H:i:s')
        ];

        // SE VERIFICA QUE KEYFOB Y CUPON ESTEN VACIOS
        if ($cupon == null && $keyfob == null) {
            $data['TipoPago'] = 'Tarjeta';
            $agregarParticipante = $modelo->agregarParticipante($data);

            if ($agregarParticipante > 0) {

                // COMISIÓN POR PORCENTAJE
                $comisionPorcentaje = round($this->precio * 0.036, 2);

                // IVA SOBRE COMISIONES
                $IVA = round(($this->comisionFija + $comisionPorcentaje) * 0.16, 2);

                // MONTO NETO
                $montoNeto = round($this->precio - $this->comisionFija - $comisionPorcentaje - $IVA, 2);

                // Crear la respuesta
                $respuesta['Status'] = 'RegistroExitoso';
                $respuesta['IdParticipante'] = $agregarParticipante;
                $respuesta['PrecioOriginal'] = $this->precio;
                $respuesta['Descuento'] = '';
                $respuesta['PrecioConDescuento'] = '';
                $respuesta['ComisionPorcentaje'] = $comisionPorcentaje;
                $respuesta['ComisionFija'] = '';
                $respuesta['MontoNeto'] = $montoNeto;
                $respuesta['IVA'] = $IVA;
                $respuesta['KeyFob'] = '';
                $respuesta['CuponAplicado'] = 'SinCupon';

                // SE CARGA EL ARRAY CON LOS MONTOS A ACTUALIZAR
                $dataMontos = [
                    'Total' => $this->precio,
                    'ComisionPorcentaje' => $comisionPorcentaje,
                    'ComisionFija' => $this->comisionFija,
                    'IVA' => $IVA,
                    'MontoTotal' => $montoNeto,
                ];

                // SE ACTUALIZA EL REGISTRO CON LOS MONTOS
                $actualizarMontos = $modelo->actualizarRegistro($agregarParticipante, $dataMontos);

                return json_encode($respuesta);
            } else {
                return json_encode('ErrorAgregarParticipante');
            }
        }

        // SE VERIFICA QUE TENGA LLAVE
        if ($keyfob != null) {

            $keyfobValida = $modelo->validarKeyfob($keyfob);
            if ($keyfobValida == 0) {
                return json_encode('KeyFobInvalida');
            }

            // SE VERIFICA SI YA EXISTE UN PASE DOBLE
            $consultaPaseDoble = $modelo->verificarPaseDoble($keyfob);

            if ($consultaPaseDoble == 'PagoCompleto') {

                // COMISIÓN POR PORCENTAJE
                $comisionPorcentaje = round($this->precio * 0.036, 2);

                // IVA SOBRE COMISIONES
                $IVA = round(($this->comisionFija + $comisionPorcentaje) * 0.16, 2);

                // MONTO NETO
                $montoNeto = round($this->precio - $this->comisionFija - $comisionPorcentaje - $IVA, 2);
                $descuento = 0;
                $montoDescuento = 0;
                $precioConDescuento = 0;
                $respuesta['Status'] = 'RegistroExitoso';
                $dataMontos = [
                    'Total' => $this->precio
                ];
                $data['TipoPago'] = 'Tarjeta';


                // VERIFICA QUE HAY CUPON
                if ($cupon != null) {

                    // SE VERIFICA QUE EL CUPON EXISTA Y ESTE ACTIVO
                    $validarCupon = $modelo->validarCupones($cupon);

                    if ($validarCupon == 'NoExisteCupon') {
                        return json_encode('NoExisteCupon');
                    } elseif ($validarCupon == 'CuponActivo') {
                        // SE VERIFICA QUE EL CUPON ESTE DENTRO DEL LIMITE PERMITIDO
                        $validarLimiteCupon = $modelo->validarLimiteCupones($cupon);

                        if ($validarLimiteCupon == 'CuponLleno') {
                            return json_encode('CuponLleno');
                        } else {

                            // CONVIERTE EL DESCUENTO DE STRING A NÚMERO
                            $descuento = (float)$validarLimiteCupon["Descuento"];

                            // CALCULA EL MONTO DEL DESCUENTO
                            $montoDescuento = ($this->precio * $descuento) / 100;

                            // RESTA EL PRECIO MENOS EL DESCUENTO
                            $precioConDescuento = $this->precio - $montoDescuento;

                            // COMISIÓN POR PORCENTAJE
                            $comisionPorcentaje = round($precioConDescuento * 0.036, 2);

                            // IVA SOBRE COMISIONES
                            $IVA = round(($this->comisionFija + $comisionPorcentaje) * 0.16, 2);

                            // MONTO NETO
                            $montoNeto = round($precioConDescuento - $this->comisionFija - $comisionPorcentaje - $IVA, 2);

                            if ($validarLimiteCupon["Descuento"] == '100') {
                                $respuesta['Status'] = 'RegistroExitosoGratuitoCupon';

                                // SE REGISTRA EL NUMERO DEL PARTICIPANTE
                                $ultimoNumeroParticipante = $modelo->ultimoNumeroParticipante();


                                if ($ultimoNumeroParticipante == 'SinNumeroParticipante') {
                                    $numeroParticipante = 100;
                                } else {
                                    $numeroParticipante = $ultimoNumeroParticipante + 1;
                                }

                                $data['TipoPago'] = 'Efectivo';

                                $respuesta["NumeroParticipante"] = $numeroParticipante;

                                $dataMontos["NumeroParticipante"] = $numeroParticipante;

                                $comisionPorcentaje = '0';
                                $montoNeto = '0';
                                $precioConDescuento = '0';
                                $dataMontos["StatusPago"] = 'Pagado';
                            }
                        }
                    } elseif ($validarCupon == 'CuponInactivo') {
                        return json_encode('CuponInactivo');
                    }
                }

                // SE  AGREGA EL REGISTRO DEL PARTICIPANTE
                $agregarParticipante = $modelo->agregarParticipante($data);

                if ($agregarParticipante > 0) {

                    // SE CARGA LA REPUESTA
                    $respuesta['IdParticipante'] = $agregarParticipante;
                    $respuesta['PrecioOriginal'] = $this->precio;
                    $respuesta['Descuento'] = $descuento;
                    $respuesta['PrecioConDescuento'] = $precioConDescuento;
                    $respuesta['ComisionPorcentaje'] = $comisionPorcentaje;
                    $respuesta['ComisionFija'] = $this->comisionFija;
                    $respuesta['MontoNeto'] = $montoNeto;
                    $respuesta['IVA'] = $IVA;
                    $respuesta['KeyFob'] = $keyfob;
                    $respuesta['CuponAplicado'] = $cupon;

                    // SE CARGA EL ARRAY CON LOS MONTOS A ACTUALIZAR
                    $dataMontos["ComisionPorcentaje"] = $comisionPorcentaje;
                    $dataMontos["ComisionFija"] = $this->comisionFija;
                    $dataMontos["MontoTotal"] = $montoNeto;
                    $dataMontos["IVA"] = $IVA;
                    $dataMontos["TotalConDescuento"] = $precioConDescuento;

                    // SE ACTUALIZA EL REGISTRO CON LOS MONTOS
                    $actualizarMontos = $modelo->actualizarRegistro($agregarParticipante, $dataMontos);

                    // SE AGREGA EL REGISTRO DE PASE DOBLE
                    $paseDoble = [
                        'KeyFob' => $keyfob,
                        'NombreParticipante' => $nombre,
                        'IdParticipante' => $agregarParticipante
                    ];

                    $agregarPaseDoble = $modelo->agregarPaseDoble($paseDoble);

                    return json_encode($respuesta);
                } else {
                    return json_encode('ErrorAgregarParticipante');
                }
            }

            if ($consultaPaseDoble == 'RegistroGratis') {

                // SI NO HAY PASE DOBLE REGISTRADO, AGREGA EL PARTICIPANTE CON PAGO
                $data['StatusPago'] = 'Pagado';
                $data['TipoPago'] = 'PaseDoble';
                $agregarParticipante = $modelo->agregarParticipante($data);

                if ($agregarParticipante > 0) {

                    // SE AGREGA EL REGISTRO DE PASE DOBLE
                    $paseDoble = [
                        'KeyFob' => $keyfob,
                        'NombreParticipante' => $nombre,
                        'IdParticipante' => $agregarParticipante
                    ];

                    $agregarPaseDoble = $modelo->agregarPaseDoble($paseDoble);

                    // SE CARGA LA REPUESTA
                    $respuesta['Status'] = 'RegistroExitosoGratuito';
                    $respuesta['IdParticipante'] = $agregarParticipante;
                    $respuesta['PrecioOriginal'] = $this->precio;
                    $respuesta['Descuento'] = '';
                    $respuesta['PrecioConDescuento'] = '';
                    $respuesta['ComisionPorcentaje'] = '';
                    $respuesta['ComisionFija'] = '';
                    $respuesta['MontoNeto'] = '';
                    $respuesta['IVA'] = '';
                    $respuesta['KeyFob'] = $keyfob;
                    $respuesta['CuponAplicado'] = '';

                    // SE REGISTRA EL NUMERO DEL PARTICIPANTE
                    $ultimoNumeroParticipante = $modelo->ultimoNumeroParticipante();

                    if ($ultimoNumeroParticipante == 'SinNumeroParticipante') {
                        $numeroParticipante = 100;
                    } else {
                        $numeroParticipante = $ultimoNumeroParticipante + 1;
                    }

                    $respuesta["NumeroParticipante"] = $numeroParticipante;

                    $actualizacionNumero["NumeroParticipante"] = $numeroParticipante;

                    $consulta = $modelo->actualizarRegistro($agregarParticipante, $actualizacionNumero);

                    return json_encode($respuesta);
                } else {
                    return json_encode('ErrorAgregarParticipante');
                }
            }
            if ($consultaPaseDoble == 'OfertaAplicada') {
                // SI EXISTE YA UN REGISTRO MANDAMOS LA RESPUESTA
                return json_encode('ExistePaseDoble');
            }
        }

        if ($cupon != null) {
            // SE VERIFICA QUE EL CUPON EXISTA Y ESTE ACTIVO
            $validarCupon = $modelo->validarCupones($cupon);

            if ($validarCupon == 'NoExisteCupon') {
                return json_encode('NoExisteCupon');
            } elseif ($validarCupon == 'CuponActivo') {

                // SE VERIFICA QUE EL CUPON ESTE DENTRO DEL LIMITE PERMITIDO
                $validarLimiteCupon = $modelo->validarLimiteCupones($cupon);

                if ($validarLimiteCupon == 'CuponLleno') {
                    return json_encode('CuponLleno');
                } else {

                    // VERIFICA SI ES GRATUITO /* yyy */
                    if ($validarLimiteCupon["Descuento"] == '100') {
                        // SI NO HAY PASE DOBLE REGISTRADO, AGREGA EL PARTICIPANTE CON PAGO
                        $data['StatusPago'] = 'Pagado';
                        $data['TipoPago'] = 'Efectivo';
                        $agregarParticipante = $modelo->agregarParticipante($data);

                        if ($agregarParticipante > 0) {


                            $respuesta['Status'] = 'RegistroExitosoGratuitoCupon';
                            $respuesta['IdParticipante'] = $agregarParticipante;
                            $respuesta['PrecioOriginal'] = $this->precio;
                            $respuesta['Descuento'] = $validarLimiteCupon["Descuento"];
                            $respuesta['PrecioConDescuento'] = 0;
                            $respuesta['ComisionPorcentaje'] = '0';
                            $respuesta['ComisionFija'] = '0';
                            $respuesta['MontoNeto'] = '0';
                            $respuesta['IVA'] = '0';
                            $respuesta['KeyFob'] = '';
                            $respuesta['CuponAplicado'] = $cupon;

                            // SE CARGA EL ARRAY CON LOS MONTOS A ACTUALIZAR
                            $dataMontos = [
                                'Total' => $this->precio,
                                'ComisionPorcentaje' => '0',
                                'ComisionFija' => '0',
                                'IVA' => '0',
                                'MontoTotal' => '0',
                                'TotalConDescuento' => '0',

                            ];

                            // SE REGISTRA EL NUMERO DEL PARTICIPANTE
                            $ultimoNumeroParticipante = $modelo->ultimoNumeroParticipante();

                            if ($ultimoNumeroParticipante == 'SinNumeroParticipante') {
                                $numeroParticipante = 100;
                            } else {
                                $numeroParticipante = $ultimoNumeroParticipante + 1;
                            }

                            $respuesta["NumeroParticipante"] = $numeroParticipante;

                            $dataMontos["NumeroParticipante"] = $numeroParticipante;


                            // SE ACTUALIZA EL REGISTRO CON LOS MONTOS
                            $actualizarMontos = $modelo->actualizarRegistro($agregarParticipante, $dataMontos);

                            return json_encode($respuesta);
                        } else {
                            return json_encode('ErrorAgregarParticipante');
                        }
                    }

                    $data['TipoPago'] = 'Tarjeta';
                    // SE  AGREGA EL REGISTRO DEL PARTICIPANTE
                    $agregarParticipante = $modelo->agregarParticipante($data);

                    if ($agregarParticipante > 0) {

                        // CONVIERTE EL DESCUENTO DE STRING A NÚMERO
                        $descuento = (float)$validarLimiteCupon["Descuento"];

                        // CALCULA EL MONTO DEL DESCUENTO
                        $montoDescuento = ($this->precio * $descuento) / 100;

                        // RESTA EL PRECIO MENOS EL DESCUENTO
                        $precioConDescuento = $this->precio - $montoDescuento;

                        // COMISIÓN POR PORCENTAJE
                        $comisionPorcentaje = round($precioConDescuento * 0.036, 2);

                        // IVA SOBRE COMISIONES
                        $IVA = round(($this->comisionFija + $comisionPorcentaje) * 0.16, 2);

                        // MONTO NETO
                        $montoNeto = round($precioConDescuento - $this->comisionFija - $comisionPorcentaje - $IVA, 2);

                        // Crear la respuesta
                        $respuesta['Status'] = 'RegistroExitoso';
                        $respuesta['IdParticipante'] = $agregarParticipante;
                        $respuesta['PrecioOriginal'] = $this->precio;
                        $respuesta['Descuento'] = $validarLimiteCupon["Descuento"];
                        $respuesta['PrecioConDescuento'] = $precioConDescuento;
                        $respuesta['ComisionPorcentaje'] = $comisionPorcentaje;
                        $respuesta['ComisionFija'] = $this->comisionFija;
                        $respuesta['MontoNeto'] = $montoNeto;
                        $respuesta['IVA'] = $IVA;
                        $respuesta['KeyFob'] = '';
                        $respuesta['CuponAplicado'] = $cupon;

                        // SE CARGA EL ARRAY CON LOS MONTOS A ACTUALIZAR
                        $dataMontos = [
                            'Total' => $this->precio,
                            'ComisionPorcentaje' => $comisionPorcentaje,
                            'ComisionFija' => $this->comisionFija,
                            'IVA' => $IVA,
                            'MontoTotal' => $montoNeto,
                            'TotalConDescuento' => $precioConDescuento,

                        ];

                        // SE ACTUALIZA EL REGISTRO CON LOS MONTOS
                        $actualizarMontos = $modelo->actualizarRegistro($agregarParticipante, $dataMontos);

                        return json_encode($respuesta);
                    } else {
                        return json_encode('ErrorAgregarParticipante');
                    }
                }
                return json_encode($validarLimiteCupon);
            } elseif ($validarCupon == 'CuponInactivo') {
                return json_encode('CuponInactivo');
            }
        }
        return json_encode('afuera');
    }

    // METODO QUE VERIFICA QUE LA KEYFOB SEA VALIDA
    public function validarKeyfob($keyFob)
    {
        $modelo = new participantes();

        $consulta = $modelo->validarKeyfob($keyFob);

        return json_encode($consulta);
    }

    // METODO QUE VERIFICA QUE LA KEYFOB SEA VALIDA VARIABLE
    public function validarKeyfobVariable()
    {
        $modelo = new participantes();

        $keyfob = $this->request->getVar('keyfob');

        $consulta = $modelo->validarKeyfob($keyfob);

        return json_encode($consulta);
    }

    // METODO QUE VALIDA QUE SOLO EXISTA UN PASE DOBLE
    public function verificarPaseDoble($keyFob)
    {
        $modelo = new participantes();

        $consulta = $modelo->verificarPaseDoble($keyFob);

        return json_encode($consulta);
    }

    // METODO QUE VALIDA QUE EL CUPON ESTE ACTIVO Y HAYA DISPONIBLES
    public function validarCupones($cupon)
    {
        $modelo = new participantes();

        $consulta = $modelo->validarCupones($cupon);

        return json_encode($consulta);
    }

    // METODO QUE VALIDA QUE EL CUPON ACTIVO NO HAYA PASADO EL LIMITE DISPONIBLE
    public function validarLimiteCupones($cupon)
    {
        $modelo = new participantes();

        $consulta = $modelo->validarLimiteCupones($cupon);

        return json_encode($consulta);
    }

    // METODO QUE PONE EL PAGO COMO CANCELADO EL REGISTRO
    public function actualizarURL()
    {
        $modelo = new participantes();

        $url = $this->request->getVar('url');
        $id = $this->request->getVar('id');

        $data = [
            'UrlPago' => $url,
            'Id' => $id
        ];

        $consulta = $modelo->actualizarURL($id, $data);

        return json_encode($consulta);
    }

    // METODO QUE PONE EL PAGO COMO CANCELADO EL REGISTRO
    public function cancelarRegistro()
    {
        $modelo = new participantes();

        $idParticipante = $this->request->getVar('id');

        $data = [
            'StatusPago' => 'Cancelado',
            'TipoPlayera' => '',
            'Categoria' => 'Sin Categoria',
            'Cupon' => '',
            'NumeroParticipante' => ''
        ];

        $modelo->cancelarPaseDoble($idParticipante);

        $consulta = $modelo->cancelarRegistro($idParticipante, $data);

        return json_encode($consulta);
    }

    // METODO QUE ACTUALIZA UN REGISTRO
    public function actualizarRegistro()
    {
        $modelo = new participantes();

        $idParticipante = $this->request->getVar('idParticipante');
        $idOrden = $this->request->getVar('idOrden');
        $urlPago = $this->request->getVar('urlPago');
        $idPago = $this->request->getVar('idPago');

        // SE CONSULTA EL NUMERO DE PARTICIPANTE, SI EXISTE, YA NO SE ACTUALIZA NADA
        $numeroExistente = $modelo->verificarNumeroParticipante($idParticipante);

        if ($numeroExistente == 'ExisteNumeroParticipante') {
            return json_encode('YaEstaActualizado');
        }

        $data = [
            'IdPago' => $idPago,
            'IdOrden' => $idOrden,
            'UrlPago' => $urlPago,
            'StatusPago' => 'Pagado'
        ];

        // SE REGISTRA EL NUMERO DEL PARTICIPANTE
        $ultimoNumeroParticipante = $modelo->ultimoNumeroParticipante();

        if ($ultimoNumeroParticipante == 'SinNumeroParticipante') {
            $numeroParticipante = 100;
        } else {
            $numeroParticipante = $ultimoNumeroParticipante + 1;
        }

        $data["NumeroParticipante"] = $numeroParticipante;

        $consulta = $modelo->actualizarRegistro($idParticipante, $data);

        return json_encode($consulta);
    }

    // METODO QUE TRAE LA INFORMACION DEL PARTICIPANTE
    public function informacionRegistro()
    {
        $idParticipante = $this->request->getVar('id');

        $modelo = new participantes();

        $consulta = $modelo->informacionRegistro($idParticipante);

        return json_encode($consulta);
    }

    // METODO QUE CREA EL QR
    public function QR()
    {
        $img = $_POST['imgBase64'];
        $idSocio = $_POST['id'];

        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $directory = './public/uploads/VamosAncianos/' . $idSocio;
        if (is_dir($directory)) {
            return json_encode('ExisteImagen');
        } else {
            mkdir($directory, 0777, true);
            $file = $directory . '/' . $idSocio . '.png';
            $success = file_put_contents($file, $data);
            return $success ? '1' : '0';
        }
    }

    // METODO QUE ENVIA CORREO
    public function Correo()
    {
        $correo = $this->request->getVar('correo');
        $id = $this->request->getVar('id');

        $modelo  = new participantes();

        $infoRegistro = $modelo->informacionRegistro($id);

        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'no_responder@carreradelashortensias.com';
            $mail->Password   = 'C4rr3r@H0rt3nc1a5';
            $mail->SMTPSecure = "tls";
            $mail->SMTPAutoTLS = false;
            $mail->Port = 587;
            $mail->setFrom('no_responder@carreradelashortensias.com', '=?UTF-8?B?' . base64_encode('Vamos Ancianos') . '?=');
            $mail->AddAddress($correo);
            $mail->isHTML(true);



            // **************************************************************LOCALHOST**************************************************************
            // IMAGEN DE CARRERA HORTENSIAS
            $mail->AddEmbeddedImage($_SERVER['DOCUMENT_ROOT'] . '/HortensiasActualizado/public/img/purple_minimum.png', 'purple_minimum', 'purple_minimum.png');
            // IMAGEN DEL QR
            $mail->AddEmbeddedImage($_SERVER['DOCUMENT_ROOT'] . '/HortensiasActualizado/public/uploads/VamosAncianos/' . $id . '/' . $id . '.png', $id, $id . '.png');



            // **************************************************************PRODUCCION**************************************************************
            // IMAGEN DE CARRERA HORTENSIAS
            // $mail->AddEmbeddedImage($_SERVER['DOCUMENT_ROOT'] . '/public/img/purple_minimum.png', 'purple_minimum', 'purple_minimum.png');
            // IMAGEN DEL QR
            // $mail->AddEmbeddedImage($_SERVER['DOCUMENT_ROOT'] . '/public/uploads/VamosAncianos/' . $id . '/' . $id . '.png', $id, $id . '.png');

            $mail->Subject =  mb_convert_encoding('Vamos Ancianos', 'ISO-8859-1', 'UTF-8');
            $mail->Body =
                '<div align="center" style="font-family: Roboto-regular, Helvetica; color:#A3B4BB">
                <div>
                    <img src="cid:purple_minimum" width="300px">
                </div>
                <div style="margin-top: 8px; color:#8B8B8B; font-size: 24px;">
                    <strong>¡Hola!</strong>
                    <br>
                    <strong><i>¡Gracias por tú confianza!</i></strong>
                    <br>🙌
                    <br>
                    <strong>
                        ¡Tu generosidad demuestra que juntos podemos lograr grandes cosas!
                    </strong>
                    <br>
                    <br>
                        ¡Esperamos que disfrutes la experiencia y te sientas orgulloso de formar parte de esta noble causa!
                    <br>
                    <br>
                        Muestra este código <strong>QR</strong> para acceder a la competencia.<br>
                    <strong>
                        ¡Gracias por correr con el corazón!
                    </strong>
                    <br>❤️
                    <br>
                    <br>
                    <strong>¡Esperamos con ansias tu participación!</strong>
                    <br>
                    <br>
                        <strong>ID PAGO: </strong>' . $id . '
                    <br>
                        <strong>NOMBRE: </strong>' . $infoRegistro["Nombre"] . '
                    <br>
                        <strong>TALLA PLAYERA: </strong>' . $infoRegistro["TipoPlayera"] . '
                    <br>
                        <strong>CATEGORÍA: </strong>' . $infoRegistro["Categoria"] . '
                    <br>
                        <strong>MONTO TOTAL: </strong> $' . (!empty($infoRegistro["Cupon"]) ? $infoRegistro["TotalConDescuento"] : $infoRegistro["Total"]) . '
                    <br>
                    <br>
                    <div>
                        <img src="cid:' . $id . '" width="300px">
                    </div>
                </div>
            </div>';
            $mail->CharSet = 'UTF-8';
            $mail->send();
            // $this->mandarCorreoAdmin($id, $infoRegistro["Nombre"], $infoRegistro["NumeroParticipante"], $infoRegistro["Categoria"]);
            echo 'Correo exitoso: ' .  $correo . '<br>';
        } catch (Exception $e) {
            echo 'Correo fallido: ' .  $correo . '. Error: ' . $mail->ErrorInfo . '<br>';
        }
    }

    // MANDA CORREO AL ADMINISTRADOR CON LOS REGISTROS
    public function mandarCorreoAdmin($idpago, $participante, $numero,  $categoria)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'no_responder@carreradelashortensias.com';
            $mail->Password   = 'C4rr3r@H0rt3nc1a5';
            $mail->SMTPSecure = "tls";
            $mail->SMTPAutoTLS = false;
            $mail->Port = 587;
            $mail->setFrom('no_responder@carreradelashortensias.com', '=?UTF-8?B?' . base64_encode('Vamos Ancianos') . '?=');
            // $mail->AddAddress('opsaeduardo@gmail.com');
            $mail->isHTML(true);

            $mail->Subject =  mb_convert_encoding('Nuevo Registro a Vamos Ancianos', 'ISO-8859-1', 'UTF-8');
            $mail->Body =
                '<div align="center" style="font-family: Roboto-regular, Helvetica; color:#A3B4BB">
                    <div style="margin-top: 8px; color:#8B8B8B; font-size: 24px;">
                        <strong>¡Se ha realizado un nuevo registro!</strong>
                        <br><br>
                    </div>
                    <div style="margin-top: 8px; color:#8B8B8B; font-size: 24px;">
                        ' . '<strong>Id pago:</strong> ' . $idpago . '
                        <br>
                        ' . '<strong>Nombre:</strong> ' . $participante . '</strong>' . '
                        <br>
                        ' . '<strong>Número Corredor:</strong> ' . $numero . '</strong>' . '
                        <br>
                        ' . '<strong>Categoría:</strong> ' . $categoria . '</strong>' . '
                        <br>
                    </div>
                    <hr style="color:#D5D8DC; border-top: 2px; width: 27%;">
                </div>
                    ';
            $mail->CharSet = 'UTF-8';
            $mail->send();
            return 1;
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    // METODO QUE ENVIA CORREO OXXO
    public function CorreoId($id, $correo)
    {

        $modelo = new participantes();

        $infoRegistro = $modelo->informacionRegistro($id);

        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'no_responder@carreradelashortensias.com';
            $mail->Password   = 'C4rr3r@H0rt3nc1a5';
            $mail->SMTPSecure = "tls";
            $mail->SMTPAutoTLS = false;
            $mail->Port = 587;
            $mail->setFrom('no_responder@carreradelashortensias.com', '=?UTF-8?B?' . base64_encode('Vamos Ancianos') . '?=');
            $mail->AddAddress($correo);
            $mail->isHTML(true);

            // IMAGEN DE CARRERA HORTENSIAS
            $mail->AddEmbeddedImage($_SERVER['DOCUMENT_ROOT'] . '/HortensiasActualizado/public/img/purple_minimum.png', 'purple_minimum', 'purple_minimum.png');
            // $mail->AddEmbeddedImage($_SERVER['DOCUMENT_ROOT'] . '/public/img/purple_minimum.png', 'purple_minimum', 'purple_minimum.png');

            $mail->Subject =  mb_convert_encoding('Vamos Ancianos 2025', 'ISO-8859-1', 'UTF-8');
            $mail->Body =
                '<div align="center" style="font-family: Roboto-regular, Helvetica; color:#A3B4BB">
                    <div>
                        <img src="cid:purple_minimum" width="300px">
                    </div>
                    <div style="margin-top: 8px; color:#8B8B8B; font-size: 24px;">
                        <strong>¡Gracias por inscribirte!</strong>
                        <br>
                        🌺
                        <br>
                        <strong><i>Tu entusiasmo nos inspira, ¡gracias por unirte a esta experiencia!</i></strong>
                        <br><br>
                        Tu pago está siendo procesado. Una vez confirmado, recibirás un correo con toda la información para acceder a la competencia.
                        <br><br>
                        <strong>¡No te preocupes, estás a un paso de la carrera! 🏁</strong>
                        <br>
                        <br>
                        Mientras tanto, asegúrate de mantenerte al tanto de nuestras novedades, y prepárate para un gran evento.
                        <br><br>
                        <strong>Detalles de tu inscripción:</strong>
                        <br>
                        <strong>ID INSCRIPCIÓN: </strong>' . $id . '
                        <br>
                        <strong>¡Pronto recibirás más noticias para unirte al evento! 🚀</strong>
                        <br><br>
                        <br>
                    </div>
                </div>
            </div>';
            $mail->CharSet = 'UTF-8';
            $mail->send();
            $this->mandarCorreoAdmin($id, $infoRegistro["Nombre"], $infoRegistro["NumeroParticipante"], $infoRegistro["Categoria"]);
            $response = [
                'status' => 'success',
                'message' => 'Correo enviado exitosamente',
                'correo_exitoso' => $correo
            ];
            return $response;
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'message' => 'Error al enviar el correo',
                'correo_fallido' => $correo,
                'error' => $mail->ErrorInfo
            ];
            return $response;
        }
    }

    // METODO QUE ENVIA CORREO OXXO
    public function CorreoConfimracion($id, $correo)
    {

        $modelo = new participantes();

        $infoRegistro = $modelo->informacionRegistro($id);

        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'no_responder@carreradelashortensias.com';
            $mail->Password   = 'C4rr3r@H0rt3nc1a5';
            $mail->SMTPSecure = "tls";
            $mail->SMTPAutoTLS = false;
            $mail->Port = 587;
            $mail->setFrom('no_responder@carreradelashortensias.com', '=?UTF-8?B?' . base64_encode('Vamos Ancianos') . '?=');
            $mail->AddAddress($correo);
            $mail->isHTML(true);

            // IMAGEN DE CARRERA HORTENSIAS
            $mail->AddEmbeddedImage($_SERVER['DOCUMENT_ROOT'] . '/public/img/purple_minimum.png', 'purple_minimum', 'purple_minimum.png');

            $mail->Subject =  mb_convert_encoding('Vamos Ancianos 2025', 'ISO-8859-1', 'UTF-8');
            $mail->Body =
                '<div align="center" style="font-family: Roboto-regular, Helvetica; color:#A3B4BB">
                    <div>
                        <img src="cid:purple_minimum" width="300px">
                    </div>
                    <div style="margin-top: 8px; color:#8B8B8B; font-size: 24px;">
                        <strong>¡Felicidades, tu pago ha sido confirmado!</strong>
                        <br>
                        🌸
                        <br>
                        <strong><i>¡Gracias por tu paciencia y confianza!</i></strong>
                        <br><br>
                        Tu inscripción ha sido completada exitosamente, y ahora estás listo para unirte oficialmente a la competencia.
                        <br><br>
                        <strong>¡Estás a un paso de la meta! 🏁</strong>
                        <br>
                        Muestra el <strong>Id de inscripción</strong> en el evento para acceder a la competencia.
                        <br><br>
                        <strong>Detalles de tu inscripción:</strong>
                        <br>
                        <strong>ID INSCRIPCIÓN: </strong>' . $id . '
                        <br>
                        <strong>NOMBRE: </strong>' . $infoRegistro["Nombre"] . '
                        <br>
                        <strong>TALLA PLAYERA: </strong>' . $infoRegistro["TipoPlayera"] . '
                        <br>
                        <strong>NO. CORREDOR: </strong>' . $infoRegistro["NumeroParticipante"] . '
                        <br>
                        <strong>KEYFOB: </strong>' . (!empty($infoRegistro["KeyFob"]) ? $infoRegistro["KeyFob"] : "No Aplica") . '
                        <br>
                        <strong>CATEGORÍA: </strong>' . $infoRegistro["Categoria"] . '
                        <br>
                        <strong>MONTO TOTAL: </strong> $' . (!empty($infoRegistro["Cupon"]) ? $infoRegistro["TotalConDescuento"] : $infoRegistro["Total"]) . '
                        <br><br>
                        <strong>¡Buena suerte en tu recorrido hacia la victoria! 🚀</strong>
                        <br><br>
                        <strong>¡Nos vemos en la línea de salida!</strong>

                    </div>
                </div>
            </div>';

            $mail->CharSet = 'UTF-8';
            $mail->send();
            $this->mandarCorreoAdmin($id, $infoRegistro["Nombre"], $infoRegistro["NumeroParticipante"], $infoRegistro["Categoria"]);
            echo 'Correo exitoso: ' .  $correo . '<br>';
        } catch (Exception $e) {
            echo 'Correo fallido: ' .  $correo . '. Error: ' . $mail->ErrorInfo . '<br>';
        }
    }

    public function clientes()
    {
        $model = new participantes();
        $clientes = $model->getClientes();
        return json_encode($clientes);
    }

// 2) ENCRIPTAR  ─────────────────────────────────────────────
private function encryptId(string $id): string
{
    $key = self::ENCRYPTEQR_SECRET_KEY;
    $iv  = substr(hash('sha256', self::ENCRYPTEQR_SECRET_IV), 0, 16);

    $b64 = openssl_encrypt($id, self::ENCRYPTEQR_METHOD, $key, 0, $iv);

    // convierte base64 → base64url  (+/  → -_   y sin “=”)
    return rtrim(strtr($b64, '+/', '-_'), '=');
}

// 3) DESENCRIPTAR (base64url → base64 → texto plano) ───────
private function decryptId(string $token): ?string
{
    $key = self::ENCRYPTEQR_SECRET_KEY;
    $iv  = substr(hash('sha256', self::ENCRYPTEQR_SECRET_IV), 0, 16);

    $token = strtr($token, '-_', '+/');
    $pad   = 4 - (strlen($token) % 4);
    if ($pad < 4) $token .= str_repeat('=', $pad);

    $plain = openssl_decrypt($token, self::ENCRYPTEQR_METHOD, $key, 0, $iv);

    return $plain === false ? null : $plain;
}


 // 4) ENDPOINT AJAX QUE DEVUELVE EL TOKEN CIFRADO
public function encriptarId()
{
    $id = $this->request->getPost('id');
    log_message('info', "ENCRYPTEQR → recibí id a encriptar: {$id}");
    $token = $this->encryptId($id);
    log_message('info', "ENCRYPTEQR → token generado: {$token}");
    return $this->response->setJSON(['token' => $token]);
}



}
