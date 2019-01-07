<?php
/*
 * 中文圆形印章类
 * @author houjit.com
 * @create on 10:03 2019-01-07
 * @example:
 * $seal = new circleSeal('你我他坐站走东西南北中',75,6,24,0,0,16,40);
 * $seal->doImg();
 */
namespace houjit\seal;

class circleSeal {
    private $sealString;  //印章字符
    private $strMaxLeng;  //最大字符长度
    private $sealRadius;  //印章半径
    private $rimWidth;   //边框厚度
    private $innerRadius;  //内圆半径
    private $startRadius;  //五角星半径
    private $startAngle;  //五角星倾斜角度
    private $backGround;  //印章颜色
    private $centerDot;   //圆心坐标
    private $img;      //图形资源句柄
    private $font;     //指定的字体
    private $fontSize;   //指定字体大小
    private $width;     //图片宽度
    private $height;    //图片高度
    private $points;    //五角星各点坐标
    private $charRadius;  //字符串半径
    private $numRadius;  //数字半径
    private $charAngle;   //字符串倾斜角度
    private $spacing;    //字符间隔角度
    private  $sealNum;    //数字
    private  $sealName;    //章名字
    private  $yheight; //字符的y坐标;



    //构造方法
    public function __construct($str ='',$num = '',$rad = 100, $rmwidth = 6, $strad = 28, $stang = 0, $crang = 0, $fsize = 16, $inrad =0){
        $this->sealString  = empty($str) ? '印章测试字符串' : $str;
        $this->sealNum  = empty($num) ? '010101010' : $num;
        $this->strMaxLeng  = 18;
        $this->sealRadius  = $rad;
        $this->rimWidth   = $rmwidth;
        $this->startRadius = $strad;
        $this->startAngle  = $stang;
        $this->charAngle  = $crang;
        $this->centerDot  = array('x'=>$rad, 'y'=>$rad);
        $this->font     = dirname(__FILE__) .'/simhei.ttf';
        $this->fontSize   = $fsize;
        $this->innerRadius = $inrad;  //默认0,没有
        $this->spacing   = 1;
        $this->width    = 2 * $this->sealRadius;
        $this->height    = 2 * $this->sealRadius;
        $this->yheight = 150; //字符的y坐标
    }

    //创建图片资源
    private function createImg(){
        $this->img     = imagecreate($this->width, $this->height);
        imagecolorresolvealpha($this->img,255,255,255,127);
        $this->backGround  = imagecolorallocate($this->img,255,0,0);
    }

    //画印章边框imagerectangle
    private function drawRim(){
        for($i=0;$i<$this->rimWidth;$i++){
            imagearc($this->img,$this->centerDot['x'],$this->centerDot['y'],$this->width - $i,$this->height - $i,0,360,$this->backGround);
        }
    }

    //画印章边框
    private function drawSqu(){
        for($i=0;$i<$this->rimWidth;$i++){
            imagerectangle($this->img,0+$i,0+$i,120-$i,120-$i,$this->backGround);
        }
    }
    //画正方形的名字
    private function drawName1(){
        //编码处理
        $this->sealName .= '印';
        $charset = mb_detect_encoding($this->sealName);
        if($charset != 'UTF-8'){
            $this->sealName = mb_convert_encoding($this->sealName, 'UTF-8', 'GBK');
        }
        //相关计量
        $leng  = mb_strlen($this->sealName,'utf8'); //字符串长度
        $nums = array();
        //拆分并写入字符串
        for($i=0;$i<$leng;$i++){
            $nums[] = mb_substr($this->sealName,$i,1,'utf8');
        }
        imagettftext($this->img,35, 0, 60,50, $this->backGround, $this->font, $nums[0]);
        imagettftext($this->img,35, 0, 60,105, $this->backGround, $this->font, $nums[1]);
        if($leng==3){
            imagettftext($this->img,35, 0, 10,77.5, $this->backGround, $this->font, $nums[2]);
        }else{
            imagettftext($this->img,35, 0, 10,50, $this->backGround, $this->font, $nums[2]);
            imagettftext($this->img,35, 0, 10,105, $this->backGround, $this->font, $nums[3]);
        }

    }


