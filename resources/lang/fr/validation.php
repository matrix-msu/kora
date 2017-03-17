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
    'accepted'             => 'Le :attribute doit &ecirc;tre accept&eacute;.',
    'active_url'           => "Le :attribute n'est pas un URL valide.",
    'after'                => 'Le :attribute doit &ecirc;tre une date ensuite :date.',
    'alpha'                => 'Le :attribute peut seulement contenir des lettres.',
    'alpha_dash'           => 'Le :attribute peut seulement contenir des lettres, des nombres, et des tirets.',
    'alpha_num'            => 'Le :attribute peut seulement contenir des lettres et des nombre.',
    'array'                => 'Le :attribute doit &ecirc;tre une rang&eacute;e.',
    'before'               => 'Le :attribute doit &ecirc;tre une date avant :date.',
    'between'              => [
        'numeric' => 'Le :attribute doit &ecirc;tre entre :min et :max.',
        'file'    => 'Le :attribute doit &ecirc;tre entre :min et :max kilobytes.',
        'string'  => 'Le :attribute doit &ecirc;tre entre :min et :max caract&egrave;res.',
        'array'   => 'Le :attribute doit avoir entre :min et :max articles.',
    ],
    'boolean'              => 'Le :attribute champ doit &ecirc;tre vrai ou faux.',
    'confirmed'            => "Le :attribute la confirmation ne s'assortit pas.",
    'date'                 => "Le :attribute n'est pas une date valide.",
    'date_format'          => "Le :attribute n'assortit pas le :format de format.",
    'different'            => 'Le :attribute et :other doivent &ecirc;tre diff&eacute;rents.',
    'digits'               => 'Le :attribute doivent &ecirc;tre les chiffres :digits.',
    'digits_between'       => 'Le :attribute doit &ecirc;tre entre :min et :max chiffres.',
    'email'                => 'Le :attribute doit &ecirc;tre une adresse email valide.',
    'exists'               => 'Le :attribute choisi est invalide.',
    'filled'               => 'Le :attribute champ est exig&eacute;.',
    'image'                => 'Le :attribute doit &ecirc;tre une image.',
    'in'                   => 'Le :attribute choisi est invalide.',
    'integer'              => 'Le :attribute doit &ecirc;tre un nombre entier.',
    'ip'                   => 'Le :attribute doit &ecirc;tre un IP address valide.',
    'json'                 => 'Le :attribute doit &ecirc;tre une ficelle valide de JSON.',
    'max'                  => [
        'numeric' => 'Le :attribute ne peut pas &ecirc;tre plus grand que :max.',
        'file'    => 'Le :attribute ne peut pas &ecirc;tre plus grand que :max kilobytes.',
        'string'  => 'Le :attribute ne peut pas &ecirc;tre plus grand que :max caract&egrave;res.',
        'array'   => 'Le :attribute ne peut pas avoir plus que :max articles.',
    ],
    'mimes'                => 'Le :attribute doit &ecirc;tre un dossier de type: :values.',
    'min'                  => [
        'numeric' => 'Le :attribute doit &ecirc;tre au moins :min.',
        'file'    => 'Le :attribute doit &ecirc;tre au moins :min kilobytes.',
        'string'  => 'Le :attribute doit &ecirc;tre au moins :min caract&egrave;res.',
        'array'   => 'Le :attribute doit avoir au moins :min articles.',
    ],
    'not_in'               => 'Le :attribute choisi est invalide.',
    'numeric'              => 'Le :attribute doit &ecirc;tre un nombre.',
    'regex'                => 'Le :attribute format est invalide.',
    'required'             => 'Le :attribute champ est exig&eacute;.',
    'required_if'          => 'Le :attribute champ est exig&eacute; quand :other est :value.',
    'required_unless'      => 'Le :attribute champ est exig&eacute; &agrave; moins que :other est dedans :values.',
    'required_with'        => 'Le :attribute champ est exig&eacute; quand :values est pr&eacute;sent.',
    'required_with_all'    => 'Le :attribute champ est exig&eacute; quand :values est pr&eacute;sent.',
    'required_without'     => "Le :attribute champ est exig&eacute; quand :values n'est pas pr&eacute;sent.",
    'required_without_all' => 'Le :attribute champ est exig&eacute; quand aucun de :values soyez pr&eacute;sent.',
    'same'                 => "Le :attribute et :other doivent s'assortir.",
    'size'                 => [
        'numeric' => 'Le :attribute doit &ecirc;tre :size.',
        'file'    => 'Le :attribute doit &ecirc;tre :size kilobytes.',
        'string'  => 'Le :attribute doit &ecirc;tre :size caract&egrave;res.',
        'array'   => 'Le :attribute doit contenir :size articles.',
    ],
    'string'               => 'Le :attribute doit &ecirc;tre une ficelle.',
    'timezone'             => 'Le :attribute doit &ecirc;tre une zone valide.',
    'unique'               => 'Le :attribute a &eacute;t&eacute; d&eacute;j&agrave; pris.',
    'url'                  => 'Le :attribute format est invalide.',
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