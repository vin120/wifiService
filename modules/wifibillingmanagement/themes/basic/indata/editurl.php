<?php
$this->title = 'Wifi Billing Management';


use app\modules\wifibillingmanagement\themes\basic\myasset\ThemeAsset;

ThemeAsset::register($this);
$baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

//$assets = '@app/modules/membermanagement/themes/basic/static';
//$baseUrl = Yii::$app->assetManager->publish($assets);

?>
<?php 
	use yii\helpers\Html;

?>

<!DOCTYPE html>
<html>
<head>
	<title>上网</title>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl?>css/public.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $baseUrl?>css/edit.css">
</head>
<body>
	<!-- header start -->
	
	<!-- header end -->
	<!-- main start -->
	<main id="main" style="margin-left:1%">
		<!-- asideNav start -->
		<aside id="asideNav" class="l"></aside>
		<!-- asideNav end -->
		<!-- content start -->
		<div class="search" style="width: 400px;margin-left:auto;margin-right:auto;">
				<form method="post">
				<label>
					<span >Name :</span>
					<input type="text" name="name" id="name" value="<?php echo isset($data[0]['name'])?$data[0]['name']:'' ?>" ></input>
				</label>
				<br/><br/><br/>
				<label>
					<span>URL :</span>
				
					<input type="text" name="url" id="url" value="<?php echo isset($data[0]['url'])?$data[0]['url']:'' ?>" ></input>
					
				</label>
				<br/><br/><br/>
					<label>
					<span>Remark:</span>
					
					<input type="text" name="remark" id="remark" value="<?php echo isset($data[0]['remark'])?$data[0]['remark']:'' ?>" ></input>
					
				</label>
					
				<br/><br/><br/>
				<input type="hidden" name="id" value="<?php  echo isset($data[0]['id'])?$data[0]['id']:''?>">
				<span class="btn"><input type="submit" id="mysubmit" value="submit"></input></span>
		<!-- content end -->
		</form>
		</div>
	</main>
	<!-- main end -->
	<script type="text/javascript" src="<?php echo $baseUrl?>js/jquery-2.2.2.min.js"></script>
	<script type="text/javascript" src="<?php echo $baseUrl?>js/public.js"></script>
	<script type="text/javascript">
$(function(){
	 
	$("#mysubmit").click(function(){
		 var wifi_name = $("#name").val();
		 var wifi_flow = $("#url").val();
		 var sale_price = $("#remark").val();
    	 if(wifi_name==''){
            alert("name can't empty");
            return false;
         }
    	 else if(wifi_flow==''){
    		 alert("url can't empty");
    		 return false;
        	 }
    	 else if(sale_price==''){
    		 alert("remark can't empty");
    		 return false;
        	 }
         
    })
})
	</script>
</body>
</html>