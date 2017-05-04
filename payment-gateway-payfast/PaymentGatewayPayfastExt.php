<?php
defined( 'MW_PATH' ) || exit( 'No direct script access allowed' );

/**
 * Payment gateway - PayFast
 *
 * Retrieve payments using payfast
 *
 * @package MailWizz EMA
 * @subpackage Payment Gateway PayFast
 * @author PayFast <support@payfast.co.za>
 * @link http://www.payfast.co.za/
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */

class PaymentGatewayPayfastExt extends ExtensionInit
{
    // name of the extension as shown in the backend panel
    public $name = 'Payment gateway - Payfast';

    // description of the extension as shown in backend panel
    public $description = 'Retrieve payments using payfast';

    // current version of this extension
    public $version = '1.0';

    // the author name
    public $author = 'PayFast';

    // author website
    public $website = 'http://www.payfast.co.za/';

    // contact email address
    public $email = 'support@payfast.co.za';

    // in which apps this extension is allowed to run
    public $allowedApps = array('customer', 'backend');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = false;

    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;

    // the extension model
    protected $_extModel;

    // run the extension
    public function run()
    {
        Yii::import('ext-payment-gateway-payfast.common.models.*');

        if ( $this->isAppName( 'backend' ) )
        {
            // handle all backend related tasks
            $this->backendApp();
        }
        elseif ( $this->isAppName( 'customer' ) && $this->getOption( 'status', 'disabled' ) == 'enabled' )
        {
            // handle all customer related tasks
            $this->customerApp();
        }
    }

    // Add the landing page for this extension (settings/general info/etc)
    public function getPageUrl()
    {
        return Yii::app()->createUrl('payment_gateway_ext_payfast/index');
    }

    // handle all backend related tasks
    protected function backendApp()
    {
        $hooks = Yii::app()->hooks;

        // register the url rule to resolve the extension page.
        Yii::app()->urlManager->addRules( array(
            array( 'payment_gateway_ext_payfast/index', 'pattern' => 'payment-gateways/payfast' ),
            array( 'payment_gateway_ext_payfast/<action>', 'pattern' => 'payment-gateways/payfast/*' ),
        ));

        // add the backend controller
        Yii::app()->controllerMap['payment_gateway_ext_payfast'] = array(
            'class' => 'ext-payment-gateway-payfast.backend.controllers.Payment_gateway_ext_payfastController',
            'extension' => $this,
        );

        // register the gateway in the list of available gateways.
        $hooks->addFilter( 'backend_payment_gateways_display_list', array( $this, '_registerGatewayForBackendDisplay' ) );
    }

    // register the gateway in the available gateways list
    public function _registerGatewayForBackendDisplay( array $registeredGateways = array() )
    {
        if ( isset( $registeredGateways['payfast'] ) )
        {
            return $registeredGateways;
        }

        $registeredGateways['payfast'] = array(
            'id'            => 'payfast',
            'name'          => Yii::t('ext_payment_gateway_payfast', 'Payfast'),
            'description'   => Yii::t('ext_payment_gateway_payfast', 'Retrieve payments using payfast'),
            'status'        => $this->getOption( 'status', 'disabled' ),
            'sort_order'    => (int)$this->getOption( 'sort_order', 1 ),
            'page_url'      => $this->getPageUrl(),
        );

        return $registeredGateways;
    }

    // handle all customer related tasks
    protected function customerApp()
    {
        $hooks = Yii::app()->hooks;

        // import the utils
        Yii::import( 'ext-payment-gateway-payfast.customer.components.utils.*' );

        // register the url rule to resolve the ipn request.
        Yii::app()->urlManager->addRules( array(
            array( 'payment_gateway_ext_payfast/ipn', 'pattern' => 'payment-gateways/payfast/ipn' ),
        ));

        // add the backend controller
        Yii::app()->controllerMap['payment_gateway_ext_payfast'] = array(
            'class' => 'ext-payment-gateway-payfast.customer.controllers.Payment_gateway_ext_payfastController',
            'extension' => $this,
        );

        // set the controller unprotected so payfast can post freely
        $unprotected = ( array )Yii::app()->params->itemAt( 'unprotectedControllers' );
        array_push( $unprotected, 'payment_gateway_ext_payfast' );
        Yii::app()->params->add( 'unprotectedControllers', $unprotected );

        // remove the csrf token validation
        $request = Yii::app()->request;
        if ( $request->isPostRequest && $request->enableCsrfValidation )
        {
            $url = Yii::app()->urlManager->parseUrl( $request );
            $routes = array( 'price_plans', 'payment_gateway_ext_payfast/ipn' );
            foreach ( $routes as $route )
            {
                if ( strpos( $url, $route ) === 0 )
                {
                    Yii::app()->detachEventHandler( 'onBeginRequest', array( $request, 'validateCsrfToken' ) );
                    Yii::app()->attachEventHandler( 'onBeginRequest', array( $this, 'validateCsrfToken' ) );
                    break;
                }
            }
        }

        // hook into drop down list and add the payfast option
        $hooks->addFilter( 'customer_price_plans_payment_methods_dropdown', array( $this, '_registerGatewayInCustomerDropDown' ) );
    }

    // this replacement is needed to avoid csrf token validation and other errors
    public function validateCsrfToken()
    {
        Yii::app()->request->enableCsrfValidation = false;
    }

    // register the assets for customer area
    public function registerCustomerAssets()
    {
        $assetsUrl = Yii::app()->assetManager->publish( dirname( __FILE__ ).'/assets/customer', false, -1, MW_DEBUG );
        Yii::app()->clientScript->registerScriptFile( $assetsUrl . '/js/payment-form.js' );
    }

    // this is called by the customer app to process the payment
    // must be implemented by all payment gateways
    public function getPaymentHandler()
    {
        return Yii::createComponent(array(
            'class' => 'ext-payment-gateway-payfast.customer.components.utils.PayfastPaymentHandler',
        ) );
    }

    // extension main model
    public function getExtModel()
    {
        if ( $this->_extModel !== null )
        {
            return $this->_extModel;
        }

        $this->_extModel = new PaymentGatewayPayfastExtModel();
        return $this->_extModel->setExtensionInstance( $this )->populate();
    }

    //
    public function _registerGatewayInCustomerDropDown( $paymentMethods )
    {
        if ( isset( $paymentMethods['payfast'] ) )
        {
            return $paymentMethods;
        }
        $paymentMethods['payfast'] = Yii::t( 'ext_payment_gateway_payfast', 'Payfast' );
        return $paymentMethods;
    }
}