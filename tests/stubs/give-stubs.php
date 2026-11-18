<?php
/**
 * Stubs do GiveWP para os testes integrados.
 *
 * O GiveWP (4.x) não é carregado nos testes: ele traz as próprias tabelas e o
 * próprio bootstrap de testes, o que tornaria a suíte lenta e frágil. Em vez
 * disso, aqui reproduzimos a superfície **mínima** da API do Give usada pelo
 * plugin (funções globais `give_*` e as classes `Give\...`), com um store em
 * memória que os testes inspecionam e reinicializam.
 *
 * ATENÇÃO: este arquivo é carregado no hook `muplugins_loaded`, ANTES do plugin
 * — por isso as funções/classes existem quando o plugin é incluído.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace {

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Store em memória que substitui o banco de dados do GiveWP.
 */
final class Lkngc_Give_Stub_Store {
    /** @var array<string, mixed> Opções do Give (give_get_option). */
    public static $options = array();

    /** @var array<string, mixed> Array completo de settings (give_get_settings). */
    public static $settings = array();

    /** @var array<int, string> Chaves apagadas por give_delete_option(). */
    public static $deleted_options = array();

    /** @var array<int, array<string, mixed>> Meta gravada por give_update_payment_meta(). */
    public static $meta = array();

    /** @var array<int, array<string, mixed>> Logs registrados em Give\Log\Log::error(). */
    public static $logs = array();

    /** @var array<int, \Give\Donations\Models\Donation> Doações "persistidas". */
    public static $donations = array();

    /** @var array<int, array<string, mixed>> Notas criadas por DonationNote::create(). */
    public static $notes = array();

    /** @var array<int, mixed> Erros devolvidos por give_get_errors(). */
    public static $errors = array();

    /** @var array<int, array<string, mixed>> Args das chamadas a give_send_to_success_page(). */
    public static $success_pages = array();

    /** @var array<int, string> Status aplicados por give_update_payment_status(). */
    public static $status_updates = array();

    /** @var string Seção atual das configurações do Give. */
    public static $current_section = '';

    /** @var array<int, string> Template do formulário (`_give_form_template`) por form ID. */
    public static $form_templates = array();

    /**
     * Volta o store ao estado inicial (chamado no setUp de cada teste).
     */
    public static function reset(): void {
        self::$options          = array();
        self::$settings         = array();
        self::$deleted_options  = array();
        self::$meta             = array();
        self::$logs             = array();
        self::$donations        = array();
        self::$notes            = array();
        self::$errors           = array();
        self::$success_pages    = array();
        self::$status_updates   = array();
        self::$current_section  = '';
        self::$form_templates   = array();
    }
}

/* ------------------------------------------------------------------------- */
/* Funções globais `give_*` usadas pelo plugin.                              */
/* ------------------------------------------------------------------------- */

if ( ! function_exists( 'give_get_option' ) ) {
    /**
     * @param string $key     Chave da opção.
     * @param mixed  $default Valor padrão.
     *
     * @return mixed
     */
    function give_get_option( $key = '', $default = false ) {
        if ( array_key_exists( $key, Lkngc_Give_Stub_Store::$options ) ) {
            return Lkngc_Give_Stub_Store::$options[ $key ];
        }

        return $default;
    }
}

if ( ! function_exists( 'give_update_option' ) ) {
    /**
     * @param string $key   Chave da opção.
     * @param mixed  $value Valor.
     *
     * @return bool
     */
    function give_update_option( $key, $value ) {
        Lkngc_Give_Stub_Store::$options[ $key ] = $value;

        return true;
    }
}

if ( ! function_exists( 'give_delete_option' ) ) {
    /**
     * @param string $key Chave da opção.
     *
     * @return bool
     */
    function give_delete_option( $key ) {
        Lkngc_Give_Stub_Store::$deleted_options[] = $key;
        unset( Lkngc_Give_Stub_Store::$options[ $key ] );

        return true;
    }
}

if ( ! function_exists( 'give_get_settings' ) ) {
    /**
     * @return array<string, mixed>
     */
    function give_get_settings() {
        return Lkngc_Give_Stub_Store::$settings;
    }
}

if ( ! function_exists( 'give_get_payment_meta' ) ) {
    /**
     * @param int    $payment_id ID da doação.
     * @param string $key        Chave da meta.
     * @param bool   $single     Se deve retornar um valor único.
     *
     * @return mixed
     */
    function give_get_payment_meta( $payment_id = 0, $key = '', $single = false ) {
        return give_get_meta( $payment_id, $key, $single );
    }
}

