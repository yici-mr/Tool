<?php

namespace yctool\tool;

class Tool
{
    /**
     * 图片转base64
     * @param string $path 文件路径
     */

    public function PictToBase64(string $path):string
    {
        return base64_encode(file_get_contents($path));
    }

}