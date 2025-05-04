<?php

namespace JustB2b\Controllers;

use JustB2b\Traits\LazyLoaderTrait;
use JustB2b\Models\PaymentMethodModel;
use JustB2b\Fields\FieldBuilder;
use WC_Payment_Gateways;

defined('ABSPATH') || exit;

class PaymentController extends BaseController
{
    use LazyLoaderTrait;

    protected ?array $paymentMethods = null;
    protected ?array $paymentFieldsDefinition = null;

    public function __construct()
    {
        parent::__construct();
        add_filter('woocommerce_available_payment_gateways', [$this, 'filterPaymentMethods']);
    }

    public function registerFields()
    {
        $paymentFields = FieldBuilder::buildFields($this->getPaymentFieldsDefinition());

        $globalController = GlobalController::getInstance();
        $generalSettings = $globalController->getGlobalSettings();

        $generalSettings->add_tab('Payments', $paymentFields);
    }

    public function getPaymentMethods(): array
    {
        $this->lazyLoad($this->paymentMethods, function () {
            $methods = [];

            $gateways = WC_Payment_Gateways::instance()->payment_gateways();

            foreach ($gateways as $gateway) {
                $methods[$gateway->id] = new PaymentMethodModel($gateway);
            }

            return $methods;
        });

        return $this->paymentMethods;
    }

    public function getPaymentFieldsDefinition(): array
    {
        $this->lazyLoad($this->paymentFieldsDefinition, function () {
            $fields = [];

            foreach ($this->getPaymentMethods() as $method) {
                $fields = array_merge(
                    $fields,
                    $method->getFields()
                );
            }

            return $fields;
        });

        return $this->paymentFieldsDefinition;
    }

    public function filterPaymentMethods($available_gateways)
    {
        $paymentMethods = $this->getPaymentMethods();

        $cartTotal = WC()->cart ? WC()->cart->get_total('edit') : 0;

        foreach ($available_gateways as $id => $gateway) {
            if (isset($paymentMethods[$id])) {
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
        }

        return $available_gateways;
    }
}
