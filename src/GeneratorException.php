<?php

namespace Codemax\Generator;

class GeneratorException extends \Exception {

    /**
     * The exception description.
     *
     * @var string
     */
    protected $message = 'Não foi possível determinar o que você está tentando fazer. Desculpa! Verifique seu nome de migração.';

}