if ( ! function_exists( 'give_get_meta' ) ) {
    /**
     * @param int    $payment_id ID da doação.
     * @param string $key        Chave da meta.
     * @param bool   $single     Se deve retornar um valor único.
     *
     * @return mixed
     */
    function give_get_meta( $payment_id = 0, $key = '', $single = false ) {
        $meta = isset( Lkngc_Give_Stub_Store::$meta[ (int) $payment_id ][ $key ] )
            ? Lkngc_Give_Stub_Store::$meta[ (int) $payment_id ][ $key ]
            : '';

        return $meta;
    }
}

if ( ! function_exists( 'give_update_payment_meta' ) ) {
    /**
     * @param int    $payment_id ID da doação.
     * @param string $key        Chave da meta.
     * @param mixed  $value      Valor.
     *
     * @return bool
     */
    function give_update_payment_meta( $payment_id, $key = '', $value = '' ) {
        Lkngc_Give_Stub_Store::$meta[ (int) $payment_id ][ $key ] = $value;

        return true;
    }
}

if ( ! function_exists( 'give_update_payment_status' ) ) {
    /**
     * @param int    $payment_id ID da doação.
     * @param string $status     Novo status.
     *
     * @return bool
     */
    function give_update_payment_status( $payment_id, $status = 'publish' ) {
        Lkngc_Give_Stub_Store::$status_updates[] = array(
            'donation_id' => (int) $payment_id,
            'status'      => $status,
        );

        return true;
    }
}

if ( ! function_exists( 'give_send_to_success_page' ) ) {
    /**
     * @param array<string, mixed> $args Argumentos de redirecionamento.
     *
     * @return bool
     */
    function give_send_to_success_page( $args = array() ) {
        Lkngc_Give_Stub_Store::$success_pages[] = $args;

        return true;
    }
}

if ( ! function_exists( 'give_clear_errors' ) ) {
    /**
     * @return void
     */
    function give_clear_errors() {
        Lkngc_Give_Stub_Store::$errors = array();
    }
}

if ( ! function_exists( 'give_get_errors' ) ) {
    /**
     * @return array<int, mixed>
     */
    function give_get_errors() {
        return Lkngc_Give_Stub_Store::$errors;
    }
}

if ( ! function_exists( 'give_get_current_setting_section' ) ) {
    /**
     * @return string
     */
    function give_get_current_setting_section() {
        return Lkngc_Give_Stub_Store::$current_section;
    }
}

/* ------------------------------------------------------------------------- */
/* Classes `Give\...` usadas pelo plugin.                                    */
/* ------------------------------------------------------------------------- */

}

namespace Give\Log {

    if ( ! class_exists( __NAMESPACE__ . '\Log' ) ) {
        /**
         * Stub de `Give\Log\Log`.
         */
        final class Log {
            /**
             * @param string               $message Mensagem.
             * @param array<string, mixed> $context Contexto.
             *
             * @return void
             */
            public static function error( $message, array $context = array() ) {
                \Lkngc_Give_Stub_Store::$logs[] = array(
                    'message' => $message,
                    'context' => $context,
                );
            }
        }
    }
}

namespace Give\Framework\Exceptions\Primitives {

    if ( ! class_exists( __NAMESPACE__ . '\Exception' ) ) {
        /**
         * Stub de `Give\Framework\Exceptions\Primitives\Exception`.
         */
        class Exception extends \Exception {
        }
    }
}

namespace Give\Framework\PaymentGateways\Exceptions {

    if ( ! class_exists( __NAMESPACE__ . '\PaymentGatewayException' ) ) {
        /**
         * Stub de `Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException`.
         */
        class PaymentGatewayException extends \Give\Framework\Exceptions\Primitives\Exception {
        }
    }
}

namespace Give\Framework\PaymentGateways\Commands {

    if ( ! interface_exists( __NAMESPACE__ . '\GatewayCommand' ) ) {
        /**
         * Stub da interface `GatewayCommand`.
         */
        interface GatewayCommand {
        }
    }

    if ( ! class_exists( __NAMESPACE__ . '\PaymentComplete' ) ) {
        /**
         * Stub do comando `PaymentComplete`.
         */
        class PaymentComplete implements GatewayCommand {
        }
    }

    if ( ! class_exists( __NAMESPACE__ . '\PaymentPending' ) ) {
        /**
         * Stub do comando `PaymentPending`.
         */
        class PaymentPending implements GatewayCommand {
        }
    }

