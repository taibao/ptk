<?php
	class NewsController extends Controller{
		/*新闻列表模块 controller+name*/
		public function actionIndex()
		{
			include_once(MODLE_PATH."NewsModel.php");
			$this->model = new NewsModel;
			$result = $this->model->select();
			$this->assign('result',$result);
			$this->view();
		}

	
	}
