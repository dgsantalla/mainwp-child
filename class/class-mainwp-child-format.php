<?php
/**
 * MainWP Child Format
 *
 * @package MainWP/Child
 */

namespace MainWP\Child;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class MainWP_Child_Format
 *
 * MainWP Child Format
 */
class MainWP_Child_Format {

    /**
     * Public static variable to hold the single instance of the class.
     *
     * @var mixed Default null
     */
    public static $instance = null;

    /**
     * Method get_class_name()
     *
     * Get class name.
     *
     * @return string __CLASS__ Class name.
     */
    public static function get_class_name() {
        return __CLASS__;
    }

    /**
     * Method instance()
     *
     * Create a public static instance.
     *
     * @return mixed Class instance.
     */
    public static function instance() {
        if ( null === static::$instance ) {
            static::$instance = new self();
        }
        return static::$instance;
    }


    /**
     * Method format_email()
     *
     * Format emails.
     *
     * @param string $body Contains the email content.
     *
     * @return string Return formatted email.
     */
    public static function format_email( $body ) {
        // La plantilla original venia enteramente marcada con MainWP: logotipo,
        // tres enlaces a mainwp.com / community.mainwp.com / docs.mainwp.com,
        // un "Hello MainWP User!" y un "(c) 2013 MainWP. All Rights Reserved.".
        // Estos correos llegan al buzon del cliente final de la agencia y
        // quedan archivados, asi que la fuga es peor que en pantalla: no se
        // puede cerrar y no caduca.
        //
        // Spec: docs/superpowers/specs/2026-09-18-fuga-marca-conector-design.md
        $marca = MainWP_Child_Branding::instance()->get_branding_title();
        $marca = '' !== $marca ? $marca : 'TutorWP';
        $marca = esc_html( $marca );

        $sitio = esc_html( get_bloginfo( 'name' ) );

        return '<div style="background:#f4f4f4;padding:24px 0;font-family:Helvetica,Arial,sans-serif;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e5e5;">
            <span style="font-size:20px;font-weight:600;color:#213343;">' . $marca . '</span>
        </div>
        <div style="padding:24px;font-size:14px;line-height:1.7;color:#4C6178;">
            ' . $body . '
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e5e5e5;font-size:12px;line-height:1.6;color:#8199AC;">
            ' . sprintf(
                /* translators: 1: nombre de marca, 2: nombre del sitio. */
                esc_html__( 'Este aviso lo envía %1$s desde tu sitio %2$s.', 'mainwp-child' ),
                $marca,
                $sitio
            ) . '
        </div>
    </div>
</div>';
    }

}
