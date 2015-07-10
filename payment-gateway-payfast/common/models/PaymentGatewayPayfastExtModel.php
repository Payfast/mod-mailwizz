<?php
defined( 'MW_PATH' ) || exit( 'No direct script access allowed' );

/**
 * PaymentGatewayPayfastExtModel
 *
 * @package MailWizz EMA
 * @subpackage Payment Gateway Payfast
 * @author PayFast <support@payfast.co.za>
 * @link http://www.payfast.co.za/
 * @copyright 2013-2015 PayFast (http://www.payfast.co.za)
 * @license http://www.opensource.org/licenses/
 */

class PaymentGatewayPayfastExtModel extends FormModel
{

    const STATUS_ENABLED = 'enabled';

    const STATUS_DISABLED = 'disabled';

    const MODE_SANDBOX = 'sandbox';

    const MODE_LIVE = 'live';

    protected $_extensionInstance;

    public $merchantId;

    public $merchantKey;

    public $passphrase;

    public $mode = 'sandbox';

    public $status = 'disabled';

    public $sort_order = 1;

    public function rules()
    {
        $rules = array(
            array( 'merchantId, merchantKey, passphrase', 'safe' ),
            array( 'mode, status, sort_order', 'required' ),
            array( 'status', 'in', 'range' => array_keys($this->getStatusesDropDown() ) ),
            array( 'mode', 'in', 'range' => array_keys( $this->getModes() ) ),
            array( 'sort_order', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 999 ),
            array( 'sort_order', 'length', 'min' => 1, 'max' => 3 ),
        );

        return CMap::mergeArray( $rules, parent::rules() );
    }

    public function save()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array( 'merchantId', 'merchantKey', 'passphrase', 'mode', 'status', 'sort_order' );
        foreach ( $attributes as $name )
        {
            $extension->setOption( $name, $this->$name );
        }
        return $this;
    }

    public function populate()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array( 'merchantId', 'merchantKey', 'passphrase', 'mode', 'status', 'sort_order' );
        foreach ( $attributes as $name )
        {
            $this->$name = $extension->getOption( $name, $this->$name );
        }
        return $this;
    }

    public function attributeLabels()
    {
        $labels = array(
            'merchantId' => Yii::t( 'ext_payment_gateway_payfast', 'Merchant ID' ),
            'merchantKey' => Yii::t( 'ext_payment_gateway_payfast', 'Merchant Key' ),
            'passphrase' => Yii::t( 'ext_payment_gateway_payfast', 'Passphrase' ),
            'mode' => Yii::t( 'ext_payment_gateway_payfast', 'Mode' ),
            'status' => Yii::t( 'app', 'Status' ),
            'sort_order' => Yii::t( 'app', 'Sort order' ),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array();
        return CMap::mergeArray( $placeholders, parent::attributePlaceholders() );
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'merchantId' => Yii::t( 'ext_payment_gateway_payfast', 'Your PayFast merchant ID' ),
            'merchantKey' => Yii::t( 'ext_payment_gateway_payfast', 'Your PayFast merchant Key' ),
            'passphrase' => Yii::t( 'ext_payment_gateway_payfast', 'Your PayFast Passphrase' ),
            'mode' => Yii::t( 'ext_payment_gateway_payfast', 'Whether the payments are live or run in sandbox' ),
            'status' => Yii::t( 'ext_payment_gateway_payfast', 'Whether this gateway is enabled and can be used for payments processing' ),
            'sort_order' => Yii::t( 'ext_payment_gateway_payfast', 'The sort order for this gateway' ),
        );

        return CMap::mergeArray( $texts, parent::attributeHelpTexts() );
    }

    public function getStatusesDropDown()
    {
        return array(
            self::STATUS_DISABLED   => Yii::t( 'app', 'Disabled' ),
            self::STATUS_ENABLED    => Yii::t( 'app', 'Enabled' ),
        );
    }

    public function getSortOrderDropDown()
    {
        $options = array();
        for ( $i = 0; $i < 100; ++$i )
        {
            $options[$i] = $i;
        }
        return $options;
    }

    public function getModes()
    {
        return array(
            self::MODE_SANDBOX => ucfirst( Yii::t( 'ext_payment_gateway_payfast', self::MODE_SANDBOX ) ),
            self::MODE_LIVE => ucfirst( Yii::t( 'ext_payment_gateway_payfast', self::MODE_LIVE ) ),
        );
    }

    public function getModeUrl()
    {
        if ( $this->mode == self::MODE_LIVE )
        {
            return 'https://www.payfast.co.za/eng/process';
        }
        return 'https://sandbox.payfast.co.za/eng/process';
    }

    public function setExtensionInstance( $instance )
    {
        $this->_extensionInstance = $instance;
        return $this;
    }

    public function getExtensionInstance()
    {
        if ( $this->_extensionInstance !== null )
        {
            return $this->_extensionInstance;
        }
        return $this->_extensionInstance = Yii::app()->extensionsManager->getExtensionInstance( 'payment-gateway-payfast' );
    }
}