    //画内圆
    private function drawInnerCircle(){
        imagearc($this->img,$this->centerDot['x'],$this->centerDot['y'],2*$this->innerRadius,2*$this->innerRadius,0,360,$this->backGround);
    }
    //画下面的数字
    private function drawNum(){
        //编码处理
        $charset = mb_detect_encoding($this->sealNum);
        if($charset != 'UTF-8'){
            $this->sealNum = mb_convert_encoding($this->sealNum, 'UTF-8', 'GBK');
        }
        //相关计量
        $this->numRadius = $this->sealRadius - $this->rimWidth - 5; //数字半径
        $leng  = mb_strlen($this->sealNum,'utf8'); //字符串长度
//        if($leng > $this->strMaxLeng) $leng = $this->strMaxLeng;
        $avgAngle  = 80 / 9;  //平均字符倾斜度

        //拆分并写入字符串
        $nums = array(); //字符数组
        for($i=0;$i<$leng;$i++){
            $nums[] = mb_substr($this->sealNum,$i,1,'utf8');
            $r =95 + $this->charAngle + $avgAngle*($i - $leng/2);   //坐标角度
            $R = 720 - $this->charAngle + $avgAngle*($leng-2*$i-1)/2 ;  //字符角度
            $x = $this->centerDot['x'] + $this->numRadius * cos(deg2rad($r)); //字符的x坐标
            $y = $this->centerDot['y'] + $this->numRadius * sin(deg2rad($r)); //字符的y坐标
            imagettftext($this->img,10, $R, $x, $y, $this->backGround, $this->font, $nums[$i]);
        }
    }

    //画中间章的名字
    private function drawName(){
        //编码处理
        $charset = mb_detect_encoding($this->sealName);
        if($charset != 'UTF-8'){
            $this->sealName = mb_convert_encoding($this->sealName, 'UTF-8', 'GBK');
        }
        //相关计量
        $leng  = mb_strlen($this->sealName,'utf8'); //字符串长度

        //拆分并写入字符串
        $nums = array(); //字符数组
        for($i=0;$i<$leng;$i++){
            $nums[] = mb_substr($this->sealName,$i,1,'utf8');
//            $r =99 + $this->charAngle + $avgAngle*($i - $leng/2);   //坐标角度
//            $R = 720 - $this->charAngle + $avgAngle*($leng-2*$i-1)/2 ;  //字符角度
            $x = 44+$i*22; //字符的x坐标
            $y = $this->yheight; //字符的y坐标
            imagettftext($this->img,18, 0, $x, $y, $this->backGround, $this->font, $nums[$i]);
        }
    }

    //画字符串
    private function drawString(){
        //编码处理
        $charset = mb_detect_encoding($this->sealString);
        if($charset != 'UTF-8'){
            $this->sealString = mb_convert_encoding($this->sealString, 'UTF-8', 'GBK');
        }

        //相关计量
        $this->charRadius = $this->sealRadius - $this->rimWidth - $this->fontSize-5; //字符串半径
        $leng  = mb_strlen($this->sealString,'utf8'); //字符串长度
        if($leng > $this->strMaxLeng) $leng = $this->strMaxLeng;
        $avgAngle  = 270 / ($this->strMaxLeng);  //平均字符倾斜度

        //拆分并写入字符串
        $words = array(); //字符数组
        for($i=0;$i<$leng;$i++){
            $words[] = mb_substr($this->sealString,$i,1,'utf8');
            $r = 630 + $this->charAngle + $avgAngle*($i - $leng/2);  //坐标角度
            $R = 720 - $this->charAngle + $avgAngle*($leng-2*$i-1)/2 ;  //字符角度
            $x = $this->centerDot['x'] + $this->charRadius * cos(deg2rad($r)); //字符的x坐标
            $y = $this->centerDot['y'] + $this->charRadius * sin(deg2rad($r)); //字符的y坐标
            imagettftext($this->img, $this->fontSize, $R, $x, $y, $this->backGround, $this->font, $words[$i]);
        }
    }


