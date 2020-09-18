<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

require_once 'TwoCheckoutApi.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Twocheckout - safe payment method
 */
class Twocheckout extends \PaymentModule
{
    const DEBUG_MODE = false;
    public    $tcoApi;
    public    $details;
    public    $owner;
    public    $name;
    public    $address;
    public    $bootstrap;
    public    $is_eu_compatible;
    public    $extra_mail_vars;
    public    $confirmUninstall;
    protected $_html       = '';
    protected $_postErrors = [];
    /**
     * @var
     */
    private $module;

    /**
     * @var array
     */
    private $_signParams = [
        'return-url',
        'return-type',
        'expiration',
        'order-ext-ref',
        'item-ext-ref',
        'lock',
        'cust-params',
        'customer-ref',
        'customer-ext-ref',
        'currency',
        'prod',
        'price',
        'qty',
        'tangible',
        'type',
        'opt',
        'coupon',
        'description',
        'recurrence',
        'duration',
        'renewal-price',
    ];

    /**
     * Twocheckout constructor.
     */
    public function __construct()
    {
        $this->name = 'twocheckout';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->author = '2Checkout (adrian.dica, andrei.popa & cosmin.panait)';
        $this->controllers = ['validation'];
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->tcoApi = new TwoCheckoutApi();
        $this->tcoApi->setSecretKey(Configuration::get('TWOCHECKOUT_SECRET_KEY'));
        $this->tcoApi->setSellerId(Configuration::get('TWOCHECKOUT_SID'));
        $this->tcoApi->setSecretWord(Configuration::get('TWOCHECKOUT_SECRET_WORD'));

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('2checkout');
        $this->description = $this->l('2checkout - Simple & safe payment solutions');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall 2Checkout payment modules?');
    }

