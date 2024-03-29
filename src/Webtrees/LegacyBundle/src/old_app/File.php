<?php
namespace Webtrees\LegacyBundle\Legacy;

/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class File - File manipulation utilities
 */
class File
{
    /**
     * Fetch a remote file
     * Note that fopen() and file_get_contents() are often unvailable, as they
     * can easily be exploited by application bugs, and are therefore disabled.
     * Hence we use fsockopen().
     * To allow arbitrarily large downloads with small memory limits, we either
     * write output to a stream or return it.
     *
     * @param string        $url
     * @param resource|null $stream
     *
     * @return null|string
     */
    public static function fetchUrl($url, $stream = null)
    {
        $host  = parse_url($url, PHP_URL_HOST);
        $port  = parse_url($url, PHP_URL_PORT);
        $path  = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        if (!$port) {
            $port = parse_url($url, PHP_URL_SCHEME) == 'https' ? 443 : 80;
        }

        $scheme = $port == 443 ? 'ssl://' : '';

        $fp = @fsockopen($scheme . $host, $port, $errno, $errstr, 5);
        if (!$fp) {
            return null;
        }

        fputs($fp, "GET $path?$query HTTP/1.0\r\nHost: $host\r\nConnection: Close\r\n\r\n");

        // The first part of the response include the HTTP headers
        $response = fread($fp, 65536);

        // The file has moved?  Follow it.
        if (preg_match('/^HTTP\/1.[01] 30[23].+\nLocation: ([^\r\n]+)/s', $response, $match)) {
            fclose($fp);

            return File::fetchUrl($match[1], $stream);
        } else {
            // The response includes headers, a blank line, then the content
            $response = substr($response, strpos($response, "\r\n\r\n") + 4);
        }

        if ($stream) {
            fwrite($stream, $response);
            while ($tmp = fread($fp, 8192)) {
                fwrite($stream, $tmp);
            }
            fclose($fp);

            return null;
        } else {
            while ($tmp = fread($fp, 8192)) {
                $response .= $tmp;
            }
            fclose($fp);

            return $response;
        }
    }

    /**
     * Recursively delete a folder or file
     *
     * @param string $path
     *
     * @return boolean Was the file deleted
     */
    public static function delete($path)
    {
        // In case the file is marked read-only
        @chmod($path, 0777);

        if (is_dir($path)) {
            $dir = opendir($path);
            while ($dir !== false && (($file = readdir($dir)) !== false)) {
                if ($file != '.' && $file != '..') {
                    File::delete($path . DIRECTORY_SEPARATOR . $file);
                }
            }
            closedir($dir);
            @rmdir($path);
        } else {
            @unlink($path);
        }

        return !file_exists($path);
    }

    /**
     * Create a folder, and sub-folders, if it does not already exist
     *
     * @param string $path
     *
     * @return boolean Does the folder now exist
     */
    public static function mkdir($path)
    {
        if (is_dir($path)) {
            return true;
        } else {
            if (dirname($path) && !is_dir(dirname($path))) {
                File::mkdir(dirname($path));
            }
            @mkdir($path);

            return is_dir($path);
        }
    }
}
