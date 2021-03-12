<?php


namespace JFBCore\JetFormBuilder;


use Jet_Form_Builder\Form_Handler;

abstract class PreventFormSubmit {

	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'manage_hooks' ) );
	}

	public function manage_hooks() {
		$handler = jet_form_builder()->form_handler;

		if ( wp_doing_ajax() ) {
			remove_action(
				'wp_ajax_' . $handler->hook_key,
				array( $handler, 'process_ajax_form' )
			);

			remove_action(
				'wp_ajax_nopriv_' . $handler->hook_key,
				array( $handler, 'process_ajax_form' )
			);
			add_action(
				'wp_ajax_' . $handler->hook_key,
				array( $this, '_prevent_ajax_submit' ), 0
			);
			add_action(
				'wp_ajax_nopriv_' . $handler->hook_key,
				array( $this, '_prevent_ajax_submit' ), 0
			);

			return;
		}

		remove_action(
			'wp_loaded',
			array( $handler, 'process_form' ), 0
		);
		add_action(
			'wp_loaded',
			array( $this, '_prevent_reload_submit' )
		);
	}

	abstract public function prevent_process_ajax_form( Form_Handler $handler );

	abstract public function prevent_process_reload_form( Form_Handler $handler );

	public function _prevent_ajax_submit() {
		$handler = jet_form_builder()->form_handler;

		$handler->is_ajax = true;
		$handler->setup_form();

		$this->prevent_process_ajax_form( $handler );
	}

	public function _prevent_reload_submit() {
		$handler = jet_form_builder()->form_handler;
		$handler->setup_form();

		$this->prevent_process_reload_form( $handler );
	}

}