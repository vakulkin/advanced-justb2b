<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use JustB2b\Traits\RuntimeCacheTrait;
use JustB2b\Models\PaymentMethodModel;
use JustB2b\Fields\FieldBuilder;

class PaymentController extends AbstractKeyController
{
    use RuntimeCacheTrait;

    protected string $modelClass = PaymentMethodModel::class;

    protected function __construct()
    {
        parent::__construct();
        add_filter('woocommerce_available_payment_gateways', [$this, 'filterPaymentMethods']);
    }

    public function registerCarbonFields()
    {
        $paymentFields = FieldBuilder::buildFields($this->modelClass::getFieldsDefinition());

        $globalController = GlobalController::getInstance();
        $generalSettings = $globalController->getGlobalSettings();

        $generalSettings->add_tab('Payments', $paymentFields);
    }

    public static function filterPaymentMethods($available_gateways)
    {
        $controller = self::getInstance();

        // Cache payment methods during request
        $paymentMethods = $controller->getFromRuntimeCache('available_payment_methods', function () use ($controller) {
            return $controller->modelClass::getPaymentMethods();
        });

        // Get cart total (raw, unformatted) and optionally cache if reused
        $cartTotal = WC()->cart ? (float) WC()->cart->get_total('edit') : 0;

        foreach ($available_gateways as $id => $gateway) {
            if (!isset($paymentMethods[$id])) {
                continue;
            }

            $method = $paymentMethods[$id];

            if (!$method->isActive()) {
                unset($available_gateways[$id]);
                continue;
            }

            $minTotal = $method->getMinOrderTotal();
            $maxTotal = $method->getMaxOrderTotal();

            if (
                ($minTotal !== false && $cartTotal < $minTotal) ||
                ($maxTotal !== false && $cartTotal > $maxTotal)
            ) {
                unset($available_gateways[$id]);
            }
        }

        return $available_gateways;
    }
}
