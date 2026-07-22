<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Response;

class SitemapController
{
    /**
     * Generate XML sitemap for the application.
     *
     * Only includes GET routes without required parameters.
     */
    public function index(): Response
    {
        $routes = Route::getRoutes();
        $urls = [];
        foreach ($routes as $route) {
            // Only consider GET routes with a name and no required parameters
            if (!in_array('GET', $route->methods())) {
                continue;
            }
            // Exclude routes that have required parameters (contain { in uri)
            if (strpos($route->uri(), '{') !== false) {
                continue;
            }
            // Skip internal routes like CSRF token routes etc.
            if ($route->named('sitemap') || $route->named('login') || $route->named('login.callback')) {
                continue;
            }
            $url = URL::to($route->uri());
            $urls[] = $url;
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $url) {
            $xml .= "  <url><loc>{$url}</loc></url>\n";
        }
        $xml .= "</urlset>";

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
