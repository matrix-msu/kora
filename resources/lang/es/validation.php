<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'accepted'             => 'El :attribute se debe aceptar.',
    'active_url'           => 'El :attribute no es URL v&aacute;lido.',
    'after'                => 'El :attribute debe ser una fecha despu&eacute;s :date.',
    'alpha'                => 'El :attribute mayo s&oacute;lo contiene cartas.',
    'alpha_dash'           => 'El :attribute mayo s&oacute;lo contiene cartas, n&uacute;meros y carreras.',
    'alpha_num'            => 'El :attribute mayo s&oacute;lo contiene cartas y n&uacute;meros.',
    'array'                => 'El :attribute debe ser una serie.',
    'before'               => 'El :attribute debe ser una fecha antes :date.',
    'between'              => [
        'numeric' => 'El :attribute debe estar entre el :min y :max.',
        'file'    => 'El :attribute debe estar entre :min y :max kilobytes.',
        'string'  => 'El :attribute debe estar entre :min y :max caracteres.',
        'array'   => 'El :attribute debe tener entre :min y :max art&iacute;culos.',
    ],
    'boolean'              => 'El :attribute el campo debe ser verdad o debe ser falso.',
    'confirmed'            => 'El :attribute la confirmaci&oacute;n no hace juego.',
    'date'                 => 'El :attribute no es una fecha v&aacute;lida.',
    'date_format'          => 'El :attribute no corresponde al :format del formato.',
    'different'            => 'El :attribute y :other debe ser diferente.',
    'digits'               => 'El :attribute debe ser :digits dígitos.',
    'digits_between'       => 'El :attribute debe estar entre :min y :max dígitos.',
    'email'                => 'El :attribute debe ser una direcci&oacute;n de correo electr&oacute;nico v&aacute;lida.',
    'exists'               => ':attribute seleccionado es inv&aacute;lido.',
    'filled'               => 'El :attribute el campo se requiere.',
    'image'                => 'El :attribute debe ser una imagen.',
    'in'                   => ':attribute seleccionado es inv&aacute;lido.',
    'integer'              => 'El :attribute debe ser un n&uacute;mero entero.',
    'ip'                   => 'El :attribute debe ser una Direcci&oacute;n IP v&aacute;lida.',
    'json'                 => 'El :attribute debe ser una cuerda de JSON v&aacute;lida.',
    'max'                  => [
        'numeric' => 'El :attribute puede no ser mayor que :max.',
        'file'    => 'El :attribute puede no ser mayor que :max kilobytes.',
        'string'  => 'El :attribute puede no ser mayor que :max caracteres.',
        'array'   => 'El :attribute puede no tener m&aacute;s que :max art&iacute;culos.',
    ],
    'mimes'                => 'El :attribute debe ser un archivo de tipo: :values.',
    'min'                  => [
        'numeric' => 'El :attribute debe ser al menos :min.',
        'file'    => 'El :attribute debe ser al menos :min kilobytes.',
        'string'  => 'El :attribute debe ser al menos :min caracteres.',
        'array'   => 'El :attribute debe tener al menos :min art&iacute;culos.',
    ],
    'not_in'               => ':attribute seleccionado es inv&aacute;lido.',
    'numeric'              => 'El :attribute debe ser un n&uacute;mero.',
    'regex'                => 'El :attribute el formato es inv&aacute;lido.',
    'required'             => 'El :attribute el campo se requiere.',
    'required_if'          => 'El :attribute el campo se requiere cuando el :other es :value.',
    'required_unless'      => 'El :attribute el campo se requiere a menos que el :other est&eacute; en :values.',
    'required_with'        => 'El :attribute el campo se requiere cuando el :values est&aacute; presente.',
    'required_with_all'    => 'El :attribute el campo se requiere cuando el :values est&aacute; presente.',
    'required_without'     => 'El :attribute el campo se requiere cuando el :values no est&aacute; presente.',
    'required_without_all' => 'El :attribute el campo se requiere cuando el ninguno de :values est&aacute; presente.',
    'same'                 => 'El :attribute y :other deben hacer juego.',
    'size'                 => [
        'numeric' => 'El :attribute debe ser :size.',
        'file'    => 'El :attribute debe ser :size kilobytes.',
        'string'  => 'El :attribute debe ser :size caracteres.',
        'array'   => 'El :attribute debe contener :size art&iacute;culos.',
    ],
    'string'               => 'El :attribute debe ser una cuerda.',
    'timezone'             => 'El :attribute debe ser una zona v&aacute;lida.',
    'unique'               => 'El :attribute se ha tomado ya.',
    'url'                  => 'El :attribute el formato es inv&aacute;lido.',
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'attributes' => [],
];