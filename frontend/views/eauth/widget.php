<?php

use yii\helpers\Html;
use yii\web\View;

/** @var $this View */
/** @var $id string */
/** @var $services stdClass[] See EAuth::getServices() */
/** @var $action string */
/** @var $popup bool */
/** @var $assetBundle string Alias to AssetBundle */

Yii::createObject(['class' => $assetBundle])->register($this);

// Open the authorization dilalog in popup window.
if ($popup) {
	$options = [];
	foreach ($services as $name => $service) {
		$options[$service->id] = $service->jsArguments;
	}
	$this->registerJs('$("#' . $id . '").eauth(' . json_encode($options) . ');');
}

?>

<div class="formShare" id="<?php echo $id; ?>">
	<div class="formShare_title">
		Войти
		<br/>
		через
		<br/>
		соцсети:
	</div>
	<ul>
		<?php
		foreach ($services as $name => $service) {
			echo '<li>';
			echo Html::a("", [$action, 'service' => $name], [
				'class' =>  $service->id . '',
				'data-eauth-service' => $service->id,
			]);
			echo '</li>';
		}?>

	</ul>
</div>
