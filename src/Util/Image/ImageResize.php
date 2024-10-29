<?php

class ImageResize
{
    /**
     * Resize an image while maintaining the aspect ratio.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the resized image.
     * @param int $maxWidth Maximum width of the resized image.
     * @param int $maxHeight Maximum height of the resized image.
     * @return void
     */
    public function resizeImage($sourcePath, $destPath, $maxWidth, $maxHeight) {
        list($width, $height) = getimagesize($sourcePath);
        $aspectRatio = $width / $height;
    
        if ($width > $height) {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $aspectRatio;
        } else {
            $newHeight = $maxHeight;
            $newWidth = $maxHeight * $aspectRatio;
        }
    
        $image = imagecreatefromjpeg($sourcePath);
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($resizedImage, $destPath);
        imagedestroy($image);
        imagedestroy($resizedImage);
    }

    /**
     * Resize and crop the image from the center while maintaining the aspect ratio.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the resized image.
     * @param int $targetWidth Target width of the cropped image.
     * @param int $targetHeight Target height of the cropped image.
     * @return void
     */
    public function resizeCropCenter($sourcePath, $destPath, $targetWidth, $targetHeight) {
        list($width, $height) = getimagesize($sourcePath);
        
    
        if ($width / $height > $targetWidth / $targetHeight) {
            $newWidth = $width * ($targetHeight / $height);
            $newHeight = $targetHeight;
        } else {
            $newWidth = $targetWidth;
            $newHeight = $height * ($targetWidth / $width);
        }
    
        $image = imagecreatefromjpeg($sourcePath);
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
        $finalImage = imagecreatetruecolor($targetWidth, $targetHeight);
        $x = ($targetWidth - $newWidth) / 2;
        $y = ($targetHeight - $newHeight) / 2;
        imagecopy($finalImage, $resizedImage, $x, $y, 0, 0, $newWidth, $newHeight);
    
        imagejpeg($finalImage, $destPath);
        imagedestroy($image);
        imagedestroy($resizedImage);
        imagedestroy($finalImage);
    }
    
    /**
     * Resize and add padding to the image from the center while maintaining the aspect ratio.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the resized image.
     * @param int $targetWidth Target width of the final image.
     * @param int $targetHeight Target height of the final image.
     * @return void
     */
    public function resizePaddingCenter($sourcePath, $destPath, $targetWidth, $targetHeight) {
        list($width, $height) = getimagesize($sourcePath);
        $aspectRatio = $width / $height;
    
        if ($width / $height > $targetWidth / $targetHeight) {
            $newHeight = $targetHeight;
            $newWidth = $targetHeight * $aspectRatio;
        } else {
            $newWidth = $targetWidth;
            $newHeight = $targetWidth / $aspectRatio;
        }
    
        $image = imagecreatefromjpeg($sourcePath);
        $finalImage = imagecreatetruecolor($targetWidth, $targetHeight);
        $bgColor = imagecolorallocate($finalImage, 255, 255, 255); // white background
        imagefill($finalImage, 0, 0, $bgColor);
    
        $x = ($targetWidth - $newWidth) / 2;
        $y = ($targetHeight - $newHeight) / 2;
        imagecopyresampled($finalImage, $image, $x, $y, 0, 0, $newWidth, $newHeight, $width, $height);
    
        imagejpeg($finalImage, $destPath);
        imagedestroy($image);
        imagedestroy($finalImage);
    }
    
    /**
     * Resize and crop the image from the top left while maintaining the aspect ratio.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the resized image.
     * @param int $targetWidth Target width of the cropped image.
     * @param int $targetHeight Target height of the cropped image.
     * @return void
     */
    public function resizeCropTopLeft($sourcePath, $destPath, $targetWidth, $targetHeight) {
        list($width, $height) = getimagesize($sourcePath);
        
    
        if ($width / $height > $targetWidth / $targetHeight) {
            $newWidth = $width * ($targetHeight / $height);
            $newHeight = $targetHeight;
        } else {
            $newWidth = $targetWidth;
            $newHeight = $height * ($targetWidth / $width);
        }
    
        $image = imagecreatefromjpeg($sourcePath);
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
        $finalImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopy($finalImage, $resizedImage, 0, 0, 0, 0, $targetWidth, $targetHeight);
    
        imagejpeg($finalImage, $destPath);
        imagedestroy($image);
        imagedestroy($resizedImage);
        imagedestroy($finalImage);
    }
    
    /**
     * Resize and add padding to the image from the top right while maintaining the aspect ratio.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the resized image.
     * @param int $targetWidth Target width of the final image.
     * @param int $targetHeight Target height of the final image.
     * @return void
     */
    public function resizePaddingTopRight($sourcePath, $destPath, $targetWidth, $targetHeight) {
        list($width, $height) = getimagesize($sourcePath);
        $aspectRatio = $width / $height;
    
        if ($width / $height > $targetWidth / $targetHeight) {
            $newHeight = $targetHeight;
            $newWidth = $targetHeight * $aspectRatio;
        } else {
            $newWidth = $targetWidth;
            $newHeight = $targetWidth / $aspectRatio;
        }
    
        $image = imagecreatefromjpeg($sourcePath);
        $finalImage = imagecreatetruecolor($targetWidth, $targetHeight);
        $bgColor = imagecolorallocate($finalImage, 255, 255, 255); // white background
        imagefill($finalImage, 0, 0, $bgColor);
    
        $x = $targetWidth - $newWidth;
        $y = 0;
        imagecopyresampled($finalImage, $image, $x, $y, 0, 0, $newWidth, $newHeight, $width, $height);
    
        imagejpeg($finalImage, $destPath);
        imagedestroy($image);
        imagedestroy($finalImage);
    }
    
