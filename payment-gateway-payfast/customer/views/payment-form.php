<?php
defined( 'MW_PATH' ) || exit( 'No direct script access allowed' );

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package PayFast
 * @subpackage Payment Gateway Payfast
 * @author PayFast <support@payfast.co.za>
 * @link http://www.payfast.co.za/
 * @copyright 2013-2015 PayFast (http://www.payfast.co.za)
 * @license http://www.opensource.org/licenses/
 */
// generate signature
$data = array (
    'merchant_id' => $model->merchantId,
    'merchant_key' => $model->merchantKey,
    'return_url' => $returnUrl,
    'cancel_url' => $cancelUrl,
    'notify_url' => $notifyUrl,
    'm_payment_id' => $customVars,
    'amount' => round( $order->total, 2 ),
    'item_name' => Yii::t( 'price_plans', 'Price plan' ).': '. $order->plan->name,
);
    foreach ( $data as $key => $val )
        $pfOutput .= $key .'='. urlencode( trim( $val ) ) .'&';

        $passPhrase = $model->passphrase;
        if( empty( $passPhrase ) )
        {
            $pfOutput = substr( $pfOutput, 0, -1 );
        }
        else
        {
            $pfOutput = $pfOutput."passphrase=".urlencode( $passPhrase );
        }

        $signature = md5( $pfOutput );

// create form
echo CHtml::form($model->getModeUrl(), 'post', array(
    'id'         => 'payfast-hidden-form',
    'data-order' => Yii::app()->createUrl('price_plans/order'),
));
echo CHtml::hiddenField( 'merchant_id', $model->merchantId );
echo CHtml::hiddenField( 'merchant_key', $model->merchantKey );

echo CHtml::hiddenField( 'return_url', $returnUrl );
echo CHtml::hiddenField( 'cancel_url', $cancelUrl );
echo CHtml::hiddenField( 'notify_url', $notifyUrl );

echo CHtml::hiddenField( 'm_payment_id', $customVars);
echo CHtml::hiddenField( 'amount', round( $order->total, 2 ) );
echo CHtml::hiddenField( 'item_name', Yii::t( 'price_plans', 'Price plan' ).': '. $order->plan->name );

echo CHtml::hiddenField( 'signature', $signature );

echo CHtml::hiddenField( 'user_agent', 'MailWizz 1.3' );

?>
    <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
        Payfast - www.payfast.co.za <br />
        <?php echo Yii::t( 'ext_payment_gateway_payfast', 'You will be redirected to pay securely via PayFast' );?>
    </p>
    <p><button class="btn btn-success pull-right"><i class="fa fa-credit-card"></i> <?php echo Yii::t( 'price_plans', 'Submit payment' )?></button></p>

<?php echo CHtml::endForm(); ?>