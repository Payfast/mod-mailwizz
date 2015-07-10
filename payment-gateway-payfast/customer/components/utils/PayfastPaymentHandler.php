<?php
defined( 'MW_PATH' ) || exit( 'No direct script access allowed' );

/**
 * PayfastPaymentHandler
 *
 * @package MailWizz EMA
 * @subpackage Payment Gateway Payfast
 * @author PayFast <support@payfast.co.za>
 * @link http://www.payfast.co.za/
 * @copyright 2013-2015 PayFast (http://www.payfast.co.za)
 * @license http://www.opensource.org/licenses/
 * @since 1.0
 */

class PayfastPaymentHandler extends PaymentHandlerAbstract
{
    // render the payment form
    public function renderPaymentView()
    {
        $order = $this->controller->getData( 'order' );
        $model = $this->extension->getExtModel();

        $cancelUrl = Yii::app()->createAbsoluteUrl( 'price_plans/index' );
        $returnUrl = Yii::app()->createAbsoluteUrl( 'price_plans/index' );
        $notifyUrl = Yii::app()->createAbsoluteUrl( 'payment_gateway_ext_payfast/ipn' );

        $assetsUrl = Yii::app()->assetManager->publish( Yii::getPathOfAlias( $this->extension->getPathAlias() ) . '/assets/customer', false, -1, MW_DEBUG );
        Yii::app()->clientScript->registerScriptFile( $assetsUrl . '/js/payment-form.js' );

        $customVars = sha1( StringHelper::uniqid() );
        $view = $this->extension->getPathAlias() . '.customer.views.payment-form';

        $this->controller->renderPartial( $view, compact( 'model', 'order', 'cancelUrl', 'returnUrl', 'notifyUrl', 'customVars' ) );
    }

    // mark the order as pending retry
    public function processOrder()
    {
        $request = Yii::app()->request;

        $transaction = $this->controller->getData( 'transaction' );
        $order = $this->controller->getData( 'order' );

        $order->status = PricePlanOrder::STATUS_PENDING;
        $order->save( false );


        $transaction->payment_gateway_name = 'Payfast - www.payfast.co.za';
        $transaction->payment_gateway_transaction_id = $request->getPost( 'm_payment_id' );
        $transaction->status = PricePlanOrderTransaction::STATUS_PENDING_RETRY;
        $transaction->save( false );

        $message = Yii::t( 'payment_gateway_ext_payfast', 'Your order is in "{status}" status, it usually takes a few minutes to be processed and if everything is fine, your pricing plan will become active!', array(
            '{status}' => Yii::t( 'orders', $order->status ),
        ));

        if ( $request->isAjaxRequest )
        {
            return $this->controller->renderJson( array(
                'result'  => 'success',
                'message' => $message,
            ) );
        }

        Yii::app()->notify->addInfo( $message );
        $this->controller->redirect( array( 'price_plans/index' ) );
    }
}
