<?php

class Outpost_Utils
{
    // TODO: Implement ignore_file_list to allow file and directory skipping (see http://stackoverflow.com/a/18271230/88487 )
    // TODO: Handle endless loop if destination is inside source (add subfolder path of destination to ignore list?)
    public static function copy_file_or_folder($source, $destination, $ignore_file_list = null)
    {
        $path = realpath($source);

        if (is_file($path)){
            copy($path, $destination);
            return;
        }

        // Build a list of the folder contents
        $directory_iterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $objects = new RecursiveIteratorIterator($directory_iterator, RecursiveIteratorIterator::SELF_FIRST);

        // Create the destination directory that will hold the source files
        self::make_folder($destination);

        // Copy source files and directories to the destination
        foreach ($objects as $original_path => $object) {

            if ($object->isDir()) {
                $object_path = substr($original_path, strlen($source));
                self::make_folder($destination . $object_path);
                continue;
            }

            if ($object->isFile() || $object->isLink()) {
                $subfolder_path = substr(dirname($original_path), strlen($source));
                if (is_writable($destination . $subfolder_path)) {
                    $full_destination = $destination . $subfolder_path . '/' . basename($original_path);
                    copy($original_path, $full_destination);
                }
            }

        }
    }

    public static function make_folder($folder, $permissions = 0777)
    {
        if (!is_dir($folder)) {
            mkdir($folder, $permissions, true);
        }
    }


    // Adapted from http://stackoverflow.com/a/1334949/88487
    public static function zip($source, $destination, $delete_after_zip = false)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        if (is_dir($source)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $path => $file) {
                // Close and reopen the zip file every 255 files to work around the lowest likely ulimit() for the
                // maximum number of open files. See http://www.php.net/manual/en/ziparchive.addfile.php#74297
                if ($zip->numFiles % 255 == 0){
                    $zip->close();
                    $zip->open($destination, ZIPARCHIVE::CREATE);
                }

                $zip_path = substr($path, strlen($source));

                if ($file->isDir()) {
                    $zip->addEmptyDir($zip_path);
                    continue;
                }

                if ($file->isFile()) {
                    $zip->addFile($path, $zip_path);
                    // $zip->addFromString($zip_path, file_get_contents($file)); // Alternative to addFile and closing/opening zip file every 255 files
                }
            }
        }

        if (is_file($source)) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        $success = $zip->close();

        if ($success && $delete_after_zip)
            self::delete_file_or_folder($source);

        return $success;
    }


    public static function download_file($filename, $download_name = null, $delete_on_download = false)
    {
        set_time_limit(0);

        if (!$download_name)
            $download_name = basename($filename);

        if (file_exists($filename)) {
            header("Pragma: public");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . filesize($filename));
            header('Content-Disposition: attachment; filename=' . $download_name);
            $success = readfile($filename);
            if ($success && $delete_on_download)
                unlink($filename);
            exit;
        } else {
            wp_die("No file exists to download at $filename.");
        }
    }

    public static function delete_file_or_folder($object)
    {
        if (!is_dir($object) && !is_file($object))
            return false;

        if (is_file($object)) {
            unlink($object);
            return true;
        }

        if (is_dir($object)) {
            $directory_iterator = new RecursiveDirectoryIterator($object, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($directory_iterator, RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $path => $file) {
                if ($file->isDir()) {
                    rmdir($path);
                    continue;
                }

                if ($file->isFile())
                    unlink($path);
            }

            rmdir($object);
            return true;
        }

        return false;
    }

}