<?php

namespace AOD\Plugin\Support\Helpers;

use Illuminate\Support\Collection;

class FileSystemHelper
{
    /**
     * @param string $dir
     * @param string[] $ignore
     * @return array
     */
    public static function getFilesFromDirectoryRecursive( $dir, $ignore = [ '.', '..' ] )
    {
        $path = str_replace( '/', DIRECTORY_SEPARATOR, trailingslashit( $dir ) );
        return Collection::make( array_diff( scandir( $path ), $ignore ) )
            ->map( function ($file) use ($path, $ignore) {
                return ! is_file( $file = $path . $file )
                    ? static::getFilesFromDirectoryRecursive( $file, $ignore )
                    : $file;
            })->toArray();
    }
}