    if ( ! class_exists( __NAMESPACE__ . '\PaymentRefunded' ) ) {
        /**
         * Stub do comando `PaymentRefunded`.
         */
        class PaymentRefunded implements GatewayCommand {
        }
    }
}

namespace Give\Framework\PaymentGateways {

    if ( ! class_exists( __NAMESPACE__ . '\PaymentGateway' ) ) {
        /**
         * Stub de `Give\Framework\PaymentGateways\PaymentGateway`.
         *
         * Deliberadamente vazio: o contrato real do Give não é reproduzido aqui,
         * apenas o suficiente para instanciar os gateways do plugin.
         */
        abstract class PaymentGateway {
        }
    }
}

namespace Give\Donations\ValueObjects {

    if ( ! class_exists( __NAMESPACE__ . '\DonationStatus' ) ) {
        /**
         * Stub do ValueObject `DonationStatus`.
         */
        final class DonationStatus {
            /** @var string */
            private $value;

            /**
             * @param string $value Valor do status.
             */
            private function __construct( $value ) {
                $this->value = $value;
            }

            /**
             * @return self
             */
            public static function COMPLETE() {
                return new self( 'complete' );
            }

            /**
             * @return self
             */
            public static function PENDING() {
                return new self( 'pending' );
            }

            /**
             * @return self
             */
            public static function FAILED() {
                return new self( 'failed' );
            }

            /**
             * @return string
             */
            public function getValue() {
                return $this->value;
            }

            /**
             * @return string
             */
            public function __toString() {
                return $this->value;
            }
        }
    }
}

namespace Give\Donations\Models {

    if ( ! class_exists( __NAMESPACE__ . '\DonationNote' ) ) {
        /**
         * Stub de `Give\Donations\Models\DonationNote`.
         */
        final class DonationNote {
            /**
             * @param array<string, mixed> $attributes Atributos da nota.
             *
             * @return self
             */
            public static function create( array $attributes = array() ) {
                \Lkngc_Give_Stub_Store::$notes[] = $attributes;

                return new self();
            }
        }
    }

    if ( ! class_exists( __NAMESPACE__ . '\Donation' ) ) {
        /**
         * Stub de `Give\Donations\Models\Donation`.
         */
        class Donation {
            /** @var int */
            public $id = 0;

            /** @var mixed Status (ValueObject `DonationStatus`). */
            public $status;

            /** @var string */
            public $gatewayId = '';

            /** @var bool Se `save()` foi chamado. */
            public $saved = false;

            /**
             * @param int $id ID da doação.
             */
            public function __construct( $id = 0 ) {
                $this->id = (int) $id;
            }

            /**
             * @param int $id ID da doação.
             *
             * @return self|null
             */
            public static function find( $id ) {
                return isset( \Lkngc_Give_Stub_Store::$donations[ (int) $id ] )
                    ? \Lkngc_Give_Stub_Store::$donations[ (int) $id ]
                    : null;
            }

            /**
             * @return bool
             */
            public function save() {
                $this->saved = true;
                \Lkngc_Give_Stub_Store::$donations[ $this->id ] = $this;

                return true;
            }

            /**
             * @return array<string, mixed>
             */
            public function toArray() {
                return array(
                    'id'     => $this->id,
                    'status' => (string) $this->status,
                );
            }
        }
    }
}

namespace {

    if ( ! class_exists( 'Give_DB_Form_Meta' ) ) {
        /**
         * Stub de `Give_DB_Form_Meta` (usado para ler `_give_form_template`).
         */
        class Give_DB_Form_Meta {
            /** @var string */
            public $table_name = '';

            /**
             * @param array<string, mixed> $args Filtros (`form_id`, `meta_key`).
             *
             * @return array<int, object>
             */
            public function get_results_by( $args = array() ) {
                $form_id  = isset( $args['form_id'] ) ? (int) $args['form_id'] : 0;

                // `null` simula um formulário sem nenhuma linha de meta.
                if ( array_key_exists( $form_id, Lkngc_Give_Stub_Store::$form_templates )
                    && null === Lkngc_Give_Stub_Store::$form_templates[ $form_id ] ) {
                    return array();
                }

                $template = isset( Lkngc_Give_Stub_Store::$form_templates[ $form_id ] )
                    ? Lkngc_Give_Stub_Store::$form_templates[ $form_id ]
                    : 'legacy';

                return array(
                    (object) array(
                        'form_id'    => $form_id,
                        'meta_key'   => isset( $args['meta_key'] ) ? $args['meta_key'] : '',
                        'meta_value' => $template,
                    ),
                );
            }
        }
    }
}
