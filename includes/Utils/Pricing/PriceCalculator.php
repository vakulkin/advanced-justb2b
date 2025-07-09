<?php

namespace JustB2b\Utils\Pricing;

use WC_Tax;
use WC_Customer;
use Automattic\WooCommerce\Proxies\LegacyProxy;
use JustB2b\Fields\NonNegativeNumberField;
use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Models\Id\ProductModel;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section price_rules
 * @title[ru] Гибкие правила ценообразования
 * @title[pl] Elastyczne reguły ustalania cen
 * @desc[ru] Модуль расчёта цен JustB2B поддерживает продвинутую логику: скидки и наценки на основе количества, ролей, групп, категорий, условий и приоритетов. Позволяет точно управлять B2B-ценами на уровне каждого товара.
 * @desc[pl] Moduł ustalania cen JustB2B obsługuje zaawansowaną logikę: rabaty i narzuty w zależności od ilości, ról, grup, kategorii, warunków i priorytetów. Umożliwia precyzyjne zarządzanie cenami B2B na poziomie pojedynczego produktu.
 * @order 100
 */

/**
 * @feature price_rules tax_rate_detection
 * @title[ru] Автоматическое определение налоговой ставки
 * @title[pl] Automatyczne wykrywanie stawki podatku
 * @desc[ru] Учитывает налоговую ставку в зависимости от настроек магазина и местоположения покупателя.
 * @desc[pl] Uwzględnia stawkę podatku na podstawie ustawień sklepu i lokalizacji klienta.
 * @order 440
 */

/**
 * @feature price_rules rrp_support
 * @title[ru] Поддержка рекомендуемой розничной цены (RRP)
 * @title[pl] Obsługa sugerowanej ceny detalicznej (RRP)
 * @desc[ru] Вычисляет и отображает RRP-цену с поддержкой первичного и вторичного источника: если основной источник возвращает 0, используется резервный.
 * @desc[pl] Oblicza i wyświetla cenę RRP z obsługą źródła podstawowego i zapasowego — jeśli główne źródło zwróci 0, zostanie użyte drugie.
 * @order 430
 */

/**
 * @feature price_rules flexible_sources
 * @title[ru] Источники цен: WC или JustB2B
 * @title[pl] Źródła cen: WooCommerce lub JustB2B
 * @desc[ru] Плагин поддерживает базовые цены как из WooCommerce, так и из собственных мета-полей JustB2B.
 * @desc[pl] Wtyczka obsługuje ceny bazowe zarówno z WooCommerce, jak i z własnych pól meta JustB2B.
 * @order 420
 */


class PriceCalculator {
	use RuntimeCacheTrait;

	protected ProductModel $product;

	public function __construct( ProductModel $product ) {
		$this->product = $product;
	}

	protected function cacheContext( array $extra = [] ): array {
		return array_merge( [ 
			'product_id' => $this->product->getOriginLangProductId(),
			'qty' => $this->product->getQty()
		], $extra );
	}

	public function getTaxRates(): array {
		return self::getFromRuntimeCache( function () {
			$WCProduct = $this->product->getWCProduct();
			if ( ! $WCProduct->is_taxable() ) {
				return [];
			}

			$customerId = get_current_user_id();
			if ( apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
				return WC_Tax::get_base_tax_rates( $WCProduct->get_tax_class( 'unfiltered' ) );
			}

			$customer = $customerId
				? wc_get_container()->get( LegacyProxy::class)->getInstance_of( WC_Customer::class, $customerId )
				: null;

			return WC_Tax::get_rates( $WCProduct->get_tax_class(), $customer );
		}, $this->cacheContext() );
	}


	public function getRRPNet(): float {
		return self::getFromRuntimeCache( function () {
			$rule = $this->product->getFirstFullFitRule();
			if ( $rule ) {
				$primaryNetRRP = $this->getNetByKey( $rule->getPrimaryRRPSource() );
				$secondaryNetRRP = $this->getSecondaryPrice( $primaryNetRRP, $rule->getSecondaryRRPSource() );
				return $this->getSecondaryPrice( $secondaryNetRRP, $rule->getThirdRRPSource() );
			}
			return 0;
		}, $this->cacheContext() );
	}

	public function getRRPGross(): float {
		return self::getFromRuntimeCache(
			fn() => $this->calcGrossFromNetPrice(
				$this->getRRPNet(),
			),
			$this->cacheContext()
		);
	}

	public function getBaseNetPrice(): float {
		return self::getFromRuntimeCache( function () {
			$rule = $this->product->getFirstFullFitRule();
			if ( $rule && ! $rule->isZeroRequestPrice() ) {
				$primaryNetPrice = $this->getNetByKey( $rule->getPrimaryPriceSource() );
				$secondaryNetPrice = $this->getSecondaryPrice( $primaryNetPrice, $rule->getSecondaryPriceSource() );
				return $this->getSecondaryPrice( $secondaryNetPrice, $rule->getThirdPriceSource() );
			}
			return 0;
		}, $this->cacheContext() );
	}

