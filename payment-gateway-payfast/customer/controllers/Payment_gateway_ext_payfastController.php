<?php
defined( 'MW_PATH' ) || exit( 'No direct script access allowed' );

/**
 * Controller file for service process.
 *
 * @package MailWizz EMA
 * @subpackage Payment Gateway Payfast
 * @author PayFast <support@payfast.co.za>
 * @link http://www.payfast.co.za/
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */

class Payment_gateway_ext_payfastController extends Controller
{
    // the extension instance
    public $extension;

    /**
     * Process the IPN
     */
    public function actionIpn()
    {
        require_once ('payfast_common.inc');
        pflog( 'PayFast ITN call received' );

        $pfError = false;
        $pfErrMsg = '';
        $pfDone = false;
        $pfData = array();
        $pfParamString = '';

        //// Notify PayFast that information has been received
        if( !$pfError && !$pfDone )
        {
            header( 'HTTP/1.0 200 OK' );
            flush();
        }

        if( !$pfError && !$pfDone )
        {
            pflog( 'Get posted data' );

            // Posted variables from ITN
            $pfData = pfGetData();

            pflog( 'PayFast Data: '. print_r( $pfData, true ) );

            if( $pfData === false )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ACCESS;
            }
        }

        if( !$pfError && !$pfDone )
        {
            pflog( 'Verify source IP' );

            if( !pfValidIP( $_SERVER['REMOTE_ADDR'] ) )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_SOURCE_IP;
            }
        }

        $pfHost = $PayFast_sandbox ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';

        if( !$pfError )
        {
            pflog( 'Verify data received' );

            $pfValid = pfValidData( $pfHost, $pfParamString );

            if( !$pfValid )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ACCESS;
            }
        }

        if ( $pfError )
        {
            pflog('Error occurred: ' . $pfErrMsg);
        }

        if ( !Yii::app()->request->isPostRequest )
        {
            $this->redirect( array( 'price_plans/index' ) );
        }

        $postData = Yii::app()->params['POST'];
        if ( !$postData->itemAt( 'm_payment_id' ) )
        {
            Yii::app()->end();
        }

        $transaction = PricePlanOrderTransaction::model()->findByAttributes( array(
            'payment_gateway_transaction_id' => $postData->itemAt( 'm_payment_id' ),
            'status' => PricePlanOrderTransaction::STATUS_PENDING_RETRY,
        ) );

        if ( empty( $transaction ) )
        {
            Yii::app()->end();
        }

        $newTransaction = clone $transaction;
        $newTransaction->transaction_id = null;
        $newTransaction->transaction_uid = null;
        $newTransaction->isNewRecord = true;
        $newTransaction->date_added = new CDbExpression( 'NOW()' );
        $newTransaction->status = PricePlanOrderTransaction::STATUS_FAILED;
        $newTransaction->payment_gateway_response = print_r( $postData->toArray(), true );
        $newTransaction->payment_gateway_transaction_id = $postData->itemAt( 'm_payment_id' );

        $model = $this->extension->getExtModel();

        $request = AppInitHelper::simpleCurlPost( $model->getModeUrl(), $postData->toArray() );

        if ( $request['status'] != 'success' )
        {
            $newTransaction->save( false );
            Yii::app()->end();
        }

        $paymentStatus = strtolower( trim( $postData->itemAt( 'payment_status') ) );
        $paymentPending = strpos( $paymentStatus, 'pending' ) === 0;
        $paymentFailed  = strpos( $paymentStatus, 'failed' ) === 0;
        $paymentSuccess = strpos( $paymentStatus, 'complete' ) === 0;

        $order = $transaction->order;

        if ( $order->status == PricePlanOrder::STATUS_COMPLETE )
        {
            $newTransaction->save( false );
            Yii::app()->end();
        }

        if ( $paymentFailed )
        {
            $order->status = PricePlanOrder::STATUS_FAILED;
            $order->save(false);
             $transaction->status = PricePlanOrderTransaction::STATUS_FAILED;
            $transaction->save( false );

            $newTransaction->save( false );

            Yii::app()->end();
        }

        if ( $paymentPending )
        {
            $newTransaction->status = PricePlanOrderTransaction::STATUS_PENDING_RETRY;
            $newTransaction->save( false );
            Yii::app()->end();
        }

        $order->status = PricePlanOrder::STATUS_COMPLETE;
        $order->save( false );

        $transaction->status = PricePlanOrderTransaction::STATUS_SUCCESS;
        $transaction->save( false );

        $newTransaction->status = PricePlanOrderTransaction::STATUS_SUCCESS;
        $newTransaction->save( false );

        Yii::app()->end();
    }
}