    /**
     * default style for API form
     * @return string
     */
    private function getDefaultStyle()
    {
        return '{
                    "margin": "0",
                    "fontFamily": "Helvetica, sans-serif",
                    "fontSize": "1rem",
                    "fontWeight": "400",
                    "lineHeight": "1.5",
                    "color": "#212529",
                    "textAlign": "left",
                    "backgroundColor": "#FFFFFF",
                    "*": {
                        "boxSizing": "border-box"
                    },
                    ".no-gutters": {
                        "marginRight": 0,
                        "marginLeft": 0
                    },
                    ".row": {
                        "display": "flex",
                        "flexWrap": "wrap"
                    },
                    ".col": {
                        "flexBasis": "0",
                        "flexGrow": "1",
                        "maxWidth": "100%",
                        "padding": "0",
                        "position": "relative",
                        "width": "100%"
                    },
                    "div": {
                        "display": "block"
                    },
                    ".field-container": {
                        "paddingBottom": "14px"
                    },
                    ".field-wrapper": {
                        "paddingRight": "25px"
                    },
                    ".input-wrapper": {
                        "position": "relative"
                    },
                    "label": {
                        "display": "inline-block",
                        "marginBottom": "9px",
                        "color": "#313131",
                        "fontSize": "14px",
                        "fontWeight": "300",
                        "lineHeight": "17px"
                    },
                    "input": {
                        "overflow": "visible",
                        "margin": 0,
                        "fontFamily": "inherit",
                        "display": "block",
                        "width": "100%",
                        "height": "42px",
                        "padding": "10px 12px",
                        "fontSize": "18px",
                        "fontWeight": "400",
                        "lineHeight": "22px",
                        "color": "#313131",
                        "backgroundColor": "#FFF",
                        "backgroundClip": "padding-box",
                        "border": "1px solid #CBCBCB",
                        "borderRadius": "3px",
                        "transition": "border-color .15s ease-in-out,box-shadow .15s ease-in-out",
                        "outline": 0
                    },
                    "input:focus": {
                        "border": "1px solid #5D5D5D",
                        "backgroundColor": "#FFFDF2"
                    },
                    ".is-error input": {
                        "border": "1px solid #D9534F"
                    },
                    ".is-error input:focus": {
                        "backgroundColor": "#D9534F0B"
                    },
                    ".is-valid input": {
                        "border": "1px solid #1BB43F"
                    },
                    ".is-valid input:focus": {
                        "backgroundColor": "#1BB43F0B"
                    },
                    ".validation-message": {
                        "color": "#D9534F",
                        "fontSize": "10px",
                        "fontStyle": "italic",
                        "marginTop": "6px",
                        "marginBottom": "-5px",
                        "display": "block",
                        "lineHeight": "1"
                    },
                    ".card-expiration-date": {
                        "paddingRight": ".5rem"
                    },
                    ".is-empty input": {
                        "color": "#EBEBEB"
                    },
                    ".lock-icon": {
                        "top": "calc(50% - 7px)",
                        "right": "10px"
                    },
                    ".valid-icon": {
                        "top": "calc(50% - 8px)",
                        "right": "-25px"
                    },
                    ".error-icon": {
                        "top": "calc(50% - 8px)",
                        "right": "-25px"
                    },
                    ".card-icon": {
                        "top": "calc(50% - 10px)",
                        "left": "10px",
                        "display": "none"
                    },
                    ".is-empty .card-icon": {
                        "display": "block"
                    },
                    ".is-focused .card-icon": {
                        "display": "none"
                    },
                    ".card-type-icon": {
                        "right": "30px",
                        "display": "block"
                    },
                    ".card-type-icon.visa": {
                        "top": "calc(50% - 14px)"
                    },
                    ".card-type-icon.mastercard": {
                        "top": "calc(50% - 14.5px)"
                    },
                    ".card-type-icon.amex": {
                        "top": "calc(50% - 14px)"
                    },
                    ".card-type-icon.discover": {
                        "top": "calc(50% - 14px)"
                    },
                    ".card-type-icon.jcb": {
                        "top": "calc(50% - 14px)"
                    },
                    ".card-type-icon.dankort": {
                        "top": "calc(50% - 14px)"
                    },
                    ".card-type-icon.cartebleue": {
                        "top": "calc(50% - 14px)"
                    },
                    ".card-type-icon.diners": {
                        "top": "calc(50% - 14px)"
                    },
                    ".card-type-icon.elo": {
                        "top": "calc(50% - 14px)"
                    }
                }';
    }

    /**
     * install the module
     * @return bool|string
     */
    public function install()
    {

        if (parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('paymentReturn')) {

            Configuration::updateValue('TWOCHECKOUT_STYLE', $this->getDefaultStyle()); // to have a starting point
            Configuration::updateValue('TWOCHECKOUT_IPN_URL', $this->context->link->getModuleLink('twocheckout', 'ipn'));

            return true;
        }

        return false;
    }

    /**
     * uninstall the module and deletes the config keys
     * @return bool
     */
    function uninstall()
    {
        Configuration::deleteByName('TWOCHECKOUT_SID');
        Configuration::deleteByName('TWOCHECKOUT_SECRET_KEY');
        Configuration::deleteByName('TWOCHECKOUT_DEMO');
        Configuration::deleteByName('TWOCHECKOUT_TYPE');
        Configuration::deleteByName('TWOCHECKOUT_IPN_URL');
        Configuration::deleteByName('TWOCHECKOUT_SECRET_WORD');
        Configuration::deleteByName('TWOCHECKOUT_STYLE_DEFAULT_MODE');
        Configuration::deleteByName('TWOCHECKOUT_STYLE');

        return parent::uninstall();
    }

    /**
     * show the settings page, also saves and validates the form on submit
     * @return string
     */
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $merchantId = strval(Tools::getValue('TWOCHECKOUT_SID'));
            $buyLinkSecretWord = strval(Tools::getValue('TWOCHECKOUT_SECRET_WORD'));
            $secretKey = strval(Tools::getValue('TWOCHECKOUT_SECRET_KEY'));
            $inline = strval(Tools::getValue('TWOCHECKOUT_TYPE'));
            $demoMode = strval(Tools::getValue('TWOCHECKOUT_DEMO'));
            $style = strval(Tools::getValue('TWOCHECKOUT_STYLE'));
            $styleMode = strval(Tools::getValue('TWOCHECKOUT_STYLE_DEFAULT_MODE'));

            if (
                (!$merchantId || empty($merchantId) || !Validate::isGenericName($merchantId))
                && (!$buyLinkSecretWord || empty($buyLinkSecretWord) || !Validate::isGenericName($buyLinkSecretWord))
                && (!$secretKey || empty($secretKey) || !Validate::isGenericName($secretKey))
                && (!$inline || empty($inline) || !Validate::isGenericName($inline))
                && (!$demoMode || empty($demoMode) || !Validate::isGenericName($demoMode))
            ) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('TWOCHECKOUT_SID', $merchantId);
                Configuration::updateValue('TWOCHECKOUT_SECRET_WORD', $buyLinkSecretWord);
                Configuration::updateValue('TWOCHECKOUT_SECRET_KEY', $secretKey);
                Configuration::updateValue('TWOCHECKOUT_TYPE', $inline);
                Configuration::updateValue('TWOCHECKOUT_DEMO', $demoMode);
                Configuration::updateValue('TWOCHECKOUT_STYLE_DEFAULT_MODE', $styleMode);
                Configuration::updateValue('TWOCHECKOUT_STYLE', $style);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output . $this->displayForm();
    }

    /**
     * creates the form for the module settings (admin area)
     * @return string
     */
    private function displayForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input'  => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('Seller ID(Merchant Code)'),
                    'name'     => 'TWOCHECKOUT_SID',
                    'size'     => 200,
                    'required' => true,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Buy Link Secret Word'),
                    'name'     => 'TWOCHECKOUT_SECRET_WORD',
                    'size'     => 200,
                    'required' => true,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Secret Key'),
                    'name'     => 'TWOCHECKOUT_SECRET_KEY',
                    'size'     => 200,
                    'required' => true,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('IPN Url'),
                    'name'     => 'TWOCHECKOUT_IPN_URL',
                    'size'     => 200,
                    'value'    => $this->context->link->getModuleLink('twocheckout', 'ipn'),
                    'desc'     => $this->l('Copy this link to your 2checkout account under the IPN section'),
                    'readonly' => true,
                ],
                [
                    'type'     => 'radio',
                    'label'    => $this->l('Cart type'),
                    'name'     => 'TWOCHECKOUT_TYPE',
                    'class'    => 't',
                    'required' => true,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'api',
                            'value' => 2,
                            'label' => $this->l('API'),
                        ],
                        [
                            'id'    => 'yes',
                            'value' => 0,
                            'label' => $this->l('Convert Plus'),
                        ],
                        [
                            'id'    => 'no',
                            'value' => 1,
                            'label' => $this->l('Inline'),
                        ]
                    ],
                ],
                [
                    'type'     => 'radio',
                    'label'    => $this->l('Demo Mode'),
                    'name'     => 'TWOCHECKOUT_DEMO',
                    'class'    => 't',
                    'required' => true,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'yes',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id'    => 'no',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ]
                    ],
                ],
                [
                    'type'     => 'radio',
                    'label'    => $this->l('Use default style for API'),
                    'name'     => 'TWOCHECKOUT_STYLE_DEFAULT_MODE',
                    'class'    => 't',
                    'required' => true,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'YES',
                            'value' => 1,
                            'label' => $this->l('YES, use default'),
                        ],
                        [
                            'id'    => 'NO',
                            'value' => 0,
                            'label' => $this->l('NO, use my custom style'),
                        ]
                    ],
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Custom style for API form'),
                    'name'  => 'TWOCHECKOUT_STYLE',
                    'desc'  => $this->l('IMPORTANT! This is the styling object that styles your form.
                     Do not remove or add new classes. You can modify the existing ones. Use
                      double quotes for all keys and values! - VALID JSON FORMAT REQUIRED (validate 
                      json before save here: https://jsonlint.com/ ).')
                ],
            ],
            'submit' => [
                'title' => $this->l('Update settings'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ],
        ];

        // Load current value
        $helper->fields_value['TWOCHECKOUT_SID'] = Configuration::get('TWOCHECKOUT_SID');
        $helper->fields_value['TWOCHECKOUT_SECRET_WORD'] = Configuration::get('TWOCHECKOUT_SECRET_WORD');
        $helper->fields_value['TWOCHECKOUT_SECRET_KEY'] = Configuration::get('TWOCHECKOUT_SECRET_KEY');
        $helper->fields_value['TWOCHECKOUT_TYPE'] = Configuration::get('TWOCHECKOUT_TYPE');
        $helper->fields_value['TWOCHECKOUT_DEMO'] = Configuration::get('TWOCHECKOUT_DEMO');
        $helper->fields_value['TWOCHECKOUT_IPN_URL'] = Configuration::get('TWOCHECKOUT_IPN_URL');
        $helper->fields_value['TWOCHECKOUT_STYLE'] = Configuration::get('TWOCHECKOUT_STYLE');
        $helper->fields_value['TWOCHECKOUT_STYLE_DEFAULT_MODE'] = Configuration::get('TWOCHECKOUT_STYLE_DEFAULT_MODE');

        return $helper->generateForm($fieldsForm);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function hookDisplayOrderConfirmation($params)
    {

        $order = $params['order'] ?? null;
        $cart = $params['cart'] ?? null;
        $this->smarty->assign(['order' => $order, 'cart' => $cart]);

        return $this->fetch('module:twocheckout/views/templates/hook/payment_return.tpl');
    }

    /**
     * @param $params
     * @return PaymentOption[]|void|void[]
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active || !$this->checkCurrency($params['cart'])) {
            return;
        }
        // we clear the cache for every change we make
        Tools::clearSmartyCache();
        Tools::clearXMLCache();
        Media::clearCache();
        Tools::generateIndex();

        if (Configuration::get('TWOCHECKOUT_TYPE') == 2) { // api with 2payJs
            return [$this->getApiPaymentOption()];
        } else { // inline or Convert+
            return [$this->getInlineConvertPaymentOption()];
        }
    }

    /**
     * @param $cart
     * @return bool
     */
    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * @return PaymentOption|void
     */
    public function getInlineConvertPaymentOption()
    {
        if (!$this->active) {
            return;
        }
        $newOption = new PaymentOption();
        $newOption->setCallToActionText('Pay with 2Checkout')
                  ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
                  ->setForm($this->generateForm());

        return $newOption;
    }

    /**
     * 2payJS->API payment method
     * @return PaymentOption
     * @throws SmartyException
     */
    public function getApiPaymentOption()
    {
        if (!$this->active) {
            return;
        }
        $newApiOption = new PaymentOption();
        $newApiOption->setCallToActionText($this->l('Pay with 2Checkout'))
                     ->setForm($this->generateApiForm())
                     ->setBinary(true)
                     ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true));

        return $newApiOption;
    }

    /**
     * genereates the form for the payment option (2payJs)
     * @return string
     * @throws SmartyException
     */
    protected function generateApiForm()
    {

        // get style and remove newlines
        if (Configuration::get('TWOCHECKOUT_STYLE_DEFAULT_MODE')) {
            $style = trim(preg_replace('/\s\s+/', ' ', $this->getDefaultStyle()));
        } else {
            $style = trim(preg_replace('/\s\s+/', ' ', Configuration::get('TWOCHECKOUT_STYLE')));
        }
        $this->context->smarty->assign([
            'action'          => $this->context->link->getModuleLink($this->name, 'validation', [], true),
            'sellerId'        => Configuration::get('TWOCHECKOUT_SID'),
            'style'           => $style,
            'script'          => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/assets/js/twocheckout.js'),
            'css'             => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/assets/css/twocheckout.css'),
            'spinner'         => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/assets/images/spinner.gif'),
        ]);

        return $this->context->smarty->fetch('module:twocheckout/views/templates/front/payment_form.tpl');
    }

    /**
     * generates the form for the payment option (convert+ & inline)
     * @return string
     * @throws SmartyException
     */
    protected function generateForm()
    {
        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'validation', [], true),
        ]);

        return $this->context->smarty->fetch('module:twocheckout/views/templates/hook/payment_options.tpl');
    }

    /**
     * @param $params
     * this hook applies only for hosted and inline, NOT api
     * @return string|void
     * @throws \Exception
     */
    public function hookActionValidateOrder($params)
    {

        /**
         * Verify if this module is enabled
         */
        if (!$this->active) {
            return;
        }

        /**
         * Order is validated and added to the database
         * Grab necessary params and redirect to 2checkout
         */
        $this->module = Module::getInstanceByName(Tools::getValue('module'));
        /** @var $cart \Cart */
        $cart = $params['cart'];
        /** @var $order \Order */
        $order = $params['order'];
        /** @var $customer \Customer */
        $customer = $params['customer'];
        /** @var $currency \Currency */
        $currency = $params['currency'];
        $orderId = Order::getIdByCartId($cart->id);
        /** @var \Address $invoice */
        $invoice = new Address(intval($cart->id_address_invoice));

        $returnUrl = _PS_BASE_URL_ . '/index.php?controller=order-confirmation&id_cart=' . (int)$cart->id . '&id_module=' . (int)$this->module->id . '&id_order=' . $orderId . '&key=' . $customer->secure_key;
        $countryIsoCode = Country::getIsoById($invoice->id_country);
        $stateIsoCode = State::getNameById($invoice->id_state);
        $languageIsoCode = Language::getIsoById($order->id_lang);
        $source = 'PRESTASHOP_1_7_6';

        switch ((int)Configuration::get('TWOCHECKOUT_TYPE')) {
            case 2: //2payJs
                $currencyIso = strtolower($currency->iso_code);
                $orderParams = [
                    'Currency'          => $currencyIso,
                    'Language'          => $languageIsoCode,
                    'Country'           => $countryIsoCode,
                    'CustomerIP'        => $this->getCustomerIp(),
                    'Source'            => $source,
                    'ExternalReference' => $orderId,
                    'Items'             => $this->getItem($orderId, $cart->getOrderTotal()),
                    'BillingDetails'    => $this->getBillingDetails($invoice, $stateIsoCode, $countryIsoCode, $customer->email),
                    'PaymentDetails'    => $this->getPaymentDetails(Tools::getValue('ess_token'), $currencyIso, $cart->id, $orderId),
                ];

                $apiResponse = $this->tcoApi->call('orders', $orderParams);

                if (!isset($apiResponse['RefNo'])) {
                    $result = ['status' => false, 'errors' => $apiResponse['message'], 'redirect' => null];
                } elseif ($apiResponse['Errors']) {
                    $errorMessage = '';
                    foreach ($apiResponse['Errors'] as $key => $value) {
                        $errorMessage .= $value . PHP_EOL;
                    }
                    $result = ['status' => false, 'error' => $errorMessage, 'redirect' => null];
                    //remove created order from prestashop
                    // is cart will be the same witch is great
                    $order->delete();
                } else {
                    $hasAuthorize3ds = $this->hasAuthorize3DS($apiResponse['PaymentDetails']['PaymentMethod']['Authorize3DS']);
                    $redirectTo = $hasAuthorize3ds ?? $returnUrl;
                    $result = ['status' => true, 'errors' => null, 'redirect' => $redirectTo];
                }

                exit(json_encode($result));
            case 1: //inline
                $billingAddressData = [
                    'name'    => $invoice->firstname . ' ' . $invoice->lastname,
                    'phone'   => $invoice->phone,
                    'country' => $countryIsoCode,
                    'state'   => $stateIsoCode,
                    'email'   => $customer->email,
                    'address' => $invoice->address1,
                    'city'    => $invoice->city,
                    'zip'     => $invoice->postcode,
                ];

                $shippingAddressData = [
                    'ship-name'     => $invoice->firstname . ' ' . $invoice->lastname,
                    'ship-country'  => $countryIsoCode,
                    'ship-state'    => $stateIsoCode,
                    'ship-city'     => $invoice->city,
                    'ship-email'    => $customer->email,
                    'ship-address'  => $invoice->address1,
                    'ship-address2' => !empty($invoice->address2) ? $invoice->address2 : '',
                ];

                $productData[] = [
                    'type'     => 'PRODUCT',
                    'name'     => 'Cart_' . $orderId,
                    'price'    => $cart->getOrderTotal(),
                    'tangible' => 0,
                    'qty'      => 1,
                ];
                $inlineLinkParams['products'] = ($productData);

                $inlineLinkParams['currency'] = strtolower($currency->iso_code);
                $inlineLinkParams['language'] = $languageIsoCode;
                $inlineLinkParams['return-method'] = [
                    'type' => 'redirect',
                    'url'  => $returnUrl
                ];

                $inlineLinkParams['test'] = Configuration::get('TWOCHECKOUT_DEMO');
                $inlineLinkParams['order-ext-ref'] = $orderId;
                $inlineLinkParams['return-url'] = $returnUrl;
                $inlineLinkParams['customer-ext-ref'] = $customer->email;
                $inlineLinkParams['src'] = $source;
                $inlineLinkParams['dynamic'] = 1;
                $inlineLinkParams['merchant'] = Configuration::get('TWOCHECKOUT_SID');
                $inlineLinkParams = array_merge($inlineLinkParams, $billingAddressData);
                $inlineLinkParams = array_merge($inlineLinkParams, $shippingAddressData);
                $inlineLinkParams['signature'] = $this->tcoApi->getInlineSignature($inlineLinkParams);

                $inlineLinkParams['url_data'] = [
                    'type' => 'redirect',
                    'url'  => $returnUrl
                ];
                $inlineLinkParams['shipping_address'] = ($shippingAddressData);
                $inlineLinkParams['billing_address'] = ($billingAddressData);

                $redirectTo = $this->context->link->getModuleLink('twocheckout', 'inline', $inlineLinkParams);

                break;
            default: //Convert+

                $buyLinkParams['name'] = $invoice->firstname . ' ' . $invoice->lastname;
                $buyLinkParams['phone'] = $invoice->phone;
                $buyLinkParams['country'] = $countryIsoCode;
                $buyLinkParams['state'] = $stateIsoCode;
                $buyLinkParams['email'] = $customer->email;
                $buyLinkParams['address'] = $invoice->address1;
                $buyLinkParams['address2'] = !empty($invoice->address2) ? $invoice->address2 : '';
                $buyLinkParams['city'] = $invoice->city;
                $buyLinkParams['ship-name'] = $invoice->firstname . ' ' . $invoice->lastname;
                $buyLinkParams['ship-country'] = $countryIsoCode;
                $buyLinkParams['ship-state'] = $stateIsoCode;
                $buyLinkParams['ship-city'] = $invoice->city;
                $buyLinkParams['ship-email'] = $customer->email;
                $buyLinkParams['ship-address'] = $invoice->address1;
                $buyLinkParams['ship-address2'] = !empty($invoice->address2) ? $invoice->address2 : '';
                $buyLinkParams['zip'] = $invoice->postcode;
                $buyLinkParams['prod'] = 'Cart_' . $orderId;
                $buyLinkParams['price'] = $cart->getOrderTotal();
                $buyLinkParams['qty'] = 1;
                $buyLinkParams['type'] = 'PRODUCT';
                $buyLinkParams['tangible'] = 0;
                $buyLinkParams['src'] = $source;
                // url NEEDS a protocol(http or https)
                $buyLinkParams['return-url'] = $returnUrl;
                $buyLinkParams['return-type'] = 'redirect';
                $buyLinkParams['expiration'] = time() + (3600 * 5);
                $buyLinkParams['order-ext-ref'] = $orderId;
                $buyLinkParams['item-ext-ref'] = date('YmdHis');
                $buyLinkParams['customer-ext-ref'] = $customer->email;
                $buyLinkParams['currency'] = strtolower($currency->iso_code);
                $buyLinkParams['language'] = $languageIsoCode;
                $buyLinkParams['test'] = Configuration::get('TWOCHECKOUT_DEMO');
                // sid in this case is the merchant code
                $buyLinkParams['merchant'] = Configuration::get('TWOCHECKOUT_SID');
                $buyLinkParams['dynamic'] = 1;
                $buyLinkParams['signature'] = $this->generateSignature(
                    $buyLinkParams,
                    Configuration::get('TWOCHECKOUT_SECRET_WORD')
                );

                $redirectTo = 'https://secure.2checkout.com/checkout/buy/?' . (http_build_query($buyLinkParams));

        }

        Tools::redirect($redirectTo);
    }


    /**
     * @param      $params
     * @param      $secretWord
     * @param bool $fromResponse
     *
     * @return string
     */
    public function generateSignature(
        $params,
        $secretWord,
        $fromResponse = false
    ) {

        if (!$fromResponse) {
            $signParams = array_filter($params, function ($k) {
                return in_array($k, $this->_signParams);
            }, ARRAY_FILTER_USE_KEY);
        } else {
            $signParams = $params;
            if (isset($signParams['signature'])) {
                unset($signParams['signature']);
            }
        }

        ksort($signParams); // order by key
        // Generate Hash
        $string = '';
        foreach ($signParams as $key => $value) {
            $string .= strlen($value) . $value;
        }

        return bin2hex(hash_hmac('sha256', $string, $secretWord, true));
    }

    /**
     * @param $params
     *
     * @return bool|mixed
     * @throws \PrestaShopException
     */
    public function hookPaymentReturn($params)
    {

        if ((!isset($params['order']) || $params['order']->module != $this->name) || !$this->active) {
            return false;
        }

        /** @var $order \Order */
        $order = $params['order'];
        if (!$order instanceof \Order) {
            PrestaShopLogger::addLog(sprintf('Invalid order from hookPaymentReturn at line %s in file %s', __LINE__, __FILE__));
            throw new PrestaShopException('Invalid order');
        }

        /**
         * Set the current state of the order in Processing
         * only  the first time, default is 0
         */

        if (!$order->current_state) {
            $order->setCurrentState(Configuration::get('PS_OS_PREPARATION'));
            $order->save();
        }

        $order_states = OrderState::getOrderStates($order->id_lang);
        $state_message = '';

        foreach ($order_states as $state) {
            if ($state['id_order_state'] === $order->current_state) {
                $state_message = $state['name'];
            }
        }
        $this->smarty->assign(['order' => $order, 'state' => $state_message]);

        return $this->fetch('module:twocheckout/views/templates/front/payment_return.tpl');
    }


    /**
     * @param        $delivery
     * @param string $stateCode
     * @param string $countryIsoCode
     * @param string $email
     * @return array
     */
    private function getBillingDetails($delivery, string $stateCode, string $countryIsoCode, string $email = '')
    {

        $address = [
            'Address1'    => $delivery->address1,
            'City'        => $delivery->city,
            'State'       => $stateCode,
            'CountryCode' => $countryIsoCode,
            'Email'       => $email,
            'FirstName'   => $delivery->firstname,
            'LastName'    => $delivery->lastname,
            'Phone'       => $delivery->phone,
            'Zip'         => $delivery->postcode,
            'Company'     => $delivery->company,
            'FiscalCode'  => $delivery->vat_number
        ];

        if ($delivery->address2) {
            $address['Address2'] = $delivery->address2;
        }

        return $address;
    }

    /**
     * for safety reasons we only send one Item with the grand total and the Cart_id as ProductName (identifier)
     * sending products order as ONE we dont have to calculate the total fee of the order (product price, tax, discounts etc)
     * @param int   $cart_id
     * @param float $total
     * @return array
     */
    private function getItem(int $cart_id, float $total)
    {
        $items[] = [
            'Code'             => null,
            'Quantity'         => 1,
            'Name'             => 'Cart_' . $cart_id,
            'Description'      => 'N/A',
            'RecurringOptions' => null,
            'IsDynamic'        => true,
            'Tangible'         => false,
            'PurchaseType'     => 'PRODUCT',
            'Price'            => [
                'Amount' => number_format($total, 2, '.', ''),
                'Type'   => 'CUSTOM'
            ]
        ];

        return $items;
    }

    /**
     * @param string $token
     * @param string $currency
     * @param int    $cartId
     * @param int    $orderId
     * @return array
     */
    private function getPaymentDetails(string $token, string $currency, int $cartId, int $orderId)
    {

        return [
            'Type'          => Configuration::get('TWOCHECKOUT_DEMO') == 1 ? 'TEST' : 'EES_TOKEN_PAYMENT',
            'Currency'      => strtolower($currency),
            'CustomerIP'    => $this->getCustomerIp(),
            'PaymentMethod' => [
                'EesToken'           => $token,
                'Vendor3DSReturnURL' => $this->context->link->getModuleLink('twocheckout', 'redirect3ds',
                    ['action' => 'success', 'cart' => $cartId, 'order' => $orderId], true),
                'Vendor3DSCancelURL' => $this->context->link->getModuleLink('twocheckout', 'redirect3ds',
                    ['action' => 'cancel', 'cart' => $cartId, 'order' => $orderId], true)
            ]
        ];
    }

    /**
     * @param $has3ds
     * @return string|null
     */
    public function hasAuthorize3DS($has3ds)
    {
        if (isset($has3ds) && isset($has3ds['Href']) && !empty($has3ds['Href'])) {

            return $has3ds['Href'] . '?avng8apitoken=' . $has3ds['Params']['avng8apitoken'];
        }

        return null;
    }

    /**
     * get customer ip or returns a default ip
     * @return mixed|string
     */
    private function getCustomerIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return $ip;
        }

        return '1.0.0.1';
    }
}