	public function getBaseGrossPrice(): float {
		return self::getFromRuntimeCache(
			fn() => $this->calcGrossFromNetPrice(
				$this->getBaseNetPrice(),
			),
			$this->cacheContext()
		);
	}

	public function getYourNetPrice(): float {
		return self::getFromRuntimeCache( function () {
			return $this->calcRule();
		}, $this->cacheContext() );
	}

	public function getYourGrossPrice(): float {
		return self::getFromRuntimeCache(
			fn() => $this->calcGrossFromNetPrice(
				$this->getYourNetPrice(),
			),
			$this->cacheContext()
		);
	}

	public function getYourNetTotal(): float {
		return self::getFromRuntimeCache(
			fn() => $this->getTotal(
				$this->getYourNetPrice()
			),
			$this->cacheContext()
		);
	}

	public function getYourGrossTotal(): float {
		return self::getFromRuntimeCache(
			fn() => $this->getTotal(
				$this->getYourGrossPrice()
			),
			$this->cacheContext()
		);
	}

	public function getNumberOfTimesToAddGifts() {
		$rule = $this->product->getFirstFullFitRule();
		if ( $rule ) {
			if ( $rule->getFreeEveryItems() > 0 ) {
				return intdiv( $this->product->getQty(), $rule->getFreeEveryItems() );
			}
			return 1;
		}
		return 0;
	}

	public function getNumberfOfGifts(): int {
		$rule = $this->product->getFirstFullFitRule();
		if ( $rule ) {
			return min( $rule->getNumberOfFree(), $this->product->getQty() ) * $this->getNumberOfTimesToAddGifts();
		}
		return 0;
	}

	public function getGiftsSaleNetTotal(): float {
		return self::getFromRuntimeCache(
			fn() => $this->getGiftsSaleTotal(
				$this->getYourNetPrice()
			),
			$this->cacheContext()
		);
	}

	public function getGiftsSaleGrossTotal(): float {
		return self::getFromRuntimeCache(
			fn() => $this->getGiftsSaleTotal(
				$this->getYourGrossPrice()
			),
			$this->cacheContext()
		);
	}

	public function getGiftsSaleTotal( $price ): float {
		$rule = $this->product->getFirstFullFitRule();
		if ( $rule ) {
			return $price * $this->getNumberfOfGifts();
		}
		return 0;
	}

	public function getFinalNetTotal(): float {
		return self::getFromRuntimeCache(
			fn() => $this->getYourNetPrice() * ( $this->product->getQty() - $this->getNumberfOfGifts() ),
			$this->cacheContext()
		);
	}
	public function getFinalGrossTotal(): float {
		return self::getFromRuntimeCache(
			fn() => $this->getYourGrossPrice() * ( $this->product->getQty() - $this->getNumberfOfGifts() ),
			$this->cacheContext()
		);
	}

	public function getFinalNetPerItemPrice(): float {
		return self::getFromRuntimeCache(
			fn() => $this->getFinalNetTotal() / $this->product->getQty(),
			$this->cacheContext()
		);
	}

	public function getFinalGrossPerItemPrice(): float {
		return self::getFromRuntimeCache(
			fn() => $this->getFinalGrossTotal() / $this->product->getQty(),
			$this->cacheContext()
		);
	}

	public function getTotal( $price ): float {
		return $price * $this->product->getQty();
	}

	protected function getSecondaryPrice( float $primaryPrice, string $secondaryKey ): float {
		if ( $primaryPrice > 0 ) {
			return $primaryPrice;
		}
		if ( 'disabled' !== $secondaryKey ) {
			return $this->getNetByKey( $secondaryKey );
		}
		return 0;
	}

	protected function getNetByKey( string $key ): float {
		if ( str_starts_with( $key, 'currency_base_price' ) || $key === 'currency_rrp_price' ) {
			$strippedKey = str_replace( 'currency_', '', $key );
			$finalKey = strtolower( get_woocommerce_currency() ) . '__' . $strippedKey;
			$value = $this->calcNetFromJustB2bMeta( $finalKey );
			$currentCurrency = apply_filters( 'wcml_price_currency', null );
			return $this->getConvertedCurrencyValue(
				$value,
				get_woocommerce_currency(),
				$currentCurrency
			);
		}
		if ( str_starts_with( $key, 'base_price' ) || $key === 'rrp_price' ) {
			$netPrice = $this->calcNetFromJustB2bMeta( $key );
			return apply_filters( 'wcml_raw_price_amount', $netPrice );
		}
		return $this->calcNetFromWCMeta( $key );
	}

