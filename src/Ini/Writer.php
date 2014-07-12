<?php

namespace Mihaeu\MovieManager\Ini;

/**
 * Class Writer
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Writer
{
    /**
     * Convert and write a php array into an .ini file.
     *
     * @param  $file
     * @param  $data
     *
     * @return bool
     */
    public static function write($file, $data)
    {
        $content = '';
        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                if (is_array($value))
                {
                    if ( ! empty($value))
                    {
                        $content .= "[$key]\r\n";
                    }
                    foreach ($value as $subkey => $subvalue)
                    {
                        if (is_array($subvalue))
                        {
                            if ( ! empty($value))
                            {
                                $content .= "[$key\\$subkey]\r\n";
                            }
                            foreach ($subvalue as $subsubkey => $subsubvalue)
                            {
                                if (is_numeric($subsubvalue))
                                {
                                    $content .= "$subsubkey=$subsubvalue\r\n";
                                }
                                else
                                {
                                    $subsubvalue = str_replace('"', "'", $subsubvalue);
                                    $content .= "$subsubkey=\"$subsubvalue\"\r\n";
                                }
                            }
                            $content .= "\r\n";
                        }
                        else
                        {
                            if (is_numeric($subvalue))
                            {
                                $content .= "$subkey=$subvalue\r\n";
                            }
                            else
                            {
                                $subvalue = str_replace('"', "'", $subvalue);
                                $content .= "$subkey=\"$subvalue\"\r\n";
                            }
                        }
                    }
                    $content .= "\r\n";
                }
                else
                {
                    if (is_numeric($value))
                    {
                        $content .= "$key=$value\r\n";
                    }
                    else
                    {
                        $value = str_replace('"', "'", $value);
                        $content .= "$key=\"$value\"\r\n";
                    }
                }
            }
        }
        else
        {
            return false;
        }

        return false !== @file_put_contents($file, $content);
    }

//    public static function alternativeWrite($file, $data)
//    {
//        $content = '';
//        if (is_array($data)) {
//            foreach ($data as $key => $value) {
//                if (is_array($value)) {
//                    if (!empty($value)) {
//                        $content .= "[$key]\r\n";
//                    }
//                    foreach ($value as $subkey => $subvalue) {
//                        if (is_array($subvalue)) {
//                            if (!empty($value)) {
//                                $content .= "[$key\\$subkey]\r\n";
//                            }
//                            foreach ($subvalue as $subsubkey => $subsubvalue) {
//                                if (is_numeric($subsubvalue)) {
//                                    $content .= "$subsubkey=$subsubvalue\r\n";
//                                } else {
//                                    $subsubvalue = str_replace('"', "'", $subsubvalue);
//                                    $content .= "$subsubkey=\"$subsubvalue\"\r\n";
//                                }
//                            }
//                            $content .= "\r\n";
//                        } else {
//                            if (is_numeric($subvalue)) {
//                                $content .= "$subkey=$subvalue\r\n";
//                            } else {
//                                $subvalue = str_replace('"', "'", $subvalue);
//                                $content .= "$subkey=\"$subvalue\"\r\n";
//                            }
//                        }
//                    }
//                    $content .= "\r\n";
//                } else {
//                    if (is_numeric($value)) {
//                        $content .= "$key=$value\r\n";
//                    } else {
//                        $value = str_replace('"', "'", $value);
//                        $content .= "$key=\"$value\"\r\n";
//                    }
//                }
//            }
//        } else {
//            return false;
//        }
//
//        return false !== @file_put_contents($file, $content);
//    }
} 