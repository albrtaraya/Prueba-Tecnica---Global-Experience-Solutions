<?php

namespace App\Utils;

class Validator{

    public static function isValidUrl(string $url){
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }// isValidUrl()
    
    public static function isSupportedPlatform(string $url, array $supportedHosts): bool{
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host'])) {
            return false;
        }

        foreach ($supportedHosts as $host) {
            if (strpos($parsedUrl['host'], $host) !== false) {
                return true;
            }
        }

        return false;
    }// isSupportedPlatform()
    
    public static function isPositiveInteger($number){
        return filter_var($number, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) !== false;
    }// isPositiveInteger()

    
    public static function isNotEmpty(string $string){
        return trim($string) !== '';
    }// isNotEmpty()

}// Validator
