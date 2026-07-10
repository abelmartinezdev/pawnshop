<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IVA
    |--------------------------------------------------------------------------
    */

    'iva_rate' => 0.16,

    /*
    |--------------------------------------------------------------------------
    | Motivos de cancelación de empeños
    |--------------------------------------------------------------------------
    */

    'cancellation_types' => [
        'capture_error' => 'Error de captura',
        'wrong_customer' => 'Cliente incorrecto',
        'wrong_amount' => 'Monto incorrecto',
        'wrong_item' => 'Prenda incorrecta',
        'duplicate' => 'Registro duplicado',
        'client_request' => 'Solicitud del cliente',
        'manager_authorization' => 'Autorización de gerencia',
        'investigation' => 'Investigación',
        'other' => 'Otro',
    ],

];