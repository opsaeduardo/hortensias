<?php

namespace App\Controllers;

use function App\Helpers\encrypt_qr_id;
use function App\Helpers\decrypt_qr_id;
use App\Controllers\BaseController;
use App\Models\Participantes;
use CodeIgniter\BaseModel;
use Stripe\StripeClient;


require 'vendor/autoload.php';

class Stripe extends BaseController
{
    public function createSession()
    {

        helper('qr');

        $id = $this->request->getVar('id');
        $token  = encrypt_qr_id((string) $id);
        $tipoPago = $this->request->getVar('tipoPago');
        $monto = $this->request->getVar('monto');
        // $gymId = $this->request->getVar('gymId');
        // $descripcion = $this->request->getVar('descripcion');
        // $correo = $this->request->getVar('correo');
        // $nombre = $this->request->getVar('nombre');
        // $apellido = $this->request->getVar('apellido');
        // $sexo = $this->request->getVar('sexo');
        // $talla = $this->request->getVar('talla');
        // $edad = $this->request->getVar('edad');

        //return json_encode("desde controller stripe: " . $gymId);

        $stripe = new StripeClient([
            // PRODUCCION
            "api_key" => env('SECRET_KEY'),
        ]);

        $checkout_session = $stripe->checkout->sessions->create([
            //'payment_method_types' => ['card', 'oxxo'],
            'payment_method_types' => [$tipoPago],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'MXN',
                    'product_data' => [
                        'name' => 'VAMOS ANCIANOS',
                    ],
                    'unit_amount' => $monto,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'ui_mode' => 'embedded',
            'return_url' => base_url() . "?StripePasarela_ID={CHECKOUT_SESSION_ID}&idSocio={$token}",
            // 'return_url' => base_url() . "?StripePasarela_ID={CHECKOUT_SESSION_ID}&idSocio=$id&descripcion=$descripcion&correo=$correo&talla=$talla&edad=$edad",
            'metadata' => [
                'id' => $id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'id' => $id,
                ]
            ]
        ]);
        echo json_encode(array('clientSecret' => $checkout_session->client_secret));
    }

    public function estadoStripe()
    {
        try {
            $stripe = new StripeClient([
                "api_key" => env('SECRET_KEY'),
            ]);
            $id = $this->request->getVar('id');
            $session = $stripe->checkout->sessions->retrieve($id);
            echo json_encode([
                $session

            ]);
            http_response_code(200);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function createPaymentLink()
    {
        helper('qr');
        $modeloParticipantes = new Participantes();
        $monto = $this->request->getVar('monto');
        $id = $this->request->getVar('id');
        $token = encrypt_qr_id((string) $id);

        // Crear una instancia del cliente de Stripe
        $stripe = new StripeClient([
            "api_key" => env('SECRET_KEY'),
        ]);


        $price = $stripe->prices->create([
            'unit_amount' => $monto,
            'currency' => 'mxn',
            'product_data' => [
                'name' => 'VAMOS ANCIANOS',
            ],
        ]);

        // Crear un enlace de pago en Stripe (con metadata para el id del participante)
        $paymentLinkStripe = $stripe->paymentLinks->create([
            'line_items' => [
                [
                    'price' => $price->id,
                    'quantity' => 1,
                ],
            ],
            'metadata' => [
                'id' => $id,
                'puntoventa' => 'true',
            ],
            'restrictions' => ['completed_sessions' => ['limit' => 1]],
            'inactive_message' => 'Lo sentimos, el enlace de pago ya no es válido.',
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => [
                    'url' => base_url("?idSocioLink={$token}"),
                ],
            ],
        ]);

        /* QUEDA PENDIENTE EL ATRAPAR IDSOCIOLINK PARA PODRR ACTUALIZAR URL DE PAGO */
        echo json_encode([
            'paymentLink' => $paymentLinkStripe->url,
            'paymentLinkId' => $paymentLinkStripe->id,
        ]);
    }

    public function deactivatePaymentLink()
    {

        $paymentLinkId = $this->request->getVar('paymentLinkId');

        try {

            $stripe = new \Stripe\StripeClient(env('SECRET_KEY'));

            // Desactivar el enlace de pago
            $updatedLink = $stripe->paymentLinks->update(
                $paymentLinkId,
                ['active' => false]
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'El enlace de pago se desactivó correctamente.',
                'link' => $updatedLink
            ]);
        } catch (\Exception $e) {

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al desactivar el enlace de pago: ' . $e->getMessage()
            ]);
        }
    }
}