    //画椭圆字符串
    private function drawString1(){
        //编码处理
        $charset = mb_detect_encoding($this->sealString);
        if($charset != 'UTF-8'){
            $this->sealString = mb_convert_encoding($this->sealString, 'UTF-8', 'GBK');
        }
        //相关计量
        $charRadiusy = $this->centerDot['y'] - $this->rimWidth - $this->fontSize-2; //字符串y
        $charRadiusx = $this->centerDot['x'] - $this->rimWidth - $this->fontSize-2; //字符串x
        $leng  = mb_strlen($this->sealString,'utf8'); //字符串长度
        if($leng > $this->strMaxLeng) $leng = $this->strMaxLeng;
        $avgAngle  = 270 / $this->strMaxLeng;  //平均字符倾斜度
        //拆分并写入字符串
        $words = array(); //字符数组
        for($i=0;$i<$leng;$i++){
            $words[] = mb_substr($this->sealString,$i,1,'utf8');
            $r = 630 + $this->charAngle + $avgAngle*($i - $leng/2);  //坐标角度
            $R = 720- $this->charAngle + $avgAngle*($leng-2*$i-1)/2;  //字符角度
            $x = $this->centerDot['x'] + $charRadiusx * cos(deg2rad($r))+2; //字符的x坐标
            $y = $this->centerDot['y'] + $charRadiusy * sin(deg2rad($r)); //字符的y坐标
            imagettftext($this->img, $this->fontSize, $R, $x, $y, $this->backGround, $this->font,$words[$i]);
        }
    }

    //画下面的数字
    private function drawNum1(){
        //编码处理
        $charset = mb_detect_encoding($this->sealNum);
        if($charset != 'UTF-8'){
            $this->sealNum = mb_convert_encoding($this->sealNum, 'UTF-8', 'GBK');
        }
        //相关计量
        $charRadiusy = $this->centerDot['y'] - $this->rimWidth - $this->fontSize+15; //字符串y
        $charRadiusx = $this->centerDot['x'] - $this->rimWidth - $this->fontSize-2; //字符串x
        $leng  = mb_strlen($this->sealNum,'utf8'); //字符串长度
//        if($leng > $this->strMaxLeng) $leng = $this->strMaxLeng;
        $avgAngle  = 80 / 9;  //平均字符倾斜度

        //拆分并写入字符串
        $nums = array(); //字符数组
        for($i=0;$i<$leng;$i++){
            $nums[] = mb_substr($this->sealNum,$i,1,'utf8');
            $r =97 + $this->charAngle + $avgAngle*($i - $leng/2);   //坐标角度
            $R = 720 - $this->charAngle + $avgAngle*($leng-2*$i-1)/2 ;  //字符角度
            $x = $this->centerDot['x'] + $charRadiusx * cos(deg2rad($r)); //字符的x坐标
            $y = $this->centerDot['y'] + $charRadiusy * sin(deg2rad($r)); //字符的y坐标
            imagettftext($this->img,10, $R, $x, $y, $this->backGround, $this->font, $nums[$i]);
        }
    }

    //画五角星
    private function drawStart(){
        $ang_out = 18 + $this->startAngle;
        $ang_in = 56 + $this->startAngle;
        $rad_out = $this->startRadius;
        $rad_in = $rad_out * 0.382;
        for($i=0;$i<5;$i++){
            //五个顶点坐标
            $this->points[] = $rad_out * cos(2*M_PI/5*$i - deg2rad($ang_out)) + $this->centerDot['x'];
            $this->points[] = $rad_out * sin(2*M_PI/5*$i - deg2rad($ang_out)) + $this->centerDot['y'];

            //内凹的点坐标
            $this->points[] = $rad_in * cos(2*M_PI/5*($i+1) - deg2rad($ang_in)) + $this->centerDot['x'];
            $this->points[] = $rad_in * sin(2*M_PI/5*($i+1) - deg2rad($ang_in)) + $this->centerDot['y'];
        }
        imagefilledpolygon($this->img, $this->points, 10, $this->backGround);
    }