    /**
     * Resize and crop the image from the top right while maintaining the aspect ratio.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the resized image.
     * @param int $targetWidth Target width of the cropped image.
     * @param int $targetHeight Target height of the cropped image.
     * @return void
     */
    public function resizeCropTopRight($sourcePath, $destPath, $targetWidth, $targetHeight) {
        list($width, $height) = getimagesize($sourcePath);
        
    
        if ($width / $height > $targetWidth / $targetHeight) {
            $newWidth = $width * ($targetHeight / $height);
            $newHeight = $targetHeight;
        } else {
            $newWidth = $targetWidth;
            $newHeight = $height * ($targetWidth / $width);
        }
    
        $image = imagecreatefromjpeg($sourcePath);
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
        $finalImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopy($finalImage, $resizedImage, $targetWidth - $newWidth, 0, 0, 0, $targetWidth, $targetHeight);
    
        imagejpeg($finalImage, $destPath);
        imagedestroy($image);
        imagedestroy($resizedImage);
        imagedestroy($finalImage);
    }
    
    /**
     * Resize and crop the image from the bottom left while maintaining the aspect ratio.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the resized image.
     * @param int $targetWidth Target width of the cropped image.
     * @param int $targetHeight Target height of the cropped image.
     * @return void
     */
    public function resizeCropBottomLeft($sourcePath, $destPath, $targetWidth, $targetHeight) {
        list($width, $height) = getimagesize($sourcePath);
        
    
        if ($width / $height > $targetWidth / $targetHeight) {
            $newWidth = $width * ($targetHeight / $height);
            $newHeight = $targetHeight;
        } else {
            $newWidth = $targetWidth;
            $newHeight = $height * ($targetWidth / $width);
        }
    
        $image = imagecreatefromjpeg($sourcePath);
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
        $finalImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopy($finalImage, $resizedImage, 0, $targetHeight - $newHeight, 0, 0, $targetWidth, $targetHeight);
    
        imagejpeg($finalImage, $destPath);
        imagedestroy($image);
        imagedestroy($resizedImage);
        imagedestroy($finalImage);
    }
    
    /**
     * Resize and crop the image from the bottom right while maintaining the aspect ratio.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the resized image.
     * @param int $targetWidth Target width of the cropped image.
     * @param int $targetHeight Target height of the cropped image.
     * @return void
     */
    public function resizeCropBottomRight($sourcePath, $destPath, $targetWidth, $targetHeight) {
        list($width, $height) = getimagesize($sourcePath);
        
    
        if ($width / $height > $targetWidth / $targetHeight) {
            $newWidth = $width * ($targetHeight / $height);
            $newHeight = $targetHeight;
        } else {
            $newWidth = $targetWidth;
            $newHeight = $height * ($targetWidth / $width);
        }
    
        $image = imagecreatefromjpeg($sourcePath);
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
        $finalImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopy($finalImage, $resizedImage, $targetWidth - $newWidth, $targetHeight - $newHeight, 0, 0, $targetWidth, $targetHeight);
    
        imagejpeg($finalImage, $destPath);
        imagedestroy($image);
        imagedestroy($resizedImage);
        imagedestroy($finalImage);
    }
    
    /**
     * Flip the image vertically.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the flipped image.
     * @return void
     */
    public function flipVertical($sourcePath, $destPath) {
        $image = imagecreatefromjpeg($sourcePath);
        $width = imagesx($image);
        $height = imagesy($image);
        
        $flippedImage = imagecreatetruecolor($width, $height);
        for ($y = 0; $y < $height; $y++) {
            imagecopy($flippedImage, $image, 0, $height - $y - 1, 0, $y, $width, 1);
        }

        imagejpeg($flippedImage, $destPath);
        imagedestroy($image);
        imagedestroy($flippedImage);
    }

    /**
     * Flip the image horizontally.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the flipped image.
     * @return void
     */
    public function flipHorizontal($sourcePath, $destPath) {
        $image = imagecreatefromjpeg($sourcePath);
        $width = imagesx($image);
        $height = imagesy($image);
        
        $flippedImage = imagecreatetruecolor($width, $height);
        for ($x = 0; $x < $width; $x++) {
            imagecopy($flippedImage, $image, $width - $x - 1, 0, $x, 0, 1, $height);
        }

        imagejpeg($flippedImage, $destPath);
        imagedestroy($image);
        imagedestroy($flippedImage);
    }

    /**
     * Rotate the image by 90 degrees clockwise.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the rotated image.
     * @return void
     */
    public function rotate90($sourcePath, $destPath) {
        $image = imagecreatefromjpeg($sourcePath);
        $rotatedImage = imagerotate($image, -90, 0);
        imagejpeg($rotatedImage, $destPath);
        imagedestroy($image);
        imagedestroy($rotatedImage);
    }

    /**
     * Rotate the image by 180 degrees.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the rotated image.
     * @return void
     */
    public function rotate180($sourcePath, $destPath) {
        $image = imagecreatefromjpeg($sourcePath);
        $rotatedImage = imagerotate($image, 180, 0);
        imagejpeg($rotatedImage, $destPath);
        imagedestroy($image);
        imagedestroy($rotatedImage);
    }

    /**
     * Rotate the image by 270 degrees clockwise.
     *
     * @param string $sourcePath Path to the source image.
     * @param string $destPath Path to save the rotated image.
     * @return void
     */
    public function rotate270($sourcePath, $destPath) {
        $image = imagecreatefromjpeg($sourcePath);
        $rotatedImage = imagerotate($image, 90, 0);
        imagejpeg($rotatedImage, $destPath);
        imagedestroy($image);
        imagedestroy($rotatedImage);
    }
}
