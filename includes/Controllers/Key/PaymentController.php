<?php

namespace JustB2b\Controllers\Key;

defined('ABSPATH') || exit;

use WC_Payment_Gateways;
use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Models\Key\PaymentMethodModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Traits\RuntimeCacheTrait;

class PaymentController extends AbstractKeyController
{
    use RuntimeCacheTrait;

    protected function __construct()
    {
        parent::__construct();
        add_filter('woocommerce_available_payment_gateways', [$this, 'filterPaymentMethods']);
    }

    public function registerCarbonFields()
    {
        $paymentFields = FieldBuilder::buildFields(PaymentMethodModel::getFieldsDefinition());

        $globalController = GlobalController::getInstance();
        $generalSettings =  $globalController->getGlobalSettings();

        $generalSettings->add_tab('Payments', $paymentFields);
    }

    public function filterPaymentMethods($available_gateways)
    {
        $paymentMethods = $this->getPaymentMethods();

        // Get cart total (raw, unformatted) and optionally cache if reused
        $cartTotal = WC()->cart ? (float) WC()->cart->get_total('edit') : 0;

        foreach ($available_gateways as $id => $gateway) {
            if (!isset($paymentMethods[$id])) {
                continue;
            }

            /** @var PaymentMethodModel $method */
            $method = $paymentMethods[$id];

            if (!$method->isActive()) {
                unset($available_gateways[$id]);
                continue;
            }

            if (
                ($cartTotal < $method->getMinOrderTotal()) ||
                (!$method->isEmptyMaxOrderTotal()  && $cartTotal > $method->getMaxOrderTotal())
            ) {
                unset($available_gateways[$id]);
            }
        }

        return $available_gateways;
    }


    public function getPaymentMethods(): array
    {
        return self::getFromRuntimeCache(function () {
            $methods = [];
            $gateways = WC_Payment_Gateways::instance()->payment_gateways();
            foreach ($gateways as $gateway) {
                $methods[$gateway->id] = new PaymentMethodModel($gateway);
            }
            return $methods;
        });

    }

}