	protected function calcNetFromWCMeta( string $key ): float {

		switch ( $key ) {
			case '_regular_price':
				$price = $this->product->getWCProduct()->get_regular_price();
				break;
			case '_sale_price':
				$price = $this->product->getWCProduct()->get_sale_price();
				break;
			default:
				$price = $this->product->getWCProduct()->get_price();
				break;
		}

		$price = abs( (float) $price );
		return wc_prices_include_tax()
			? $this->calcNetFromGrossPrice( $price )
			: $price;
	}

	protected function calcNetFromJustB2bMeta( string $key ): float {
		$field = new NonNegativeNumberField( $key, '' );
		// todo: not beautiful place
		$price = $field->getValue( $this->product->getOriginLangProductId() );
		$globalController = GlobalController::getInstance();
		$settingsObject = $globalController->getSettingsModelObject();
		$isNet = $settingsObject->getFieldValue( "setting_type_$key" ) !== 'gross';
		return $isNet ? $price : $this->calcNetFromGrossPrice( $price );
	}

	/**
	 * @feature price_rules net_gross_conversion
	 * @title[ru] Конвертация между нетто и брутто
	 * @desc[ru] Автоматически пересчитывает цену с учётом налога — из брутто в нетто и обратно.
	 * @order 410
	 */
	public function calcNetFromGrossPrice( float $gross ): float {
		$removeTaxes = WC_Tax::calc_tax( $gross, $this->getTaxRates(), true );
		return $gross - array_sum( $removeTaxes );
	}

	public function calcGrossFromNetPrice( float $net ): float {
		$addTaxes = WC_Tax::calc_tax( $net, $this->getTaxRates(), false );
		return $net + array_sum( $addTaxes );
	}

	public function getConvertedCurrencyValue( $value, $inputCurrency, $outputCurrecny ): float {
		$inputCurrencyRate = apply_filters( 'wcml_raw_price_amount', 1, $inputCurrency ) ?: 1;
		$outputCurrecnyRate = apply_filters( 'wcml_raw_price_amount', 1, $outputCurrecny ) ?: 1;
		return $value * ( $outputCurrecnyRate / $inputCurrencyRate );
	}

	/**
	 * @feature price_rules rule_engine
	 * @title[ru] Многоуровневая система расчёта цен
	 * @desc[ru] Определяет цену товара в зависимости от правил: процентные и числовые скидки, наценки, установка фиксированной цены, с учётом нетто и брутто.
	 * @order 400
	 */
	public function calcRule(): float {
		$result = 0;
		$rule = $this->product->getFirstFullFitRule();

		if ( ! $rule ) {
			return $result;
		}

		$kind = $rule->getKind();
		$value = $rule->getValue();

		switch ( $kind ) {
			case 'price_source':
				$result = $this->getBaseNetPrice();
				break;

			case 'net_minus_percent':
				$result = max( 0, $this->getBaseNetPrice() * ( 1 - $value * 0.01 ) );
				break;

			case 'gross_minus_percent':
				$gross = $this->getBaseGrossPrice() * ( 1 - $value * 0.01 );
				$result = $this->calcNetFromGrossPrice( $gross );
				break;

			case 'net_plus_percent':
				$result = $this->getBaseNetPrice() * ( 1 + $value * 0.01 );
				break;

			case 'gross_plus_percent':
				$gross = $this->getBaseGrossPrice() * ( 1 + $value * 0.01 );
				$result = $this->calcNetFromGrossPrice( $gross );
				break;

			case 'net_minus_number':
			case 'gross_minus_number':
			case 'net_plus_number':
			case 'gross_plus_number':
			case 'net_equals_number':
			case 'gross_equals_number':
				$currentCurrency = apply_filters( 'wcml_price_currency', null );
				$value = $this->getConvertedCurrencyValue(
					$value,
					$rule->getCurrency(),
					$currentCurrency
				);

				switch ( $kind ) {
					case 'net_minus_number':
						$result = max( 0, $this->getBaseNetPrice() - $value );
						break;
					case 'gross_minus_number':
						$gross = max( 0, $this->getBaseGrossPrice() - $value );
						$result = $this->calcNetFromGrossPrice( $gross );
						break;
					case 'net_plus_number':
						$result = $this->getBaseNetPrice() + $value;
						break;
					case 'gross_plus_number':
						$gross = $this->getBaseGrossPrice() + $value;
						$result = $this->calcNetFromGrossPrice( $gross );
						break;
					case 'net_equals_number':
						$result = $value;
						break;
					case 'gross_equals_number':
						$result = $this->calcNetFromGrossPrice( $value );
						break;
				}
				break;
		}

		return $result;
	}


}