    //输出
    private function outPut(){
        header('Content-type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }
    //对外生成
    public function saveImg($path,$name=''){
        $this->sealName = $name;
        $this->createImg();
        $this->drawRim();
        $this->drawInnerCircle();
        $this->drawName();
        $this->drawNum();
        $this->drawString();
        $this->drawStart();
        imagepng($this->img,$path);
        imagedestroy($this->img);
    }
//生成椭圆
    public function saveEll($path,$name=''){
        $this->sealName = $name;
        $this->width  = 200;
        $this->height = 147;
        $this->yheight = 110;
        $this->startRadius = 20;
        $this->fontSize = 14;
        $this->sealRadius = 75;
        $this->points = array();
        $this->centerDot  = array('x'=>$this->width/2, 'y'=>$this->height/2);
        $this->createImg();
        $this->drawRim();
        $this->drawInnerCircle();
        $this->drawName();
        $this->drawNum1();
        $this->drawString1();
        $this->drawStart();
        imagepng($this->img,$path);
        imagedestroy($this->img);
//        $this->outPut();
    }
    //生成圆角正方形
    public function saveSquare($path,$name=''){
        $this->sealName = $name;
//        $this->width  = 120;
//        $this->height = 120;
//        $this->createImg();
//        $this->drawSqu();
//        $this->drawName1();
//        $this->outPut();


        $this->width    = 120;
        $this->height   = 120;
        $this->img    = imagecreatetruecolor($this->width, $this->width);   // 创建一个正方形的图像
        $bgcolor     = imagecolorallocate($this->img, 255, 0, 0);   // 图像的背景
        imagefill($this->img, 0, 0, $bgcolor);

        // 圆角处理
        $radius  = 5;
        // lt(左上角)
        $lt_corner  = $this->get_lt_rounder_corner($radius);
        imagecopymerge($this->img, $lt_corner, 0, 0, 0, 0, $radius, $radius, 100);
        // lb(左下角)
        $lb_corner  = imagerotate($lt_corner, 90, 0);
        imagecopymerge($this->img, $lb_corner, 0, $this->height - $radius, 0, 0, $radius, $radius, 100);
        // rb(右上角)
        $rb_corner  = imagerotate($lt_corner, 180, 0);
        imagecopymerge($this->img, $rb_corner, $this->width - $radius, $this->height - $radius, 0, 0, $radius, $radius, 100);
        // rt(右下角)
        $rt_corner  = imagerotate($lt_corner, 270, 0);
        imagecopymerge($this->img, $rt_corner, $this->width - $radius, 0, 0, 0, $radius, $radius, 100);

        $img1    = imagecreatetruecolor(110, 110);   // 创建一个正方形的图像
        $bgcolor     = imagecolorallocate($img1, 255, 255, 255);   // 图像的背景
        imagefill($img1, 0, 0, $bgcolor);
        // 圆角处理
        $radius  = 5;
        // lt(左上角)
        $lt_corner  = $this->get_lt_rounder_corner($radius,255,1,0);
        imagecopymerge($img1, $lt_corner, 0, 0, 0, 0, $radius, $radius, 100);
        // lb(左下角)
        $lb_corner  = imagerotate($lt_corner, 90, 0);
        imagecopymerge($img1, $lb_corner, 0, 105, 0, 0, $radius, $radius, 100);
        // rb(右上角)
        $rb_corner  = imagerotate($lt_corner, 180, 0);
        imagecopymerge($img1, $rb_corner, 105, 105, 0, 0, $radius, $radius, 100);
        // rt(右下角)
        $rt_corner  = imagerotate($lt_corner, 270, 0);
        imagecopymerge($img1, $rt_corner,105, 0, 0, 0, $radius, $radius, 100);

        imagecopymerge($this->img, $img1, 5, 5, 0, 0, 110, 110, 100);
        $this->backGround  = imagecolorallocate($this->img,255,0,0);
        $this->drawName1();
        imagepng($this->img,$path);
        imagedestroy($this->img);
    }

    function get_lt_rounder_corner($radius,$r=255,$g=255,$b=255) {
        $img     = imagecreatetruecolor($radius, $radius);  // 创建一个正方形的图像
        $bgcolor    = imagecolorallocate($img, $r, $g, $b);   // 图像的背景
        $fgcolor    = imagecolorallocate($img, 255,0,0);
        imagefill($img, 0, 0, $bgcolor);
        // $radius,$radius：以图像的右下角开始画弧
        // $radius*2, $radius*2：已宽度、高度画弧
        // 180, 270：指定了角度的起始和结束点
        // fgcolor：指定颜色
        imagefilledarc($img, $radius, $radius, $radius*2, $radius*2, 180, 270, $fgcolor, IMG_ARC_PIE);
        // 将弧角图片的颜色设置为透明
        imagecolortransparent($img, $fgcolor);
        // 变换角度
        // $img = imagerotate($img, 90, 0);
        // $img = imagerotate($img, 180, 0);
        // $img = imagerotate($img, 270, 0);
        // header('Content-Type: image/png');
        // imagepng($img);
        return $img;
    }
    //对外生成
    public function doImg($name=''){
        $this->sealName = $name;
        $this->createImg();
        $this->drawRim();
        $this->drawInnerCircle();
        $this->drawName();
        $this->drawNum();
        $this->drawString();
        $this->drawStart();
        $this->outPut();
    }
}