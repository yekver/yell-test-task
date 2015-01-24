<?php

namespace app\controllers;

use app\models\LinkParser;
use Yii;
use yii\web\Controller;
use app\models\LoginForm;

class SiteController extends Controller
{
    public function actionIndex()
    {
		if (\Yii::$app->user->isGuest) {
			return $this->actionLogin();
		}

		$model = new LinkParser();
		$inksList = null;
		if($model->load(Yii::$app->request->post()) && $model->validate()) {
			$inksList = $model->getIndexPageLinks();
			$model->saveLinks($inksList);
		}

		return $this->render('index', [
			'model' => $model,
			'links' => $inksList
		]);
    }

    public function actionLogin()
    {
		if (!\Yii::$app->user->isGuest) {
			return $this->goHome();
		}

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
