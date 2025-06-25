<?php

namespace JustB2b\Controllers\Key;

use JustB2b\Controllers\Id\UsersController;
use JustB2b\Models\Key\CheckoutModel;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

class CheckoutController extends AbstractKeyController {
	use RuntimeCacheTrait;

	private string $key = 'checkout';
	protected CheckoutModel $checkoutModelObject;
	protected function __construct() {
		parent::__construct();

		$this->checkoutModelObject = new CheckoutModel();

		add_action( 'woocommerce_checkout_create_order', [ $this, 'addOrderTypeMeta' ], 10, 2 );
		add_action( 'woocommerce_thankyou', [ $this, 'renderThankYouOrderMeta' ] );
		add_action( 'woocommerce_order_details_after_order_table', [ $this, 'renderThankYouOrderMeta' ] );
		add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'renderAdminOrderMeta' ] );
		add_action( 'woocommerce_email_order_meta', [ $this, 'renderEmailOrderMeta' ], 10, 4 );
	}

	public static function getKey() {
		return 'checkout';
	}

	public static function getSingleName(): string {
		return 'checkout';
	}

	public static function getPluralName(): string {
		return 'Checkouts';
	}

	public function getDefinitions(): array {
		return CheckoutModel::getKeyFieldsDefinition();
	}

	public function getModelObject() {
		return $this->checkoutModelObject;
	}

	public function addOrderTypeMeta( $order, $data ) {
		$userType = UsersController::getCurrentUser()->isB2b() ? 'b2b' : 'b2c';
		$order->update_meta_data( '_justb2b_user_type', $userType );
	}

	public function renderThankYouOrderMeta( $orderId ): void {
		$order = wc_get_order( $orderId );
		$userType = $order->get_meta( '_justb2b_user_type' );
		if ( $userType === 'b2b' ) {
			echo "<p>Type: {$userType}</p>";
		}
	}


	public function renderAdminOrderMeta( $order ): void {
		$userType = $order->get_meta( '_justb2b_user_type' );
		if ( $userType === 'b2b' ) {
			echo "<p class=\"form-field form-field-wide\">Type: {$userType}</p>";
		}
	}

	public function renderEmailOrderMeta( $order, $sent_to_admin, $plain_text, $email ): void {
		$userType = $order->get_meta( '_justb2b_user_type' );
		if ( $userType === 'b2b' ) {
			echo $plain_text ? "Type: {$userType}\n" : "<p>Type: {$userType}</p>";
		}
	}
}
