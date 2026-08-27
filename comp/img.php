<?

class img {

    public static function imageextend($infile, $newWidth, $newHeight) {
        $im = new Imagick();
        $im->readimage($infile);

        $width = $im->getimagewidth();
        $height = $im->getimageheight();

        if ($width > $newWidth && $height > $newHeight) {
            $im->destroy();
            return;
        }
        
        $borderWidth = $newWidth - $width;
        if ($borderWidth < 0)
            $borderWidth = 0;

        $borderHeight = $newHeight - $height;
        if ($borderHeight < 0)
            $borderHeight = 0;

        $im->borderImage(new ImagickPixel("white"), ceil($borderWidth / 2), ceil($borderHeight / 2));
        $im->writeImage($infile);
        $im->destroy();
        return;
    }

    public static function imageextendAndCut($infile, $newWidth, $newHeight) {
        $im = new Imagick();
        $im->readimage($infile);

        $width = $im->getimagewidth();
        $height = $im->getimageheight();

        #if ($width > $newWidth && $height > $newHeight) {
#            $im->destroy();
#            return;
#       }

        $borderWidth = $newWidth - $width;
        if ($borderWidth < 0)
            $borderWidth = 0;

        $borderHeight = $newHeight - $height;
        if ($borderHeight < 0)
            $borderHeight = 0;

        $im->borderImage(new ImagickPixel("white"), ceil($borderWidth / 2), ceil($borderHeight / 2));

        $width = $im->getimagewidth();
        $height = $im->getimageheight();

        if ($width > $newWidth && $newWidth)
            $cropWidth = $newWidth;
        else
            $cropWidth = $width;

        if ($height > $newHeight && $newHeight)
            $cropHeight = $newHeight;
        else
            $cropHeight = $height;

        $cropX = ceil(($width - $cropWidth) / 2);
        $cropY = ceil(($height - $cropHeight) / 2);

        $im->cropimage($cropWidth, $cropHeight, $cropX, $cropY);

        $im->writeImage($infile);


        $im->destroy();
        return;
    }

    public static function imageresize_i($infile, $newd) {
        $im = new Imagick();

        $im->readimage($infile);

        $width = $im->getimagewidth();
        $height = $im->getimageheight();

        $newh = 0;
        $neww = 0;

        if ($newd > $width && $newh > $height) {
            $im->destroy();
            return;
        }

        if ($width > $height)
            $im->thumbnailImage($newd, 0);
        else
            $im->thumbnailImage(0, $newd);

        $im->writeImage($infile);

        $im->destroy();
        return;
    }

    public static function imageresize($infile, $newWidth, $newHeight) {
        $im = new Imagick();
        try {
        $im->readimage($infile);
        } catch (Exception $ex) {
            var_dump($ex);
            die();
        }

        $width = $im->getimagewidth();
        $height = $im->getimageheight();

        if ($newWidth > $width && $newHeight > $height) {
            $im->destroy();
            return;
        }
        
        if ($newWidth && $newWidth < $width)
            $im->thumbnailImage($newWidth, 0);
        
        if ($newHeight && $newHeight < $height)
            $im->thumbnailImage($newHeight, 0);

        $im->writeImage($infile);

        $im->destroy();
        return;
    }

    public static function imageresize_ii($infile, $newd) {
        $im = new Imagick();

        $im->readimage($infile);

        $width = $im->getimagewidth();
        $height = $im->getimageheight();

        $newh = 0;
        $neww = 0;

        if ($newd > $width && $newh > $height) {
            $im->destroy();
            return;
        }

        if ($width > $height)
            $im->thumbnailImage(0, $newd);
        else
            $im->thumbnailImage($newd, 0);

        $im->writeImage($infile);

        $im->destroy();
        return;
    }

    public static function imagecut($infile, $neww, $newh) {
        $im = new Imagick();
        $im->readimage($infile);

        $width = $im->getimagewidth();
        $height = $im->getimageheight();

        $im->cropthumbnailimage($neww, $newh);
        $im->writeImage($infile);
        $im->destroy();

        return;
    }

    public static function imagecrop($infile, $width, $height, $x1, $y2) {
        $im = new Imagick();
        $im->readimage($infile);

        $im->cropimage($width, $height, $x1, $y2);
        $im->writeImage($infile);
        $im->destroy();

        return;
    }

    public static function imagemask($infile, $maskFile) {
        $im = new Imagick();
        $im->readimage($infile);

        $mask = new Imagick();
        $mask->readimage($maskFile);

        $im->compositeImage($mask, Imagick::COMPOSITE_DEFAULT, 0, 0);
        
        $im->writeImage($infile);
        $im->destroy();
        $mask->destroy();

        return;
    }

    public static function imagewatermark($infile, $maskFile) {
        $im = new Imagick();
        $im->readimage($infile);

        $mask = new Imagick();
        $mask->readimage($maskFile);
        
        $width = $im->getimagewidth();
        $height = $im->getimageheight();
        $maskWidth = $mask->getimagewidth();
        $maskHeight = $mask->getimageheight();
        $x = $width - $maskWidth - 5;
        $y = $height - $maskHeight - 5;
        
        $im->compositeImage($mask, Imagick::COMPOSITE_DEFAULT, $x, $y);
        
        $im->writeImage($infile);
        $im->destroy();
        $mask->destroy();

        return;        
    }
}

?>