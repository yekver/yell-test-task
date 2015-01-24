<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LinkParser */
/* @var $links array */
?>

<div class="site-parser">
	<h2>Парсер ссылок с главной страницы сайта</h2>

	<?php $form = ActiveForm::begin([
		'id' => 'parser-form',
		'options' => ['class' => 'form-horizontal'],
		'fieldConfig' => [
			'template' => "<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
			'labelOptions' => ['class' => 'control-label'],
		],
	]); ?>

	<?= $form->field($model, 'site')->textInput(['placeholder' => 'http://example.com/']); ?>

	<?= Html::submitButton('Запуск', ['class' => 'btn btn-primary']) ?>


	<?php ActiveForm::end();

	if($links !== null) : ?>
		<h3>Найденые ссылки:</h3>
		<?= Html::ul($links, ['class' => 'list-group',
			'item' => function($link) {
				return '<li class="list-group-item">' . Html::a(Html::encode($link), Html::encode($link), ['target' => '_blank']) . '</li>';
			}]);
	endif; ?>
</div>